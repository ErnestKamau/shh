#!/usr/bin/env python3
"""
Laravel migration consistency audit.

Detects duplicate CREATE statements, repeated objects, dependency ordering issues,
and classifies duplicate CREATE TABLE migrations as identical / superset / divergent.
"""

from __future__ import annotations

import importlib.util
import re
import sys
from collections import defaultdict
from dataclasses import dataclass, field
from datetime import datetime
from pathlib import Path
from typing import Optional


ROOT = Path(__file__).resolve().parent.parent
MIGRATIONS_ROOT = ROOT / "database" / "migrations"
REPORT_PATH = ROOT / "migration_consistency_report.md"

TIMESTAMP_RE = re.compile(r"^(\d{4}_\d{2}_\d{2}_\d{6})_(.+)\.php$")

# Load sibling dependency analyzer without package install
_DEP_ANALYZER_PATH = ROOT / "scripts" / "migration_dependency_analyzer.py"
_spec = importlib.util.spec_from_file_location("migration_dependency_analyzer", _DEP_ANALYZER_PATH)
_dep = importlib.util.module_from_spec(_spec)
assert _spec and _spec.loader
sys.modules["migration_dependency_analyzer"] = _dep
_spec.loader.exec_module(_dep)


def extract_method_body(content: str, method: str = "up") -> str:
    m = re.search(rf"function\s+{method}\s*\(\s*\)\s*(?::\s*\w+)?\s*\{{", content, re.I)
    if not m:
        return ""
    start = m.end()
    depth = 1
    i = start
    while i < len(content) and depth > 0:
        if content[i] == "{":
            depth += 1
        elif content[i] == "}":
            depth -= 1
        i += 1
    return content[start : i - 1]


def extract_up_method(content: str) -> str:
    return extract_method_body(content, "up")


def extract_down_method(content: str) -> str:
    return extract_method_body(content, "down")


def extract_db_sql_strings(content: str) -> list[str]:
    return _dep.extract_db_sql_strings(content)


def normalize_identifier(name: str) -> str:
    name = name.strip().strip('"`[]')
    if "." in name:
        parts = name.split(".")
        name = parts[-1]
    return name.lower()


def normalize_table(name: str) -> Optional[str]:
    return _dep.normalize_table(name)


@dataclass
class ColumnDef:
    name: str
    type_hint: str
    modifiers: frozenset[str] = frozenset()

    def signature(self) -> tuple[str, str, frozenset[str]]:
        return (self.name, self.type_hint, self.modifiers)


@dataclass
class TableSchema:
    table: str
    columns: dict[str, ColumnDef] = field(default_factory=dict)
    indexes: set[str] = field(default_factory=set)
    foreign_keys: set[str] = field(default_factory=set)
    raw_snippet: str = ""

    def column_names(self) -> set[str]:
        return set(self.columns.keys())

    def column_signatures(self) -> dict[str, tuple[str, str, frozenset[str]]]:
        return {n: c.signature() for n, c in self.columns.items()}


@dataclass
class MigrationRecord:
    path: Path
    filename: str
    timestamp: str
    suffix: str
    relative_path: str
    up_body: str
    down_body: str = ""
    tables_dropped_up: set[str] = field(default_factory=set)
    tables_created: dict[str, TableSchema] = field(default_factory=dict)
    tables_created_down: dict[str, TableSchema] = field(default_factory=dict)
    views_created: set[str] = field(default_factory=set)
    view_create_or_replace: dict[str, bool] = field(default_factory=dict)
    indexes_created: set[str] = field(default_factory=set)
    types_created: set[str] = field(default_factory=set)
    schemas_created: set[str] = field(default_factory=set)
    schema_create_if_not_exists: dict[str, bool] = field(default_factory=dict)
    extensions_created: set[str] = field(default_factory=set)
    extension_create_if_not_exists: dict[str, bool] = field(default_factory=dict)
    foreign_keys_created: set[str] = field(default_factory=set)


TYPE_NORMALIZE = {
    "biginteger": "bigint",
    "unsignedbiginteger": "bigint",
    "unsignedinteger": "integer",
    "unsignedsmallinteger": "smallint",
    "unsignedtinyinteger": "smallint",
    "tinyinteger": "smallint",
    "increments": "serial",
    "bigincrements": "bigserial",
    "id": "bigserial",
    "uuid": "uuid",
    "string": "varchar",
    "text": "text",
    "longtext": "text",
    "mediumtext": "text",
    "boolean": "boolean",
    "bool": "boolean",
    "timestamp": "timestamp",
    "timestamptz": "timestamptz",
    "datetime": "timestamp",
    "date": "date",
    "time": "time",
    "json": "json",
    "jsonb": "jsonb",
    "float": "float",
    "double": "double",
    "decimal": "decimal",
    "binary": "bytea",
    "char": "char",
}


def normalize_type(raw: str) -> str:
    raw = raw.strip().lower()
    raw = re.sub(r"\s+", "", raw)
    m = re.match(r"(\w+)(?:\(([^)]*)\))?", raw)
    if not m:
        return raw
    base = TYPE_NORMALIZE.get(m.group(1), m.group(1))
    if m.group(2):
        return f"{base}({m.group(2)})"
    return base


def parse_modifiers(fragment: str) -> frozenset[str]:
    mods: set[str] = set()
    lower = fragment.lower()
    if "nullable()" in lower or "->nullable" in lower:
        mods.add("nullable")
    else:
        mods.add("not_null")
    if "unsigned()" in lower:
        mods.add("unsigned")
    if "unique()" in lower or "->unique" in lower:
        mods.add("unique")
    if re.search(r"default\s*\(", lower):
        dm = re.search(r"default\s*\(\s*['\"]?([^'\"),\]]+)", lower)
        if dm:
            mods.add(f"default:{dm.group(1).strip()}")
    if "usecurrent()" in lower.replace(" ", ""):
        mods.add("use_current")
    return frozenset(mods)


def extract_schema_create_blocks(up: str) -> list[tuple[str, str]]:
    blocks: list[tuple[str, str]] = []
    for m in re.finditer(r"Schema::create\s*\(\s*['\"]([^'\"]+)['\"]", up, re.I):
        table = normalize_table(m.group(1))
        if not table:
            continue
        start = m.end()
        # find closure opening {
        brace = up.find("{", start)
        if brace < 0:
            continue
        depth = 1
        i = brace + 1
        while i < len(up) and depth > 0:
            if up[i] == "{":
                depth += 1
            elif up[i] == "}":
                depth -= 1
            i += 1
        body = up[brace + 1 : i - 1]
        blocks.append((table, body))
    return blocks


def parse_blueprint_columns(body: str) -> dict[str, ColumnDef]:
    columns: dict[str, ColumnDef] = {}
    patterns = [
        r"\$table->(\w+)\s*\(\s*['\"]([^'\"]+)['\"]([^;]*)\)",
        r"\$table->(\w+)\s*\(\s*['\"]([^'\"]+)['\"]\s*\)",
        r"\$table->id\s*\(\s*\)",
        r"\$table->uuid\s*\(\s*['\"]([^'\"]+)['\"]",
        r"\$table->timestamps\s*\(\s*\)",
        r"\$table->softDeletes\s*\(\s*\)",
        r"\$table->rememberToken\s*\(\s*\)",
    ]
    for m in re.finditer(r"\$table->(\w+)\s*\(\s*['\"]([^'\"]+)['\"]([^;]*)\)", body):
        method, col, rest = m.group(1), m.group(2).lower(), m.group(3) or ""
        col_type = normalize_type(method)
        fragment = m.group(0) + rest
        columns[col] = ColumnDef(col, col_type, parse_modifiers(fragment))

    if re.search(r"\$table->id\s*\(\s*\)", body):
        columns.setdefault("id", ColumnDef("id", "bigserial", frozenset({"not_null"})))
    for m in re.finditer(r"\$table->uuid\s*\(\s*['\"]([^'\"]+)['\"]", body):
        col = m.group(1).lower()
        columns[col] = ColumnDef(col, "uuid", parse_modifiers(m.group(0)))
    if re.search(r"\$table->timestamps\s*\(\s*\)", body):
        columns.setdefault("created_at", ColumnDef("created_at", "timestamp", frozenset({"nullable"})))
        columns.setdefault("updated_at", ColumnDef("updated_at", "timestamp", frozenset({"nullable"})))
    if re.search(r"\$table->softDeletes\s*\(\s*\)", body):
        columns.setdefault("deleted_at", ColumnDef("deleted_at", "timestamp", frozenset({"nullable"})))
    if re.search(r"\$table->rememberToken\s*\(\s*\)", body):
        columns.setdefault("remember_token", ColumnDef("remember_token", "varchar", frozenset({"nullable"})))

    return columns


def parse_blueprint_indexes(body: str, table: str) -> set[str]:
    indexes: set[str] = set()
    for m in re.finditer(r"\$table->index\s*\(\s*\[([^\]]+)\]", body):
        cols = tuple(sorted(c.strip().strip("'\"").lower() for c in m.group(1).split(",")))
        indexes.add(f"{table}({','.join(cols)})")
    for m in re.finditer(r"\$table->index\s*\(\s*['\"]([^'\"]+)['\"]", body):
        indexes.add(f"{table}({m.group(1).lower()})")
    for m in re.finditer(r"\$table->unique\s*\(\s*\[([^\]]+)\]", body):
        cols = tuple(sorted(c.strip().strip("'\"").lower() for c in m.group(1).split(",")))
        indexes.add(f"{table}_unique({','.join(cols)})")
    for m in re.finditer(r"\$table->unique\s*\(\s*['\"]([^'\"]+)['\"]", body):
        indexes.add(f"{table}_unique({m.group(1).lower()})")
    return indexes


def parse_blueprint_foreign_keys(body: str, table: str) -> set[str]:
    fks: set[str] = set()
    for m in re.finditer(
        r"\$table->(?:foreign(?:Id|Uuid)?|unsignedBigInteger|uuid)\s*\(\s*['\"]([^'\"]+)['\"]"
        r"[^;]*?(?:->constrained\s*\(\s*['\"]([^'\"]+)['\"]|->references\s*\([^)]+\)\s*->on\s*\(\s*['\"]([^'\"]+)['\"])",
        body,
        re.I | re.S,
    ):
        col = m.group(1).lower()
        ref_table = (m.group(2) or m.group(3) or "").lower()
        if ref_table:
            fks.add(f"{table}.{col}->{ref_table}")
    for m in re.finditer(
        r"foreignId\s*\(\s*['\"]([^'\"]+)['\"]\s*\)\s*->constrained\s*\(\s*['\"]([^'\"]+)['\"]",
        body,
        re.I,
    ):
        fks.add(f"{table}.{m.group(1).lower()}->{m.group(2).lower()}")
    for m in re.finditer(
        r"foreign\s*\(\s*['\"]([^'\"]+)['\"]\s*\)\s*->references\s*\([^)]+\)\s*->on\s*\(\s*['\"]([^'\"]+)['\"]",
        body,
        re.I,
    ):
        fks.add(f"{table}.{m.group(1).lower()}->{m.group(2).lower()}")
    return fks


def parse_sql_create_table(sql: str) -> dict[str, TableSchema]:
    tables: dict[str, TableSchema] = {}
    for m in re.finditer(
        r"CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?([\"`]?(?:\w+\.)?\w+[\"`]?)\s*\((.*?)\)\s*;",
        sql,
        re.I | re.S,
    ):
        raw_name = m.group(1)
        table = normalize_table(raw_name)
        if not table:
            continue
        body = m.group(2)
        schema = TableSchema(table=table, raw_snippet=body[:500])
        for col_m in re.finditer(
            r"^\s*([\"`]?\w+[\"`]?)\s+([\w\(\),\s]+?)(?:,|\s*$)",
            body,
            re.I | re.M,
        ):
            col_name = normalize_identifier(col_m.group(1))
            if col_name in {"primary", "constraint", "foreign", "unique", "check"}:
                continue
            col_type = normalize_type(col_m.group(2).split()[0] if col_m.group(2) else "unknown")
            mods: set[str] = set()
            frag = col_m.group(0).lower()
            if "not null" in frag:
                mods.add("not_null")
            if "null" in frag and "not null" not in frag:
                mods.add("nullable")
            schema.columns[col_name] = ColumnDef(col_name, col_type, frozenset(mods))
        tables[table] = schema
    return tables


def parse_sql_views(sql: str) -> set[str]:
    views: set[str] = set()
    for m in re.finditer(
        r"CREATE\s+(?:OR\s+REPLACE\s+)?(?:MATERIALIZED\s+)?VIEW\s+(?:IF\s+NOT\s+EXISTS\s+)?([\"`]?(?:\w+\.)?\w+[\"`]?)",
        sql,
        re.I,
    ):
        v = normalize_table(m.group(1))
        if v:
            views.add(v)
    return views


def parse_sql_indexes(sql: str) -> set[str]:
    indexes: set[str] = set()
    for m in re.finditer(
        r"CREATE\s+(?:UNIQUE\s+)?INDEX\s+(?:IF\s+NOT\s+EXISTS\s+)?([\"`]?\w+[\"`]?)\s+ON\s+([\"`]?(?:\w+\.)?\w+[\"`]?)",
        sql,
        re.I,
    ):
        idx = normalize_identifier(m.group(1))
        tbl = normalize_table(m.group(2))
        if idx and tbl:
            indexes.add(f"{idx} ON {tbl}")
    return indexes


def parse_sql_types(sql: str) -> set[str]:
    types: set[str] = set()
    for m in re.finditer(r"CREATE\s+TYPE\s+([\"`]?(?:\w+\.)?\w+[\"`]?)", sql, re.I):
        t = normalize_identifier(m.group(1))
        if t:
            types.add(t)
    return types


def parse_sql_schemas(sql: str) -> set[str]:
    schemas: set[str] = set()
    for m in re.finditer(r"CREATE\s+SCHEMA\s+(?:IF\s+NOT\s+EXISTS\s+)?([\"`]?\w+[\"`]?)", sql, re.I):
        s = normalize_identifier(m.group(1))
        if s:
            schemas.add(s)
    return schemas


def parse_sql_extensions(sql: str) -> set[str]:
    exts: set[str] = set()
    for m in re.finditer(r"CREATE\s+EXTENSION\s+(?:IF\s+NOT\s+EXISTS\s+)?([\"`]?\w+[\"`]?)", sql, re.I):
        e = normalize_identifier(m.group(1))
        if e:
            exts.add(e)
    return exts


def parse_sql_foreign_keys(sql: str, default_table: str = "") -> set[str]:
    fks: set[str] = set()
    for m in re.finditer(
        r"FOREIGN\s+KEY\s*\(\s*([\"`]?\w+[\"`]?)\s*\)\s*REFERENCES\s+([\"`]?(?:\w+\.)?\w+[\"`]?)",
        sql,
        re.I,
    ):
        col = normalize_identifier(m.group(1))
        ref = normalize_table(m.group(2)) or ""
        if default_table and col and ref:
            fks.add(f"{default_table}.{col}->{ref}")
    for m in re.finditer(
        r"ALTER\s+TABLE\s+([\"`]?(?:\w+\.)?\w+[\"`]?)\s+ADD\s+(?:CONSTRAINT\s+\w+\s+)?FOREIGN\s+KEY\s*\(\s*([\"`]?\w+[\"`]?)\s*\)\s*REFERENCES\s+([\"`]?(?:\w+\.)?\w+[\"`]?)",
        sql,
        re.I,
    ):
        tbl = normalize_table(m.group(1)) or ""
        col = normalize_identifier(m.group(2))
        ref = normalize_table(m.group(3)) or ""
        if tbl and col and ref:
            fks.add(f"{tbl}.{col}->{ref}")
    return fks


def parse_schema_drops(body: str) -> set[str]:
    drops: set[str] = set()
    for match in re.finditer(
        r"Schema::(?:drop|dropIfExists)\s*\(\s*['\"]([^'\"]+)['\"]", body, re.I
    ):
        t = normalize_table(match.group(1))
        if t:
            drops.add(t)
    for sql in extract_db_sql_strings(body):
        for m in re.finditer(
            r"DROP\s+TABLE\s+(?:IF\s+EXISTS\s+)?([\"`]?(?:\w+\.)?\w+[\"`]?)", sql, re.I
        ):
            t = normalize_table(m.group(1))
            if t:
                drops.add(t)
    return drops


def ingest_body(
    rec: MigrationRecord,
    body: str,
    target_tables: dict[str, TableSchema],
    *,
    collect_indexes: bool = True,
    collect_fks: bool = True,
    collect_views: bool = True,
    collect_types: bool = True,
    collect_schemas: bool = True,
    collect_extensions: bool = True,
) -> None:
    for table, block_body in extract_schema_create_blocks(body):
        schema = TableSchema(table=table, raw_snippet=block_body[:800])
        schema.columns = parse_blueprint_columns(block_body)
        schema.indexes = parse_blueprint_indexes(block_body, table)
        schema.foreign_keys = parse_blueprint_foreign_keys(block_body, table)
        target_tables[table] = schema
        if collect_indexes:
            rec.indexes_created.update(schema.indexes)
        if collect_fks:
            rec.foreign_keys_created.update(schema.foreign_keys)

    for sql in extract_db_sql_strings(body):
        for table, schema in parse_sql_create_table(sql).items():
            if table in target_tables:
                existing = target_tables[table]
                existing.columns.update(schema.columns)
                existing.indexes.update(schema.indexes)
            else:
                target_tables[table] = schema
            if collect_indexes:
                rec.indexes_created.update(parse_sql_indexes(sql))
            if collect_fks:
                rec.foreign_keys_created.update(parse_sql_foreign_keys(sql, table))
        if collect_views:
            for view in parse_sql_views(sql):
                rec.views_created.add(view)
                rec.view_create_or_replace[view] = bool(
                    re.search(r"CREATE\s+OR\s+REPLACE\s+VIEW", sql, re.I)
                )
        if collect_indexes:
            rec.indexes_created.update(parse_sql_indexes(sql))
        if collect_types:
            rec.types_created.update(parse_sql_types(sql))
        if collect_schemas:
            for sch in parse_sql_schemas(sql):
                rec.schemas_created.add(sch)
                rec.schema_create_if_not_exists[sch] = bool(
                    re.search(r"CREATE\s+SCHEMA\s+IF\s+NOT\s+EXISTS", sql, re.I)
                )
        if collect_extensions:
            for ext in parse_sql_extensions(sql):
                rec.extensions_created.add(ext)
                rec.extension_create_if_not_exists[ext] = bool(
                    re.search(r"CREATE\s+EXTENSION\s+IF\s+NOT\s+EXISTS", sql, re.I)
                )
        if collect_fks:
            rec.foreign_keys_created.update(parse_sql_foreign_keys(sql))

    if collect_views:
        for m in re.finditer(
            r"CREATE\s+(OR\s+REPLACE\s+)?VIEW\s+([\"`]?(?:\w+\.)?\w+[\"`]?)",
            body,
            re.I,
        ):
            v = normalize_table(m.group(2))
            if v:
                rec.views_created.add(v)
                rec.view_create_or_replace[v] = bool(m.group(1))


def parse_migration(path: Path) -> Optional[MigrationRecord]:
    m = TIMESTAMP_RE.match(path.name)
    if not m:
        return None
    content = path.read_text(encoding="utf-8", errors="replace")
    up = extract_up_method(content)
    down = extract_down_method(content)
    rec = MigrationRecord(
        path=path,
        filename=path.name,
        timestamp=m.group(1),
        suffix=m.group(2),
        relative_path=str(path.relative_to(ROOT)),
        up_body=up,
        down_body=down,
    )
    rec.tables_dropped_up = parse_schema_drops(up)
    ingest_body(rec, up, rec.tables_created)
    ingest_body(
        rec,
        down,
        rec.tables_created_down,
        collect_indexes=False,
        collect_fks=False,
        collect_views=False,
        collect_types=False,
        collect_schemas=False,
        collect_extensions=False,
    )
    return rec


def compare_table_schemas(a: TableSchema, b: TableSchema) -> str:
    """Return: identical | a_superset | b_superset | divergent"""
    sig_a = a.column_signatures()
    sig_b = b.column_signatures()
    names_a = set(sig_a)
    names_b = set(sig_b)

    if not names_a and not names_b:
        return "identical"

    if names_a == names_b:
        shared = names_a
        if all(sig_a[n] == sig_b[n] for n in shared):
            return "identical"
        # same columns, different definitions
        diffs = [n for n in shared if sig_a[n] != sig_b[n]]
        if diffs:
            return "divergent"

    if names_a > names_b and names_b.issubset(names_a):
        if all(sig_a.get(n) == sig_b.get(n) for n in names_b):
            return "a_superset"
        return "divergent"
    if names_b > names_a and names_a.issubset(names_b):
        if all(sig_a.get(n) == sig_b.get(n) for n in names_a):
            return "b_superset"
        return "divergent"
    overlap = names_a & names_b
    if overlap:
        conflicting = [n for n in overlap if sig_a[n] != sig_b[n]]
        if conflicting:
            return "divergent"
    only_a = names_a - names_b
    only_b = names_b - names_a
    if only_a and only_b:
        return "divergent"
    if only_a:
        return "a_superset"
    if only_b:
        return "b_superset"
    return "divergent"


def format_column_sig(sig: tuple[str, str, frozenset[str]]) -> str:
    name, col_type, mods = sig
    mod_str = ", ".join(sorted(mods)) if mods else "none"
    return f"{name} ({col_type}; {mod_str})"


def schema_diff_detail(a: TableSchema, b: TableSchema) -> list[str]:
    lines: list[str] = []
    sig_a = a.column_signatures()
    sig_b = b.column_signatures()
    for col in sorted(set(sig_a) | set(sig_b)):
        in_a = col in sig_a
        in_b = col in sig_b
        if in_a and not in_b:
            lines.append(f"- Column `{col}` only in first migration — {format_column_sig(sig_a[col])}")
        elif in_b and not in_a:
            lines.append(f"- Column `{col}` only in second migration — {format_column_sig(sig_b[col])}")
        elif sig_a[col] != sig_b[col]:
            lines.append(
                f"- Column `{col}` differs: {format_column_sig(sig_a[col])} vs {format_column_sig(sig_b[col])}"
            )
    return lines


def recommendation_for_duplicate_pair(
    mig_a: MigrationRecord,
    mig_b: MigrationRecord,
    table: str,
    relation: str,
) -> str:
    drops_a = table in mig_a.tables_dropped_up
    drops_b = table in mig_b.tables_dropped_up

    if relation == "identical":
        if drops_a or drops_b:
            dropper = mig_a if drops_a else mig_b
            other = mig_b if drops_a else mig_a
            return (
                f"**Identical schemas; drop-and-recreate pattern.** `{dropper.filename}` "
                f"calls `dropIfExists`/`DROP` on `{table}` before creating. "
                f"`{other.filename}` may use `Schema::hasTable` guards. "
                f"On fresh migrate both may run — confirm intended order. "
                f"Recommend consolidating to one migration after manual review; do not delete silently."
            )
        keep = mig_a if mig_a.timestamp <= mig_b.timestamp else mig_b
        drop = mig_b if keep is mig_a else mig_a
        return (
            f"**Identical.** Recommend archiving or deleting `{drop.relative_path}` "
            f"and keeping `{keep.relative_path}` (earlier timestamp). "
            f"Do not delete without confirming neither has run in production with divergent history."
        )
    if relation == "a_superset":
        return (
            f"**Superset.** `{mig_a.relative_path}` has all columns of `{mig_b.relative_path}` plus extras. "
            f"Recommend keeping `{mig_a.filename}` and converting `{mig_b.filename}` "
            f"table `{table}` create into a no-op (or removing that create block). Manual review required."
        )
    if relation == "b_superset":
        return (
            f"**Superset.** `{mig_b.relative_path}` has all columns of `{mig_a.relative_path}` plus extras. "
            f"Recommend keeping `{mig_b.filename}` and converting `{mig_a.filename}` "
            f"table `{table}` create into a no-op (or removing that create block). Manual review required."
        )
    return (
        f"**Divergent schemas.** Manual review required. Do not auto-delete either migration. "
        f"Reconcile column definitions before consolidating."
    )


def run_dependency_audit() -> dict:
    paths = sorted(MIGRATIONS_ROOT.rglob("*.php"), key=lambda p: p.name)
    migrations: list = []
    skipped: list[str] = []
    for i, path in enumerate(paths):
        info = _dep.parse_migration(path, i)
        if info:
            migrations.append(info)
        else:
            skipped.append(path.name)

    mig_by_name = {m.filename: m for m in migrations}
    creators_map = _dep.build_table_creators(migrations)
    original_ts = {m.filename: m.timestamp for m in migrations}

    orig_violations = _dep.find_violations(migrations, creators_map, original_ts)
    orig_unique: dict[tuple[str, str, str], object] = {}
    for v in orig_violations:
        orig_unique[(v.consumer, v.provider, v.table)] = v
    orig_violations = list(orig_unique.values())
    orig_ordering = _dep.ordering_violations(orig_violations, original_ts)
    cycles = _dep.detect_cycles_from_violations(orig_ordering)
    post_unknown = [v for v in orig_violations if v.provider == "UNKNOWN"]

    return {
        "total": len(migrations),
        "skipped": skipped,
        "ordering_violations": orig_ordering,
        "unknown_refs": post_unknown,
        "cycles": cycles,
    }


def main() -> int:
    paths = sorted(MIGRATIONS_ROOT.rglob("*.php"), key=lambda p: (p.name, str(p)))
    records: list[MigrationRecord] = []
    skipped: list[str] = []
    for path in paths:
        rec = parse_migration(path)
        if rec:
            records.append(rec)
        else:
            skipped.append(str(path.relative_to(ROOT)))

    # Object registries: object -> list of (migration, detail)
    table_creates: dict[str, list[tuple[MigrationRecord, TableSchema]]] = defaultdict(list)
    view_creates: dict[str, list[MigrationRecord]] = defaultdict(list)
    index_creates: dict[str, list[MigrationRecord]] = defaultdict(list)
    type_creates: dict[str, list[MigrationRecord]] = defaultdict(list)
    schema_creates: dict[str, list[MigrationRecord]] = defaultdict(list)
    extension_creates: dict[str, list[MigrationRecord]] = defaultdict(list)
    fk_creates: dict[str, list[MigrationRecord]] = defaultdict(list)

    for rec in records:
        for table, schema in rec.tables_created.items():
            table_creates[table].append((rec, schema))
        for view in rec.views_created:
            view_creates[view].append(rec)
        for idx in rec.indexes_created:
            index_creates[idx].append(rec)
        for typ in rec.types_created:
            type_creates[typ].append(rec)
        for sch in rec.schemas_created:
            schema_creates[sch].append(rec)
        for ext in rec.extensions_created:
            extension_creates[ext].append(rec)
        for fk in rec.foreign_keys_created:
            fk_creates[fk].append(rec)

    dep = run_dependency_audit()

    # Tables created in up() then dropped by a later migration's up()
    table_first_create: dict[str, MigrationRecord] = {}
    create_then_drop: list[tuple[str, MigrationRecord, MigrationRecord]] = []
    sorted_records = sorted(records, key=lambda r: (r.timestamp, r.relative_path))
    for rec in sorted_records:
        for table in rec.tables_created:
            if table not in table_first_create:
                table_first_create[table] = rec
    for rec in sorted_records:
        for table in rec.tables_dropped_up:
            creator = table_first_create.get(table)
            if creator and creator.filename != rec.filename and creator.timestamp < rec.timestamp:
                create_then_drop.append((table, creator, rec))

    # down() creates that duplicate another migration's up() create (rollback-only)
    down_up_dupes: list[tuple[str, MigrationRecord, MigrationRecord]] = []
    up_creators: dict[str, list[MigrationRecord]] = defaultdict(list)
    for rec in records:
        for table in rec.tables_created:
            up_creators[table].append(rec)
    for rec in records:
        for table in rec.tables_created_down:
            for up_rec in up_creators.get(table, []):
                if up_rec.filename != rec.filename:
                    down_up_dupes.append((table, up_rec, rec))

    lines = [
        "# Migration Consistency Report",
        "",
        f"Generated: {datetime.now().isoformat()}",
        "",
        "## Executive Summary",
        "",
        f"| Metric | Count |",
        f"|--------|------:|",
        f"| Migrations scanned | {len(records)} |",
        f"| Files skipped (no timestamp) | {len(skipped)} |",
        f"| Tables with multiple CREATE migrations | {sum(1 for t, ms in table_creates.items() if len(ms) > 1)} |",
        f"| Views created more than once | {sum(1 for v, ms in view_creates.items() if len(ms) > 1)} |",
        f"| Indexes created more than once | {sum(1 for i, ms in index_creates.items() if len(ms) > 1)} |",
        f"| Types created more than once | {sum(1 for t, ms in type_creates.items() if len(ms) > 1)} |",
        f"| Schemas created more than once | {sum(1 for s, ms in schema_creates.items() if len(ms) > 1)} |",
        f"| Extensions created more than once | {sum(1 for e, ms in extension_creates.items() if len(ms) > 1)} |",
        f"| Foreign keys created more than once | {sum(1 for f, ms in fk_creates.items() if len(ms) > 1)} |",
        f"| Tables created then dropped (lifecycle) | {len(create_then_drop)} |",
        f"| down() creates duplicating up() elsewhere | {len(down_up_dupes)} |",
        f"| Dependency ordering violations | {len(dep['ordering_violations'])} |",
        f"| Unknown table references | {len(dep['unknown_refs'])} |",
        f"| Circular dependencies | {len(dep['cycles'])} |",
        "",
        "> **Policy:** This report never recommends silent deletion. All consolidation "
        "requires manual review and confirmation against production migration history.",
        "",
    ]

    # Duplicate CREATE TABLE migrations with classification
    dup_tables = {t: ms for t, ms in table_creates.items() if len(ms) > 1}
    lines += ["## Duplicate CREATE TABLE Migrations", ""]
    if not dup_tables:
        lines.append("No tables are created by more than one migration.")
        lines.append("")
    else:
        identical_count = superset_count = divergent_count = 0
        for table in sorted(dup_tables):
            migrations_for_table = dup_tables[table]
            lines.append(f"### Table: `{table}`")
            lines.append("")
            lines.append("| Migration | Path | Timestamp | Columns | Drops table in up()? |")
            lines.append("|-----------|------|-----------|--------:|:--------------------:|")
            for rec, schema in migrations_for_table:
                drops = "yes" if table in rec.tables_dropped_up else "no"
                lines.append(
                    f"| `{rec.filename}` | `{rec.relative_path}` | {rec.timestamp} | {len(schema.columns)} | {drops} |"
                )
            lines.append("")

            # pairwise comparison
            pairs_done: set[tuple[str, str]] = set()
            for i in range(len(migrations_for_table)):
                for j in range(i + 1, len(migrations_for_table)):
                    rec_a, schema_a = migrations_for_table[i]
                    rec_b, schema_b = migrations_for_table[j]
                    pair_key = tuple(sorted([rec_a.filename, rec_b.filename]))
                    if pair_key in pairs_done:
                        continue
                    pairs_done.add(pair_key)
                    relation = compare_table_schemas(schema_a, schema_b)
                    if relation == "identical":
                        identical_count += 1
                    elif relation in ("a_superset", "b_superset"):
                        superset_count += 1
                    else:
                        divergent_count += 1

                    label = {
                        "identical": "A) Identical",
                        "a_superset": "B) Superset (first ⊃ second)",
                        "b_superset": "B) Superset (second ⊃ first)",
                        "divergent": "C) Divergent",
                    }[relation]
                    lines.append(f"#### Pair: `{rec_a.filename}` ↔ `{rec_b.filename}` — {label}")
                    lines.append("")
                    rec = recommendation_for_duplicate_pair(rec_a, rec_b, table, relation)
                    lines.append(rec)
                    lines.append("")
                    if relation == "divergent":
                        lines.append("**Column differences:**")
                        lines.extend(schema_diff_detail(schema_a, schema_b))
                        lines.append("")
            lines.append("---")
            lines.append("")

        lines += [
            "### CREATE TABLE Classification Totals",
            "",
            f"- Identical pairs: {identical_count}",
            f"- Superset pairs: {superset_count}",
            f"- Divergent pairs: {divergent_count}",
            "",
        ]

    # Duplicate views
    lines += ["## Duplicate CREATE VIEW Migrations", ""]
    dup_views = {v: ms for v, ms in view_creates.items() if len(ms) > 1}
    if dup_views:
        for view, ms in sorted(dup_views.items()):
            lines.append(f"### View: `{view}`")
            for rec in ms:
                or_replace = rec.view_create_or_replace.get(view, False)
                guard = "has runtime guard" if "hasTable" in rec.up_body or "migrations" in rec.up_body else ""
                lines.append(
                    f"- `{rec.relative_path}` ({rec.timestamp})"
                    f"{' — CREATE OR REPLACE' if or_replace else ' — plain CREATE VIEW'}"
                    f"{f' — {guard}' if guard else ''}"
                )
            lines.append(
                "- **Recommendation:** Later migrations that use `DROP VIEW IF EXISTS` + "
                "`CREATE OR REPLACE` are usually intentional replacements. "
                "Consolidate only if both run unconditionally on fresh migrate."
            )
            lines.append("")
    else:
        lines.append("No views created by more than one migration.")
        lines.append("")

    lines += ["## Tables Created Then Dropped (Forward Lifecycle)", ""]
    if create_then_drop:
        seen_lifecycle: set[tuple[str, str, str]] = set()
        for table, creator, dropper in create_then_drop:
            key = (table, creator.filename, dropper.filename)
            if key in seen_lifecycle:
                continue
            seen_lifecycle.add(key)
            lines.append(f"### `{table}`")
            lines.append(f"- Created: `{creator.relative_path}` ({creator.timestamp})")
            lines.append(f"- Dropped: `{dropper.relative_path}` ({dropper.timestamp})")
            lines.append(
                "- **Note:** Not a duplicate CREATE on forward migrate if drop runs after create. "
                "Verify ordering is correct for fresh installs."
            )
            lines.append("")
    else:
        lines.append("No create-then-drop lifecycle conflicts detected.")
        lines.append("")

    lines += ["## down() CREATE Duplicating up() Elsewhere (Rollback Path)", ""]
    if down_up_dupes:
        seen_down: set[tuple[str, str, str]] = set()
        for table, up_rec, down_rec in sorted(down_up_dupes, key=lambda x: (x[0], x[1].timestamp)):
            key = (table, up_rec.filename, down_rec.filename)
            if key in seen_down:
                continue
            seen_down.add(key)
            lines.append(
                f"- `{table}`: up in `{up_rec.relative_path}`; "
                f"down() create in `{down_rec.relative_path}`"
            )
        lines.append("")
        lines.append(
            "These do not duplicate on `php artisan migrate` forward, but rollback paths "
            "may recreate schemas already defined elsewhere."
        )
        lines.append("")
    else:
        lines.append("None detected.")
        lines.append("")

    # Duplicate indexes
    lines += ["## Duplicate CREATE INDEX Migrations", ""]
    dup_indexes = {i: ms for i, ms in index_creates.items() if len(ms) > 1}
    if dup_indexes:
        for idx, ms in sorted(dup_indexes.items()):
            lines.append(f"### Index: `{idx}`")
            for rec in ms:
                lines.append(f"- `{rec.relative_path}`")
            lines.append(
                "- **Recommendation:** Keep one index definition; remove duplicates or use "
                "`IF NOT EXISTS` where supported. Manual review required."
            )
            lines.append("")
    else:
        lines.append("No indexes created by more than one migration.")
        lines.append("")

    # Types, schemas, extensions
    for title, registry, obj_label in (
        ("CREATE TYPE", type_creates, "Type"),
        ("CREATE SCHEMA", schema_creates, "Schema"),
        ("CREATE EXTENSION", extension_creates, "Extension"),
    ):
        lines += [f"## Duplicate {title} Statements", ""]
        dups = {k: ms for k, ms in registry.items() if len(ms) > 1}
        if dups:
            for name, ms in sorted(dups.items()):
                lines.append(f"### {obj_label}: `{name}`")
                for rec in ms:
                    extra = ""
                    if title == "CREATE SCHEMA":
                        if rec.schema_create_if_not_exists.get(name):
                            extra = " (IF NOT EXISTS)"
                        elif "DROP SCHEMA" in rec.up_body:
                            extra = " (DROP SCHEMA first)"
                    if title == "CREATE EXTENSION" and rec.extension_create_if_not_exists.get(name):
                        extra = " (IF NOT EXISTS)"
                    lines.append(f"- `{rec.relative_path}` ({rec.timestamp}){extra}")
                if title == "CREATE SCHEMA" or title == "CREATE EXTENSION":
                    lines.append(
                        "- **Recommendation:** `IF NOT EXISTS` makes re-runs safe; "
                        "duplicate statements are redundant but usually harmless. "
                        "Consider consolidating to the earliest migration."
                    )
                else:
                    lines.append(
                        "- **Recommendation:** Manual review — duplicate types will fail on fresh migrate."
                    )
                lines.append("")
        else:
            lines.append(f"No duplicate {title} statements detected.")
            lines.append("")

    # Foreign keys
    lines += ["## Foreign Keys Created More Than Once", ""]
    dup_fks = {f: ms for f, ms in fk_creates.items() if len(ms) > 1}
    if dup_fks:
        for fk, ms in sorted(dup_fks.items()):
            lines.append(f"### `{fk}`")
            for rec in ms:
                lines.append(f"- `{rec.relative_path}`")
            lines.append(
                "- **Recommendation:** Remove duplicate FK adds from later migrations "
                "or guard with schema introspection. Manual review required."
            )
            lines.append("")
    else:
        lines.append("No foreign keys created by more than one migration.")
        lines.append("")

    # Dependency ordering section
    lines += ["## Dependency Ordering Violations", ""]
    if dep["ordering_violations"]:
        by_consumer: dict[str, list] = defaultdict(list)
        for v in sorted(dep["ordering_violations"], key=lambda x: x.consumer):
            by_consumer[v.consumer].append(v)
        for consumer, vs in sorted(by_consumer.items()):
            lines.append(f"### `{consumer}`")
            for v in vs:
                lines.append(
                    f"- Needs `{v.table}` from `{v.provider}` — {v.reason}"
                )
            lines.append("")
    else:
        lines.append("No timestamp ordering violations detected (by filename order).")
        lines.append("")

    if dep["unknown_refs"]:
        lines += ["## Unknown Table References", ""]
        seen: set[tuple[str, str]] = set()
        for v in dep["unknown_refs"]:
            key = (v.consumer, v.table)
            if key in seen:
                continue
            seen.add(key)
            lines.append(f"- `{v.consumer}` references `{v.table}` (no CREATE migration found)")
        lines.append("")

    if dep["cycles"]:
        lines += ["## Circular Dependencies", ""]
        for a, b in dep["cycles"]:
            lines.append(f"- `{a}` ↔ `{b}`")
        lines.append("")

    if skipped:
        lines += ["## Skipped Files", ""]
        for s in skipped[:50]:
            lines.append(f"- `{s}`")
        if len(skipped) > 50:
            lines.append(f"- … and {len(skipped) - 50} more")
        lines.append("")

    lines += [
        "## Methodology",
        "",
        "- Parsed `up()` and `down()` bodies from all `database/migrations/**/*.php` files.",
        "- Duplicate CREATE TABLE analysis uses **`up()` only** (forward migrate path).",
        "- Detected `Schema::create`, `DB::statement` SQL, blueprint columns/indexes/FKs.",
        "- Table schema comparison uses column names, normalized types, and nullable/unique modifiers.",
        "- Dependency analysis reuses `scripts/migration_dependency_analyzer.py`.",
        "- Comparisons are static; runtime guards (`Schema::hasTable`, `IF NOT EXISTS`) are not executed.",
        "",
    ]

    REPORT_PATH.write_text("\n".join(lines), encoding="utf-8")
    print(f"Report written to {REPORT_PATH}")
    print(
        f"tables_dup={len(dup_tables)} views_dup={len(dup_views)} "
        f"indexes_dup={len(dup_indexes)} fks_dup={len(dup_fks)} "
        f"ordering={len(dep['ordering_violations'])}"
    )
    return 0


if __name__ == "__main__":
    sys.exit(main())

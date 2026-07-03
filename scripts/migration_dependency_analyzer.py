#!/usr/bin/env python3
"""
Laravel migration dependency analyzer with minimal timestamp renames.
"""

from __future__ import annotations

import re
import sys
from collections import defaultdict
from dataclasses import dataclass, field
from datetime import datetime, timedelta
from pathlib import Path
from typing import Optional


ROOT = Path(__file__).resolve().parent.parent
MIGRATIONS_ROOT = ROOT / "database" / "migrations"
REPORT_PATH = ROOT / "migration_dependency_report.md"

TIMESTAMP_RE = re.compile(r"^(\d{4}_\d{2}_\d{2}_\d{6})_(.+)\.php$")

BLOCKLIST = frozenset(
    {
        "select", "where", "set", "values", "into", "from", "join", "on", "as",
        "and", "or", "not", "null", "true", "false", "case", "when", "then",
        "else", "end", "exists", "if", "only", "using", "with", "without",
        "integer", "bigint", "smallint", "text", "varchar", "boolean", "uuid",
        "timestamp", "date", "numeric", "decimal", "real", "double", "serial",
        "the", "that", "this", "dynamics", "dual", "lateral", "unnest",
    }
)

SYSTEM_PREFIXES = ("information_schema.", "pg_catalog.", "pg_toast.")


def normalize_table(name: str) -> Optional[str]:
    name = name.strip().strip('"`[]')
    if not name:
        return None
    lower = name.lower()
    if lower in BLOCKLIST:
        return None
    for prefix in SYSTEM_PREFIXES:
        if lower.startswith(prefix):
            return None
    if lower.startswith("pg_"):
        return None
    return lower


def extract_up_method(content: str) -> str:
    m = re.search(r"function\s+up\s*\(\s*\)\s*(?::\s*\w+)?\s*\{", content, re.I)
    if not m:
        return content
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


def extract_db_sql_strings(content: str) -> list[str]:
    strings: list[str] = []
    for m in re.finditer(r"DB::(?:statement|unprepared)\s*\(\s*(['\"])", content, re.I):
        quote = m.group(1)
        pos = m.end()
        if pos < len(content) and content[pos : pos + 3] == "<<<":
            hm = re.match(r"<<<'(\w+)'|<<<(\w+)", content[pos:])
            if hm:
                marker = hm.group(1) or hm.group(2)
                em = re.search(rf"^{re.escape(marker)}\s*;", content[pos:], re.M)
                if em:
                    strings.append(content[pos : pos + em.start()])
            continue
        buf: list[str] = []
        escaped = False
        while pos < len(content):
            c = content[pos]
            if escaped:
                buf.append(c)
                escaped = False
            elif c == "\\":
                escaped = True
            elif c == quote:
                strings.append("".join(buf))
                break
            else:
                buf.append(c)
            pos += 1
    return strings


def parse_sql_tables(sql: str) -> dict[str, set[str]]:
    result = {
        "creates": set(),
        "alters": set(),
        "drops": set(),
        "reads": set(),
        "fk_targets": set(),
    }

    def add(bucket: str, raw: str) -> None:
        t = normalize_table(raw)
        if t:
            result[bucket].add(t)

    for m in re.finditer(
        r"\bCREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?([\"`]?(?:\w+\.)?\w+[\"`]?)", sql, re.I
    ):
        add("creates", m.group(1))
    for m in re.finditer(
        r"\bALTER\s+TABLE\s+(?:ONLY\s+)?(?:IF\s+EXISTS\s+)?([\"`]?(?:\w+\.)?\w+[\"`]?)", sql, re.I
    ):
        add("alters", m.group(1))
    for m in re.finditer(
        r"\bDROP\s+TABLE\s+(?:IF\s+EXISTS\s+)?([\"`]?(?:\w+\.)?\w+[\"`]?)", sql, re.I
    ):
        add("drops", m.group(1))
    for m in re.finditer(r"\bUPDATE\s+([\"`]?(?:\w+\.)?\w+[\"`]?)\s+SET", sql, re.I):
        add("reads", m.group(1))
    for m in re.finditer(r"\bINSERT\s+INTO\s+([\"`]?(?:\w+\.)?\w+[\"`]?)", sql, re.I):
        add("reads", m.group(1))
    for m in re.finditer(r"\bDELETE\s+FROM\s+([\"`]?(?:\w+\.)?\w+[\"`]?)", sql, re.I):
        add("reads", m.group(1))
    for m in re.finditer(
        r"\bTRUNCATE\s+(?:TABLE\s+)?([\"`]?(?:\w+\.)?\w+[\"`]?)", sql, re.I
    ):
        add("drops", m.group(1))
    for m in re.finditer(r"\bREFERENCES\s+([\"`]?(?:\w+\.)?\w+[\"`]?)", sql, re.I):
        add("fk_targets", m.group(1))
    for m in re.finditer(r"\bFROM\s+([\"`]?(?:\w+\.)?\w+[\"`]?)", sql, re.I):
        add("reads", m.group(1))
    for m in re.finditer(r"\bJOIN\s+([\"`]?(?:\w+\.)?\w+[\"`]?)", sql, re.I):
        add("reads", m.group(1))
    return result


@dataclass
class MigrationInfo:
    path: Path
    filename: str
    timestamp: str
    suffix: str
    sort_key: tuple
    original_index: int
    creates: set[str] = field(default_factory=set)
    alters: set[str] = field(default_factory=set)
    drops: set[str] = field(default_factory=set)
    renames: list[tuple[str, str]] = field(default_factory=list)
    fk_targets: set[str] = field(default_factory=set)
    reads: set[str] = field(default_factory=set)
    pinned_last: bool = False

    @property
    def requires(self) -> set[str]:
        return (self.alters | self.drops | self.fk_targets | self.reads) - self.creates

    @property
    def is_create(self) -> bool:
        return bool(self.creates)


def add_table(target: set[str], raw: str) -> None:
    t = normalize_table(raw)
    if t:
        target.add(t)


def parse_migration(path: Path, index: int) -> Optional[MigrationInfo]:
    m = TIMESTAMP_RE.match(path.name)
    if not m:
        return None

    content = path.read_text(encoding="utf-8", errors="replace")
    up = extract_up_method(content)
    info = MigrationInfo(
        path=path,
        filename=path.name,
        timestamp=m.group(1),
        suffix=m.group(2),
        sort_key=(m.group(1), index),
        original_index=index,
        pinned_last=m.group(1).startswith("9999_"),
    )

    for match in re.finditer(r"Schema::create\s*\(\s*['\"]([^'\"]+)['\"]", up, re.I):
        add_table(info.creates, match.group(1))
    for match in re.finditer(r"Schema::(?:table|drop|dropIfExists)\s*\(\s*['\"]([^'\"]+)['\"]", up, re.I):
        ctx = up[max(0, match.start() - 30) : match.start() + 20].lower()
        if "drop" in ctx:
            add_table(info.drops, match.group(1))
        else:
            add_table(info.alters, match.group(1))
    for match in re.finditer(
        r"Schema::rename\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]([^'\"]+)['\"]", up, re.I
    ):
        old = normalize_table(match.group(1))
        new = normalize_table(match.group(2))
        if old and new:
            info.renames.append((old, new))
            add_table(info.alters, old)
    for match in re.finditer(r"Schema::has(?:Table|Column)\s*\(\s*['\"]([^'\"]+)['\"]", up, re.I):
        add_table(info.reads, match.group(1))
    for match in re.finditer(r"\$this->safeTable\s*\(\s*['\"]([^'\"]+)['\"]", up, re.I):
        add_table(info.alters, match.group(1))
    for match in re.finditer(r"DB::table\s*\(\s*['\"]([^'\"]+)['\"]", up, re.I):
        add_table(info.reads, match.group(1))
    for pattern in (
        r"->on\s*\(\s*['\"]([^'\"]+)['\"]",
        r"->constrained\s*\(\s*['\"]([^'\"]+)['\"]",
        r"->references\s*\([^)]+\)\s*->on\s*\(\s*['\"]([^'\"]+)['\"]",
    ):
        for match in re.finditer(pattern, up, re.I):
            add_table(info.fk_targets, match.group(1))

    for sql in extract_db_sql_strings(up):
        parsed = parse_sql_tables(sql)
        info.creates.update(parsed["creates"])
        info.alters.update(parsed["alters"])
        info.drops.update(parsed["drops"])
        info.reads.update(parsed["reads"])
        info.fk_targets.update(parsed["fk_targets"])

    info.fk_targets -= info.creates
    return info


@dataclass
class Violation:
    consumer: str
    provider: str
    table: str
    reason: str
    consumer_index: int
    provider_index: int


def ts_to_dt(ts: str) -> datetime:
    return datetime.strptime(ts, "%Y_%m_%d_%H%M%S")


def dt_to_ts(dt: datetime) -> str:
    return dt.strftime("%Y_%m_%d_%H%M%S")


def build_table_creators(migrations: list[MigrationInfo]) -> dict[str, str]:
    creators: dict[str, str] = {}
    for mig in migrations:
        for t in mig.creates:
            if t not in creators:
                creators[t] = mig.filename
        for _, new in mig.renames:
            if new not in creators:
                creators[new] = mig.filename
    return creators


def sort_migrations(
    migrations: list[MigrationInfo], timestamps: dict[str, str]
) -> list[MigrationInfo]:
    return sorted(
        migrations,
        key=lambda m: (timestamps[m.filename], m.original_index),
    )


def find_violations(
    migrations: list[MigrationInfo],
    creators_map: dict[str, str],
    timestamps: dict[str, str],
) -> list[Violation]:
    filename_to_idx = {m.filename: i for i, m in enumerate(migrations)}
    ordered = sort_migrations(migrations, timestamps)
    existing: set[str] = set()
    rename_map: dict[str, str] = {}
    violations: list[Violation] = []

    def resolve(t: str) -> str:
        seen: set[str] = set()
        while t in rename_map and t not in seen:
            seen.add(t)
            t = rename_map[t]
        return t

    for i, mig in enumerate(ordered):
        local_created = set(mig.creates)

        def missing(t: str) -> bool:
            t = resolve(t)
            return t not in existing and t not in local_created

        for t in sorted(mig.requires):
            if missing(t):
                rt = resolve(t)
                provider = creators_map.get(rt, "UNKNOWN")
                reason = "FK target must exist" if t in mig.fk_targets else "table must exist"
                p_idx = filename_to_idx.get(provider, -1)
                c_idx = filename_to_idx.get(mig.filename, -1)
                violations.append(
                    Violation(mig.filename, provider, rt, reason, c_idx, p_idx)
                )

        for t in mig.creates:
            existing.add(t)
        for old, new in mig.renames:
            existing.discard(resolve(old))
            existing.add(new)
            rename_map[resolve(old)] = new
        for t in mig.drops:
            existing.discard(resolve(t))

    return violations


def ordering_violations(violations: list[Violation], timestamps: dict[str, str]) -> list[Violation]:
    result = []
    for v in violations:
        if v.provider == "UNKNOWN":
            continue
        if ts_to_dt(timestamps[v.provider]) > ts_to_dt(timestamps[v.consumer]):
            result.append(v)
    return result


def preserve_alter_chains(
    migrations: list[MigrationInfo], timestamps: dict[str, str]
) -> None:
    """Ensure same-table alter migrations keep original relative order."""
    table_alters: dict[str, list[MigrationInfo]] = defaultdict(list)
    for mig in sorted(migrations, key=lambda m: m.sort_key):
        if mig.alters:
            for t in mig.alters:
                table_alters[t].append(mig)

    for migs in table_alters.values():
        for i in range(len(migs) - 1):
            a, b = migs[i], migs[i + 1]
            ta, tb = ts_to_dt(timestamps[a.filename]), ts_to_dt(timestamps[b.filename])
            if ta >= tb:
                timestamps[b.filename] = dt_to_ts(ta + timedelta(seconds=1))


def fix_minimal_timestamps(
    migrations: list[MigrationInfo],
    creators_map: dict[str, str],
    mig_by_name: dict[str, MigrationInfo],
) -> dict[str, str]:
    timestamps = {m.filename: m.timestamp for m in migrations}

    # Batch-move convert/ legacy creates before first dependent alter
    convert_migs = sorted(
        [
            m
            for m in migrations
            if "/convert/" in str(m.path).replace("\\", "/")
            and m.timestamp.startswith("2026_04_23_211")
            and m.is_create
        ],
        key=lambda m: m.sort_key,
    )
    if convert_migs:
        anchor = ts_to_dt("2026_02_05_200000")
        for i, m in enumerate(convert_migs):
            timestamps[m.filename] = dt_to_ts(anchor + timedelta(seconds=i))

    # Also move convert non-create that are part of batch? Only creates for now.

    max_iterations = 500
    for _ in range(max_iterations):
        violations = find_violations(migrations, creators_map, timestamps)
        ordering = ordering_violations(violations, timestamps)
        if not ordering:
            break

        changed = False
        for v in ordering:
            provider = mig_by_name[v.provider]
            consumer = mig_by_name[v.consumer]
            p_ts = ts_to_dt(timestamps[v.provider])
            c_ts = ts_to_dt(timestamps[v.consumer])

            if provider.is_create and not consumer.is_create:
                # Move create earlier
                target = c_ts - timedelta(seconds=1)
                if provider.pinned_last:
                    continue
                if p_ts >= c_ts:
                    timestamps[v.provider] = dt_to_ts(target)
                    changed = True
            elif provider.is_create and consumer.is_create:
                if p_ts >= c_ts:
                    timestamps[v.provider] = dt_to_ts(c_ts - timedelta(seconds=1))
                    changed = True
            else:
                # Move consumer later
                if p_ts >= c_ts:
                    timestamps[v.consumer] = dt_to_ts(p_ts + timedelta(seconds=1))
                    changed = True

        preserve_alter_chains(migrations, timestamps)

        # Resolve duplicate timestamps
        by_ts: dict[str, list[str]] = defaultdict(list)
        for name, ts in timestamps.items():
            by_ts[ts].append(name)
        for ts, names in by_ts.items():
            if len(names) > 1:
                base = ts_to_dt(ts)
                for i, name in enumerate(sorted(names, key=lambda n: mig_by_name[n].original_index)):
                    timestamps[name] = dt_to_ts(base + timedelta(seconds=i))
                changed = True

        if not changed:
            break

    # Ensure pinned last stays last
    for m in migrations:
        if m.pinned_last:
            timestamps[m.filename] = "9999_12_31_235959"

    return timestamps


def detect_cycles_from_violations(
    violations: list[Violation],
) -> list[tuple[str, str]]:
    """Detect mutual ordering constraints (A before B and B before A)."""
    before: dict[str, set[str]] = defaultdict(set)
    for v in violations:
        if v.provider != "UNKNOWN":
            before[v.provider].add(v.consumer)
    pairs = []
    for a, bs in before.items():
        for b in bs:
            if a in before.get(b, set()):
                pairs.append((a, b))
    return pairs


def main() -> int:
    apply = "--apply" in sys.argv
    paths = sorted(MIGRATIONS_ROOT.rglob("*.php"), key=lambda p: p.name)
    migrations: list[MigrationInfo] = []
    skipped: list[str] = []

    for i, path in enumerate(paths):
        info = parse_migration(path, i)
        if info:
            migrations.append(info)
        else:
            skipped.append(path.name)

    mig_by_name = {m.filename: m for m in migrations}
    creators_map = build_table_creators(migrations)
    original_ts = {m.filename: m.timestamp for m in migrations}

    orig_violations = find_violations(migrations, creators_map, original_ts)
    orig_unique: dict[tuple[str, str, str], Violation] = {}
    for v in orig_violations:
        orig_unique[(v.consumer, v.provider, v.table)] = v
    orig_violations = list(orig_unique.values())
    orig_ordering = ordering_violations(orig_violations, original_ts)

    new_ts = fix_minimal_timestamps(migrations, creators_map, mig_by_name)

    post_violations = find_violations(migrations, creators_map, new_ts)
    post_unique: dict[tuple[str, str, str], Violation] = {}
    for v in post_violations:
        post_unique[(v.consumer, v.provider, v.table)] = v
    post_violations = list(post_unique.values())
    post_ordering = ordering_violations(post_violations, new_ts)
    post_unknown = [v for v in post_violations if v.provider == "UNKNOWN"]

    cycles = detect_cycles_from_violations(orig_ordering)

    renames = []
    for mig in migrations:
        if new_ts[mig.filename] != mig.timestamp:
            renames.append(
                {
                    "original": mig.filename,
                    "new": f"{new_ts[mig.filename]}_{mig.suffix}.php",
                    "original_ts": mig.timestamp,
                    "new_ts": new_ts[mig.filename],
                    "path": str(mig.path.relative_to(ROOT)),
                }
            )

    reasons: dict[str, list[str]] = defaultdict(list)
    for v in orig_ordering:
        reasons[v.consumer].append(
            f"`{v.table}` created by `{v.provider}` ({v.reason})"
        )

    lines = [
        "# Migration Dependency Report",
        "",
        f"Generated: {datetime.now().isoformat()}",
        "",
        "## Summary",
        "",
        f"- **Migrations scanned:** {len(migrations)}",
        f"- **Skipped files:** {len(skipped)}",
        f"- **Ordering violations (original):** {len(orig_ordering)}",
        f"- **Unknown table references (original):** {len([v for v in orig_violations if v.provider == 'UNKNOWN'])}",
        f"- **Migrations renamed:** {len(renames)}",
        f"- **Ordering violations (after fix):** {len(post_ordering)}",
        f"- **Unknown table references (after fix):** {len(post_unknown)}",
        f"- **Circular dependencies:** {len(cycles)}",
        "",
        "## Strategy",
        "",
        "1. Batch-moved `convert/2026_04_23_211*` CREATE migrations to `2026_02_05_20xxxx` "
        "(before earliest ALTER migrations referencing legacy tables).",
        "2. Iteratively adjusted timestamps for remaining ordering violations, preferring "
        "moving CREATE migrations earlier over moving ALTER migrations later.",
        "3. Preserved same-table ALTER chains and pinned `9999_12_31_235959_add_convert_foreign_keys.php` last.",
        "",
    ]

    if renames:
        lines += [
            "## Renamed Migrations",
            "",
            "| Original | New | Reason |",
            "|----------|-----|--------|",
        ]
        for r in sorted(renames, key=lambda x: x["new_ts"]):
            rs = "; ".join(
                reasons.get(r["original"], ["Convert batch reorder or dependency adjustment"])[:2]
            )
            lines.append(f"| `{r['original']}` | `{r['new']}` | {rs} |")
        lines.append("")

    lines += ["## Ordering Violations (Original)", ""]
    by_consumer: dict[str, list[Violation]] = defaultdict(list)
    for v in sorted(orig_ordering, key=lambda x: x.consumer):
        by_consumer[v.consumer].append(v)
    for consumer, vs in sorted(by_consumer.items()):
        lines.append(f"### `{consumer}`")
        for v in vs:
            lines.append(
                f"- Needs `{v.table}` from `{v.provider}` — {v.reason}"
            )
            lines.append(
                f"  - Chain: `{v.provider}` creates `{v.table}` → required by `{v.consumer}`"
            )
        lines.append("")

    if post_ordering:
        lines += ["## Unresolved Ordering Issues", ""]
        for v in post_ordering:
            lines.append(
                f"- `{v.consumer}` needs `{v.table}` from `{v.provider}`"
            )
        lines.append("")

    if post_unknown:
        lines += ["## Unknown Table References (After Fix)", ""]
        for v in post_unknown:
            lines.append(f"- `{v.consumer}` references `{v.table}` (no CREATE migration found)")
        lines.append("")

    if cycles:
        lines += ["## Circular Dependencies", ""]
        for a, b in cycles:
            lines.append(f"- `{a}` ↔ `{b}`")
        lines.append("")

    lines += ["## Manual Review Items", ""]
    review_tables = sorted(set(v.table for v in post_unknown))
    if review_tables:
        for t in review_tables:
            lines.append(f"- Table `{t}` — no CREATE migration detected; may be a view or false positive")
    else:
        lines.append("None required for ordering.")
    lines.append("")

    REPORT_PATH.write_text("\n".join(lines), encoding="utf-8")

    print(
        f"Scanned={len(migrations)} orig_ordering={len(orig_ordering)} "
        f"renames={len(renames)} post_ordering={len(post_ordering)} "
        f"post_unknown={len(post_unknown)}"
    )

    if apply:
        ops: list[tuple[Path, Path, Path]] = []
        for mig in migrations:
            ts = new_ts[mig.filename]
            if ts == mig.timestamp:
                continue
            new_name = f"{ts}_{mig.suffix}.php"
            new_path = mig.path.parent / new_name
            temp = mig.path.parent / f"__tmp__{mig.filename}"
            ops.append((mig.path, temp, new_path))
        for old, temp, _ in ops:
            old.rename(temp)
        for _, temp, new in ops:
            temp.rename(new)
        print(f"Applied {len(ops)} renames")
    else:
        print("Run with --apply to rename files")

    return 0 if not post_ordering else 2


if __name__ == "__main__":
    sys.exit(main())

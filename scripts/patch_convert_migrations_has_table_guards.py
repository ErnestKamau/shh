#!/usr/bin/env python3
"""Add Schema::hasTable guards to convert/ CREATE migrations (idempotent on legacy DBs)."""

from __future__ import annotations

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
CONVERT = ROOT / "database" / "migrations" / "convert"

UP_RE = re.compile(
    r"(public\s+function\s+up\s*\(\s*\)\s*(?::\s*void)?\s*\{)(\s*)",
    re.MULTILINE,
)


def extract_up_body(text: str) -> str:
    m = re.search(r"function\s+up\s*\(\s*\)\s*(?::\s*\w+)?\s*\{", text)
    if not m:
        return ""
    start = m.end()
    depth = 1
    i = start
    while i < len(text) and depth > 0:
        if text[i] == "{":
            depth += 1
        elif text[i] == "}":
            depth -= 1
        i += 1
    return text[start : i - 1]


def patch_file(path: Path) -> bool:
    text = path.read_text(encoding="utf-8")
    if "Schema::create" not in text or "// No-op" in text:
        return False

    up_body = extract_up_body(text)
    tables = re.findall(r"Schema::create\s*\(\s*['\"]([^'\"]+)['\"]", up_body)
    if not tables or len(tables) > 1:
        return False
    if all(f"hasTable('{t}')" in up_body or f'hasTable("{t}")' in up_body for t in tables):
        return False

    table = tables[0]
    guard = f"\n        if (Schema::hasTable('{table}')) {{\n            return;\n        }}\n"

    new_text, n = UP_RE.subn(lambda m: m.group(1) + m.group(2) + guard, text, count=1)
    if n != 1:
        return False

    new_text = re.sub(r"\{\n        \n        if \(Schema::hasTable", "{\n        if (Schema::hasTable", new_text)
    new_text = re.sub(r"\nSchema::create\(", "\n        Schema::create(", new_text)
    path.write_text(new_text, encoding="utf-8")
    return True


def main() -> int:
    patched = 0
    for path in sorted(CONVERT.glob("*.php")):
        if path.name.startswith("9999_"):
            continue
        if patch_file(path):
            patched += 1
    print(f"Patched {patched} convert migration(s)")
    return 0


if __name__ == "__main__":
    sys.exit(main())

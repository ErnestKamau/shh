"""
language.py - Lightweight response language handling for IMARA AI.

This module intentionally avoids external language detection dependencies.
It only decides between English and Kiswahili for response shaping.
"""

import re
from typing import Optional

SUPPORTED_LANGUAGES = {"en", "sw", "auto"}

_SWAHILI_DIRECTIVE = re.compile(
    r"\b("
    r"swahili|kiswahili|ki[-\s]?swahili|"
    r"ongea|zungumza|jibu|nijibu|tuongee|tumia|kwa\s+kiswahili|"
    r"sema\s+kwa\s+kiswahili|respond\s+in\s+swahili|reply\s+in\s+swahili|"
    r"speak\s+swahili|use\s+swahili"
    r")\b",
    re.IGNORECASE,
)

_ENGLISH_DIRECTIVE = re.compile(
    r"\b(english|respond\s+in\s+english|reply\s+in\s+english|speak\s+english|use\s+english)\b",
    re.IGNORECASE,
)

_SWAHILI_MARKERS = {
    "habari", "tafadhali", "asante", "samahani", "naomba", "nisaidie",
    "msaada", "nini", "gani", "wapi", "lini", "vipi", "kwa", "ya",
    "za", "wa", "ni", "kuna", "sina", "nina", "una", "tuna", "hii",
    "hiyo", "hapa", "hapo", "leo", "kesho", "jana", "tafuta", "onyesha",
    "eleza", "maabara", "sampuli", "matokeo", "hesabu", "ripoti",
}


def normalize_language(language: Optional[str]) -> str:
    value = (language or "auto").strip().lower().replace("_", "-")
    if value in {"sw", "sw-ke", "kiswahili", "swahili"}:
        return "sw"
    if value in {"en", "en-us", "en-gb", "english"}:
        return "en"
    return "auto"


def detect_language(message: str, requested: Optional[str] = None) -> str:
    requested_language = normalize_language(requested)
    if requested_language in {"en", "sw"}:
        return requested_language

    text = (message or "").lower()
    if _ENGLISH_DIRECTIVE.search(text):
        return "en"
    if _SWAHILI_DIRECTIVE.search(text):
        return "sw"

    words = re.findall(r"[a-zA-Z']+", text)
    if not words:
        return "en"

    swahili_hits = sum(1 for word in words if word in _SWAHILI_MARKERS)
    if swahili_hits >= 2 or (swahili_hits >= 1 and len(words) <= 5):
        return "sw"

    return "en"


def language_instruction(language: Optional[str]) -> str:
    if normalize_language(language) == "sw":
        return (
            "Language rule: Reply in natural Kiswahili. Keep standard lab and system terms "
            "such as LIMS, QC, TAT, sample, batch, analyte, and CAPA when they are clearer "
            "for operations. Do not translate database field names, IDs, or internal codes."
        )
    return "Language rule: Reply in clear professional English."


def localize_fixed_text(key: str, language: Optional[str], fallback: str) -> str:
    if normalize_language(language) != "sw":
        return fallback

    texts = {
        "health": "IMARA AI iko mtandaoni na imeunganishwa na hifadhidata. Uliza swali ukiwa tayari.",
        "language_capability": (
            "Ndiyo. Ninaweza kujibu kwa Kiingereza au Kiswahili. "
            "Unaweza kuniuliza kwa Kiswahili, nami nitakujibu kwa Kiswahili."
        ),
        "portal_no_recent_submission": "Sijapata mawasilisho ya hivi karibuni kwenye akaunti yako ya portal.",
        "dynamic_sql_blocked": (
            "Niliunda query ya hilo swali, lakini haikupita ukaguzi wa usalama. "
            "Tafadhali liandike upya au uliza ripoti maalum."
        ),
        "dynamic_sql_restricted": "Swali hilo linahusu data iliyozuiwa ambayo haipatikani katika hali hii.",
        "dynamic_sql_invalid": (
            "Nilijaribu kujibu kwa uchambuzi, lakini sikuweza kutengeneza query sahihi. "
            "Jaribu kuliandika upya au uliza moja ya ripoti zilizopo."
        ),
        "dynamic_sql_failed": "Query imechukua muda mrefu au imeshindwa. Jaribu swali maalum zaidi.",
        "dynamic_sql_empty": (
            "Query imekamilika lakini haijapata rekodi zinazolingana. "
            "Huenda data haipo kwa vichujio vilivyotumika."
        ),
        "chat_error": (
            "Ninapata shida kutoa jibu kwa sasa. "
            "Tafadhali jaribu tena baada ya muda mfupi, au uliza swali maalum zaidi."
        ),
        "rag_no_summary": (
            "Nimepata nyaraka zinazohusiana, lakini sikuweza kutoa muhtasari. "
            "Tafadhali angalia vyanzo."
        ),
    }
    return texts.get(key, fallback)

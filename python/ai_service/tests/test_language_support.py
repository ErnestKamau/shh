from python.ai_service.core.language import detect_language
from python.ai_service.core import mode_registry


def test_detects_explicit_swahili_request():
    assert detect_language("tafadhali jibu kwa Kiswahili") == "sw"


def test_detects_swahili_message_markers():
    assert detect_language("habari, nisaidie na sampuli") == "sw"


def test_default_language_is_english():
    assert detect_language("what can you do?") == "en"


def test_swahili_capabilities_are_localized():
    answer = mode_registry.get_capabilities("lab", "sw")

    assert "Katika **Lab Mode**" in answer
    assert "ninaweza kusaidia" in answer


def test_swahili_greeting_is_localized():
    answer = mode_registry.get_greeting_for_language("general", "habari", "sw")

    assert "Habari." in answer
    assert "Nikusaidieje leo?" in answer

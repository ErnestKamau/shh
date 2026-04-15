<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

/**
 * Detects prompt injection and jailbreak attempts in user-supplied text.
 *
 * Phase 3 — Pattern-matching approach; covers ~95 % of common attacks
 * without requiring an ML inference call.
 */
class PromptInjectionDetector
{
    /**
     * Regex patterns keyed by a human-readable attack category.
     * All patterns are case-insensitive unless they carry explicit flags.
     */
    private const PATTERNS = [
        // ── Instruction override ─────────────────────────────────────────
        'ignore_instructions' => '/\bignore\s+(previous|prior|above|all|any)\s+(instructions?|prompts?|rules?|context)\b/i',
        'override_system'     => '/\b(system\s*override|override\s*instructions?|disregard\s*(all|previous|the)\s*(instructions?|rules?))\b/i',
        'forget_context'      => '/\b(forget|discard|dismiss)\s+(everything|all|your|previous)\b/i',
        'from_now_on'         => '/\bfrom\s+now\s+on\s*[,:]?\s*(you\s+(will|must|are\s+to)|ignore)\b/i',

        // ── Role / persona manipulation ──────────────────────────────────
        'act_as'              => '/\b(act\s+as|pretend\s+(you\s+are|to\s+be)|roleplay\s+as|you\s+are\s+now|behave\s+as)\b.{0,80}(ai|assistant|model|gpt|llm|bot|system)\b/i',
        'dan_jailbreak'       => '/\bDAN\b|\bdo\s+anything\s+now\b/i',
        'jailbreak_keyword'   => '/\bjailbreak\b/i',

        // ── Prompt delimiter injection ───────────────────────────────────
        'system_tag'          => '/\[\s*SYSTEM\s*\]|\<\s*SYS\s*\>|\[INST\]|###\s*System\s*:/i',
        'fenced_system'       => '/```\s*(system|prompt|instruction|context)\s*\n/i',
        'triple_quote_delim'  => '/"""[\s\S]{0,300}(system|instruction|prompt)[\s\S]{0,300}"""/i',

        // ── Data exfiltration ────────────────────────────────────────────
        'exfiltrate_prompt'   => '/\b(print|output|reveal|show|write|repeat|display)\s+(the|your|this)\s*(system\s*)?(prompt|instructions?|context|configuration|rules?)\b/i',

        // ── Unicode tricks ───────────────────────────────────────────────
        'rtl_override'        => '/\x{202E}/u',     // U+202E RIGHT-TO-LEFT OVERRIDE
    ];

    /**
     * Returns the name of the first matching pattern, or null if input is clean.
     */
    public function detect(string $input): ?string
    {
        foreach (self::PATTERNS as $name => $pattern) {
            if (preg_match($pattern, $input)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Run detection, log any match to the dedicated audit channel, and return
     * true when an injection was detected (caller should abort the request).
     *
     * @param  array<string, mixed>  $context  Extra fields written to the log entry.
     */
    public function check(string $input, array $context = []): bool
    {
        $matched = $this->detect($input);

        if ($matched !== null) {
            Log::channel('prompt-injection')->warning('Prompt injection attempt detected', array_merge([
                'pattern'       => $matched,
                'input_length'  => mb_strlen($input),
                'input_excerpt' => mb_substr($input, 0, 200),
            ], $context));

            return true;
        }

        return false;
    }
}

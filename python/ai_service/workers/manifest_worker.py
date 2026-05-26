"""
workers/manifest_worker.py — Manifest Worker

Wraps the existing ManifestIntentRouter + LiveDataService pipeline.
Handles deterministic Tier-1 and LLM-assisted Tier-2 manifest queries.
This is a pure extraction from the SimpleAssistant logic.
"""

import logging
import re
import time
import json
import concurrent.futures
import pandas as pd
from typing import List, Dict, Any, Optional, Tuple

from python.ai_service.services.live_data_service import LiveDataService
from python.ai_service.services.visualization_service import VisualizationService
from python.ai_service.services.ollama_service import OllamaService
from python.ai_service.core.manifest_intent_router import ManifestIntentRouter
from python.ai_service.core.query_classifier import QueryClassifier
from python.ai_service.core.query_filter_extractor import QueryFilterExtractor, _resolve_date_range
from python.ai_service.core import mode_registry
from python.ai_service.core.language import language_instruction, normalize_language

logger = logging.getLogger(__name__)

_SQL_TIMEOUT = 5.0
_LLM_ROUTE_TIMEOUT = 3.0
_LOCALIZE_TIMEOUT = 6.0


class ManifestWorker:
    """
    Worker responsible for executing pre-approved manifest SQL queries.
    Tier 1: keyword rules. Tier 2: LLM classification over manifest intents.
    Returns None if no manifest intent matches the query.
    """

    def __init__(
        self,
        live_data: LiveDataService,
        visualizer: VisualizationService,
        ollama: OllamaService,
    ):
        self.live_data = live_data
        self.visualizer = visualizer
        self.ollama = ollama
        self.intent_router = ManifestIntentRouter()
        self.query_classifier = QueryClassifier()
        self.filter_extractor = QueryFilterExtractor()

    def run(
        self,
        message: str,
        company_id: int = 1,
        use_visuals: bool = True,
        mode: Optional[str] = None,
        module_context: Optional[str] = None,
        trace_id: str = "",
        language: Optional[str] = None,
    ) -> Optional[Dict[str, Any]]:
        """
        Attempt to answer the query using the manifest.
        Returns a result dict if any intent matched, or None if no match.
        """
        domain_whitelist = mode_registry.get_allowed_domains(mode)

        # ── Tier 1: Keyword rules ─────────────────────────────────────────────
        keyword_matches = self.intent_router.match_all(message, domain_whitelist=domain_whitelist)
        all_intents: List[Tuple[str, str, Dict[str, Any], float]] = []

        if keyword_matches:
            all_intents = self._assign_granular_filters(message, keyword_matches)

        # ── Tier 2: LLM classification if needed ──────────────────────────────
        q_count = message.count("?")
        conjunction_count = len(re.split(r"\band\b|\balso\b|\bas well as\b|, ", message, flags=re.IGNORECASE))
        should_consult_llm = (
            not all_intents
            or q_count > len(all_intents)
            or conjunction_count > len(all_intents)
            or (" and " in message.lower() or " as well as " in message.lower())
        )

        if should_consult_llm:
            llm_map = self._llm_classify(message, domain_whitelist, module_context)
            if llm_map and "none" not in llm_map:
                existing = {m[0] for m in all_intents}
                base_filters = self.filter_extractor.extract(message)
                for intent, data in llm_map.items():
                    if intent in existing:
                        continue
                    overrides = data.get("filters", {})
                    conf = data.get("confidence", 0.85)
                    f = base_filters.copy()
                    if overrides:
                        if "date_label" in overrides:
                            try:
                                start, end = _resolve_date_range(overrides["date_label"])
                                overrides["date_start"] = start
                                overrides["date_end"] = end
                            except Exception:
                                pass
                        f.update(overrides)
                    all_intents.append((intent, "llm", f, conf))

        if not all_intents:
            logger.info(f"ManifestWorker [{trace_id[:8]}]: No intent matched")
            return None

        # ── Execute matched intents ────────────────────────────────────────────
        logger.info(f"ManifestWorker [{trace_id[:8]}]: Executing intents={[m[0] for m in all_intents]}")
        return self._execute_multi(message, all_intents, company_id, use_visuals, trace_id, language)

    def _execute_multi(
        self,
        message: str,
        matches: List[Tuple[str, str, Dict[str, Any], float]],
        company_id: int,
        use_visuals: bool,
        trace_id: str,
        language: Optional[str] = None,
    ) -> Dict[str, Any]:
        summaries, all_data = [], {}
        combined_latency, success_count = 0, 0
        total_conf = sum(m[3] for m in matches)
        avg_conf = total_conf / len(matches) if matches else 0.0

        with concurrent.futures.ThreadPoolExecutor(max_workers=min(len(matches), 4)) as executor:
            futures = {}
            for intent, tier, filters, conf in matches:
                params = {"company_id": company_id}
                params.update(filters or {})
                futures[executor.submit(self.live_data.execute_step, intent, params=params, trace_id=trace_id)] = intent

            for future in concurrent.futures.as_completed(futures):
                intent = futures[future]
                try:
                    res = future.result(timeout=_SQL_TIMEOUT)
                    if res.get("success", False):
                        success_count += 1
                        summary = res.get("summary", "")
                        if use_visuals:
                            template = self.live_data._resolve_template(intent)
                            if template and "visualize" in template:
                                df = pd.DataFrame(res.get("data", []))
                                chart = self.visualizer.generate_chart_block(df, template["visualize"])
                                if chart:
                                    summary = chart + "\n\n" + summary
                        summaries.append(summary)
                        all_data[intent] = res.get("data", [])
                        combined_latency += res.get("execution_latency_ms", 0)
                    else:
                        summaries.append(f"Could not retrieve data for `{intent}`.")
                except Exception as exc:
                    logger.error(f"ManifestWorker: intent {intent} failed: {exc}")
                    summaries.append(f"Failed to retrieve data for `{intent}`.")

        answer = "\n\n---\n\n".join(summaries) if summaries else None
        if answer:
            answer = self._localize_answer(answer, language)
        return {
            "answer": answer,
            "sources": [],
            "meta": {
                "route": f"manifest:{','.join([m[0] for m in matches])}",
                "routing_tier": "manifest",
                "confidence": avg_conf,
                "sql_execution_ms": combined_latency,
                "multi_count": len(matches),
                "success_count": success_count,
            },
        }

    def _llm_classify(
        self,
        message: str,
        domain_whitelist: Optional[List[str]],
        module_context: Optional[str],
    ) -> Dict[str, Any]:
        intents_info, all_names = [], []
        unrestricted = not domain_whitelist or "*" in domain_whitelist
        for domain_name, domain_templates in self.live_data.templates.items():
            if not unrestricted and domain_name not in domain_whitelist:
                continue
            for name, details in domain_templates.items():
                intents_info.append(f"- {name}: {details.get('description', '')} (Domain: {domain_name})")
                all_names.append(name)

        restriction = ""
        if not unrestricted:
            restriction = f"CRITICAL: Only consider reports from these allowed domains: {', '.join(domain_whitelist)}."

        prompt = f"""You are an intent classifier for a LIMS. {restriction}
Map the user query to the appropriate report(s). If asking multiple questions, identify ALL.

Available Reports:
{chr(10).join(intents_info)}

User Query: "{message}"

Rules:
1. Return JSON: {{ "intent_name": {{ "filters": {{}}, "confidence": 0.0 }} }}
2. If no clear match, return {{ "none": {{}} }}
3. Semantically interpret the User Query. Ignore spelling mistakes, poor grammar, and typos.
4. No explanation, no preamble.

Classification:"""

        try:
            with concurrent.futures.ThreadPoolExecutor(max_workers=1) as ex:
                raw = ex.submit(self.ollama.generate, prompt=prompt).result(timeout=_LLM_ROUTE_TIMEOUT)
            clean = raw.strip().lstrip("```json").lstrip("```").rstrip("```").strip()
            mapping = json.loads(clean)
            return {k: v for k, v in mapping.items() if k in all_names or k == "none"}
        except Exception as exc:
            logger.warning(f"ManifestWorker LLM classify failed: {exc}")
            return {}

    def _localize_answer(self, answer: str, language: Optional[str]) -> str:
        if normalize_language(language) != "sw":
            return answer

        prompt = f"""{language_instruction(language)}

Translate the following LIMS report answer into natural Kiswahili.
Preserve all numbers, IDs, markdown tables, chart blocks, source labels, and technical terms where clearer.
Do not add new facts.

Answer:
{answer}

Kiswahili answer:"""

        try:
            with concurrent.futures.ThreadPoolExecutor(max_workers=1) as ex:
                localized = ex.submit(self.ollama.generate, prompt=prompt).result(timeout=_LOCALIZE_TIMEOUT)
            return localized.strip() or answer
        except Exception as exc:
            logger.warning(f"ManifestWorker localization failed: {exc}")
            return answer

    def _assign_granular_filters(self, message: str, matches) -> List[Tuple]:
        segments = re.split(r"\band\b|\balso\b|\bas well as\b|,", message, flags=re.IGNORECASE)
        base = self.filter_extractor.extract(message)
        final = []
        for intent, tier, conf in matches:
            best_seg = message
            for seg in segments:
                if any(p in seg.lower() for p, _ in []):
                    best_seg = seg
                    break
            f = self.filter_extractor.extract(best_seg)
            f.update({k: v for k, v in base.items() if k not in f})
            final.append((intent, tier, f, conf))
        return final

"""
SimpleAssistant — Stage 2: Production-Hardened Operational AI

Routing flow:
    1. Greeting detection → conversational (skip SQL + RAG entirely)
    2. Exact identifier extraction (QueryClassifier) → domain hints
    3. Keyword/rule-based manifest routing (ManifestIntentRouter)
    4. LLM classifier fallback (only when rules miss)
    5. RAG knowledge base search
    6. Conversational fallback

Every request is logged to ai.ai_request_logs via RequestLogger.
"""
import logging
import re
import time
import json
import uuid
import pandas as pd
from typing import List, Dict, Any, Optional, Tuple

from ai_service.services.ollama_service import OllamaService
from ai_service.services.visualization_service import VisualizationService
from ai_service.services.live_data_service import LiveDataService
from ai_service.services.retrieval_service import RetrievalService
from ai_service.core.manifest_intent_router import ManifestIntentRouter
from ai_service.core.query_classifier import QueryClassifier
from ai_service.core.query_filter_extractor import QueryFilterExtractor
from ai_service.core.request_logger import request_logger

logger = logging.getLogger(__name__)

# ── Error messages (user-facing, controlled) ─────────────────────────────
_ERR_NO_SQL_MATCH = (
    "I couldn't find a matching operational query for that request. "
    "Try rephrasing your question or ask about a specific metric like "
    "sample counts, inventory levels, or equipment status."
)
_ERR_NO_RAG_SOURCES = (
    "I couldn't find relevant knowledge-base content for that question. "
    "The knowledge base may not cover that topic yet."
)
_ERR_DB_UNAVAILABLE = (
    "The operational database is temporarily unavailable. "
    "Please try again shortly."
)
_ERR_AI_UNAVAILABLE = (
    "The AI service is temporarily busy. "
    "Please try again in a moment."
)
_ERR_ZERO_ROWS = (
    "No matching records were found for the filters used. "
    "This may indicate the data hasn't been synced yet, "
    "or no records match the current criteria."
)

# Timeouts for operations (seconds)
_LLM_ROUTE_TIMEOUT = 3.0
_SQL_TIMEOUT = 5.0
_RAG_TIMEOUT = 7.0

_ERR_TIMEOUT = (
    "That request is taking longer than expected. "
    "Try narrowing your query or asking for a specific subset of data."
)


class SimpleAssistant:
    def __init__(
        self,
        ollama: OllamaService,
        live_data: LiveDataService,
        retrieval: RetrievalService,
        visualizer: VisualizationService,
    ):
        self.ollama = ollama
        self.live_data = live_data
        self.retrieval = retrieval
        self.visualizer = visualizer
        self.intent_router = ManifestIntentRouter()
        self.query_classifier = QueryClassifier()
        self.filter_extractor = QueryFilterExtractor()

    def process_query(
        self,
        message: str,
        company_id: int = 1,
        use_visuals: bool = True,
        model: Optional[str] = None,
        *,
        trace_id: Optional[str] = None,
        user_id: Optional[int] = None,
        session_id: Optional[str] = None,
    ) -> Dict[str, Any]:
        """
        Main entry point for processing an operational query.
        Tiered adaptive flow: Greeting → Rules → LLM → RAG → Chat.
        """
        start_time = time.time()
        trace_id = trace_id or str(uuid.uuid4())
        routing_tier = "fallback"
        route_name = "conversational"
        error_message = None
        success = True
        source_count = 0
        cache_hit = False

        logger.info(
            f"SimpleAssistant [{trace_id[:8]}]: "
            f"query='{message[:60]}...' flow_started"
        )

        result = {
            "answer": "",
            "sources": [],
            "meta": {
                "route": "conversational",
                "latency_ms": 0,
                "trace_id": trace_id,
                "routing_tier": "fallback",
            },
        }

        try:
            # ── Step 0: Greeting short-circuit ────────────────────────
            if self.intent_router.is_greeting(message):
                logger.info(f"SimpleAssistant [{trace_id[:8]}]: Greeting detected")
                routing_tier = "greeting"
                route_name = "conversational"
                result["answer"] = self._generate_conversational(message)
                result["meta"]["route"] = "conversational"
                result["meta"]["routing_tier"] = routing_tier
                result["meta"]["latency_ms"] = self._elapsed_ms(start_time)
                return result

            # ── Step 1: Feature Extraction (Classifier + Filters) ─────
            classification = self.query_classifier.classify(message)
            domain_hints = classification.get("domains", ["all"])
            extracted_filters = self.filter_extractor.extract(message)

            # Inject extracted sample ID for specific batch lookups
            for entity in classification.get("identifiers", []):
                if entity["type"] == "sample":
                    extracted_filters["batch_code"] = entity["id"].upper()
                    break

            # ── Step 2: Rule-based manifest routing (Tier 1) ───────────
            # We gather these, but don't exit early yet to allow LLM coverage for missing parts.
            keyword_matches = self.intent_router.match_all(message)
            all_intents: List[Tuple[str, str, Dict[str, Any]]] = []
            
            if keyword_matches:
                # Assign granular filters for keywords by segmenting the query
                all_intents = self._assign_granular_filters(message, keyword_matches)

            # ── Step 3: LLM classifier fallback (Tier 2) ──────────────
            # We consult the LLM if:
            # - No keyword matches were found
            # - OR if multiple segments exist (multi-query), to ensure full coverage
            # - OR if we want maximum robustness
            
            # Smart coverage detection: count "questions" or segments
            q_count = message.count("?")
            quote_count = message.count('"')
            conjunction_count = len(re.split(r"\band\b|\balso\b|\bas well as\b|\, ", message, flags=re.IGNORECASE))
            
            # Simple heuristic: if there are more "questions" than keyword matches, 
            # or if the query is structurally complex, consult the LLM.
            should_consult_llm = (
                not all_intents or 
                q_count > len(all_intents) or
                (quote_count >= 4 and len(all_intents) < (quote_count // 2)) or
                conjunction_count > len(all_intents) or
                (" and " in message.lower() or " as well as " in message.lower())
            )
            
            if should_consult_llm:
                llm_intent_map = self._llm_classify_intents(message)
                if llm_intent_map and "none" not in llm_intent_map:
                    # Merge LLM results into all_intents
                    existing_intents = {m[0] for m in all_intents}
                    for intent, overrides in llm_intent_map.items():
                        if intent in existing_intents:
                            continue  # Keep the keyword match's granular filters
                        
                        # Merge overrides into the base filters
                        f = extracted_filters.copy()
                        if overrides:
                            # If the LLM found a specific date label, resolve it
                            if "date_label" in overrides:
                                from ai_service.core.query_filter_extractor import _resolve_date_range
                                try:
                                    start, end = _resolve_date_range(overrides["date_label"])
                                    overrides["date_start"] = start
                                    overrides["date_end"] = end
                                except Exception:
                                    pass
                            f.update(overrides)
                        all_intents.append((intent, "llm", f))
            
            # ── Step 4: Execute SQL if any intents matched ────────────
            if all_intents:
                return self._execute_multi_sql_route(
                    message, all_intents, company_id, use_visuals,
                    start_time, trace_id, result
                )

            # ── Step 4: RAG knowledge base search ─────────────────────
            try:
                import concurrent.futures
                with concurrent.futures.ThreadPoolExecutor(max_workers=1) as executor:
                    future = executor.submit(
                        self.retrieval.search,
                        query=message,
                        limit=5,
                        metadata_filters=(
                            {"company_id": company_id} if company_id else None
                        ),
                    )
                    chunks = future.result(timeout=_RAG_TIMEOUT)
            except concurrent.futures.TimeoutError:
                logger.warning(f"SimpleAssistant [{trace_id[:8]}]: RAG search timed out")
                chunks = []
                result["answer"] = _ERR_TIMEOUT
                # Fall through to conversational or error depending on severity
            except Exception as rag_exc:
                logger.error(f"SimpleAssistant [{trace_id[:8]}]: RAG search failed: {rag_exc}")
                chunks = []

            if chunks:
                logger.info(
                    f"SimpleAssistant [{trace_id[:8]}]: "
                    f"RAG matched {len(chunks)} chunks"
                )
                routing_tier = "rag"
                route_name = "rag"
                source_count = len(chunks)
                result["sources"] = self._format_sources(chunks)
                result["answer"] = self._synthesize_rag_answer(message, chunks)

                if not result["answer"]:
                    result["answer"] = _ERR_NO_RAG_SOURCES
                    success = False
                    error_message = "RAG synthesis returned empty"

                result["meta"]["route"] = "rag"
                result["meta"]["routing_tier"] = routing_tier
                result["meta"]["source_count"] = source_count
                result["meta"]["latency_ms"] = self._elapsed_ms(start_time)
                return result
            else:
                logger.info(
                    f"SimpleAssistant [{trace_id[:8]}]: "
                    "No RAG sources, falling back to conversational"
                )

            # ── Step 5: Conversational fallback ───────────────────────
            routing_tier = "fallback"
            route_name = "conversational"
            result["answer"] = self._generate_conversational(message)
            result["meta"]["route"] = "conversational"
            result["meta"]["routing_tier"] = routing_tier
            result["meta"]["latency_ms"] = self._elapsed_ms(start_time)

        except Exception as e:
            logger.error(f"SimpleAssistant [{trace_id[:8]}] unhandled error: {e}")
            success = False
            error_message = str(e)
            routing_tier = "error"
            route_name = "error"
            result["answer"] = _ERR_AI_UNAVAILABLE
            result["meta"]["route"] = "error"
            result["meta"]["error_type"] = type(e).__name__
            result["meta"]["latency_ms"] = self._elapsed_ms(start_time)

        finally:
            # ── Always log the request ────────────────────────────────
            latency = self._elapsed_ms(start_time)
            result["meta"]["latency_ms"] = latency

            request_logger.log(
                trace_id=trace_id,
                query=message,
                mode=result["meta"].get("route", "unknown"),
                route_name=route_name,
                routing_tier=routing_tier,
                latency_ms=latency,
                success=success,
                error_message=error_message,
                company_id=company_id,
                user_id=user_id,
                session_id=session_id,
                response_preview=result.get("answer", "")[:500],
                source_count=source_count,
                cache_hit=cache_hit,
            )

        return result

    # ── SQL execution helper ──────────────────────────────────────────────

    def _execute_multi_sql_route(
        self,
        message: str,
        matches: List[Tuple[str, str, Dict[str, Any]]],
        company_id: int,
        use_visuals: bool,
        start_time: float,
        trace_id: str,
        result: Dict[str, Any],
        global_filters: Optional[Dict[str, Any]] = None
    ) -> Dict[str, Any]:
        """Execute multiple SQL intents in parallel and aggregate results."""
        logger.info(
            f"SimpleAssistant [{trace_id[:8]}]: "
            f"Executing {len(matches)} SQL intents: {[m[0] for m in matches]}"
        )

        summaries = []
        all_data = {}
        combined_latency = 0
        success_count = 0
        routing_tiers = list(set(m[1] for m in matches))
        
        import concurrent.futures
        with concurrent.futures.ThreadPoolExecutor(max_workers=len(matches)) as executor:
            # Prepare futures with specific params for each intent
            future_to_intent = {}
            for intent, tier, specific_filters in matches:
                params = {"company_id": company_id}
                if global_filters:
                    params.update(global_filters)
                if specific_filters:
                    params.update(specific_filters)
                
                future = executor.submit(self.live_data.execute_step, intent, params=params)
                future_to_intent[future] = intent
            
            for future in concurrent.futures.as_completed(future_to_intent):
                intent = future_to_intent[future]
                try:
                    sql_res = future.result(timeout=_SQL_TIMEOUT)
                    if sql_res.get("success", False):
                        success_count += 1
                        summary = sql_res.get("summary", "")
                        
                        # Add visualization if enabled
                        if use_visuals:
                            template = self.live_data._resolve_template(intent)
                            if template and "visualize" in template:
                                df = pd.DataFrame(sql_res.get("data", []))
                                chart_block = self.visualizer.generate_chart_block(
                                    df, template["visualize"]
                                )
                                if chart_block:
                                    summary = chart_block + "\n\n" + summary
                        
                        summaries.append(summary)
                        all_data[intent] = sql_res.get("data", [])
                        combined_latency += sql_res.get("execution_latency_ms", 0)
                    else:
                        logger.warning(f"Intent {intent} failed: {sql_res.get('error')}")
                        summaries.append(f"*(Reporting error for '{intent}': {sql_res.get('summary', 'Unavailable')})*")
                except Exception as e:
                    logger.error(f"Execution failed for {intent}: {e}")
                    summaries.append(f"*(Failed to retrieve data for '{intent}')*")

        if success_count == 0:
            result["answer"] = _ERR_DB_UNAVAILABLE
            result["meta"]["route"] = "multi_sql_failed"
        else:
            result["answer"] = "\n\n---\n\n".join(summaries)
            result["meta"]["route"] = f"multi_sql:{','.join([m[0] for m in matches])}"
            result["meta"]["routing_tier"] = ",".join(routing_tiers)
            result["meta"]["sql_execution_ms"] = combined_latency
            result["meta"]["multi_count"] = len(matches)
            result["meta"]["multi_report"] = True

        result["meta"]["latency_ms"] = self._elapsed_ms(start_time)
        return result

    def _assign_granular_filters(self, message: str, matches: List[Tuple[str, str]]) -> List[Tuple[str, str, Dict[str, Any]]]:
        """
        Split query by conjunctions and assign filters to intents based on proximity.
        Fallback for keyword-based multi-queries.
        """
        import re
        segments = re.split(r'\band\b|\balso\b|\bas well as\b|\,', message, flags=re.IGNORECASE)
        final_matches = []
        
        for intent, tier in matches:
            # Find which segment contains keywords for this intent
            # This is a heuristic: we check which segment matches the intent router's rules
            intent_filters = {}
            for seg in segments:
                # If the intent router would match this intent in this segment,
                # use this segment's filters.
                seg_matches = self.intent_router.match_all(seg)
                if any(m[0] == intent for m in seg_matches):
                    intent_filters = self.filter_extractor.extract(seg)
                    break
            
            final_matches.append((intent, tier, intent_filters))
        
        return final_matches

    def _execute_sql_route(
        self,
        message: str,
        intent: str,
        company_id: int,
        use_visuals: bool,
        start_time: float,
        trace_id: str,
        routing_tier: str,
        result: Dict[str, Any],
        filters: Optional[Dict[str, Any]] = None
    ) -> Dict[str, Any]:
        """Execute a matched SQL intent and build the response."""
        logger.info(
            f"SimpleAssistant [{trace_id[:8]}]: "
            f"Executing SQL intent '{intent}' (tier={routing_tier})"
        )

        params = {"company_id": company_id}
        if filters:
            params.update(filters)

        try:
            import concurrent.futures
            with concurrent.futures.ThreadPoolExecutor(max_workers=1) as executor:
                future = executor.submit(
                    self.live_data.execute_step,
                    intent, params=params
                )
                sql_res = future.result(timeout=_SQL_TIMEOUT)
        except concurrent.futures.TimeoutError:
            logger.warning(f"SimpleAssistant [{trace_id[:8]}]: SQL execution timed out")
            result["answer"] = _ERR_TIMEOUT
            result["meta"]["route"] = f"sql:{intent}"
            result["meta"]["routing_tier"] = routing_tier
            result["meta"]["error_type"] = "timeout"
            result["meta"]["latency_ms"] = self._elapsed_ms(start_time)
            return result
        except Exception as db_exc:
            logger.error(
                f"SimpleAssistant [{trace_id[:8]}]: "
                f"DB execution failed for '{intent}': {db_exc}"
            )
            result["answer"] = _ERR_DB_UNAVAILABLE
            result["meta"]["route"] = f"sql:{intent}"
            result["meta"]["routing_tier"] = routing_tier
            result["meta"]["error_type"] = "db_connection"
            result["meta"]["latency_ms"] = self._elapsed_ms(start_time)
            return result

        if not sql_res.get("success", False):
            error = sql_res.get("error", "")
            # Circuit breaker or template not found
            if "Circuit" in str(error):
                result["answer"] = _ERR_DB_UNAVAILABLE
            elif "not found in manifest" in str(error):
                result["answer"] = _ERR_NO_SQL_MATCH
            else:
                result["answer"] = sql_res.get(
                    "summary", _ERR_DB_UNAVAILABLE
                )
            result["meta"]["route"] = f"sql:{intent}"
            result["meta"]["routing_tier"] = routing_tier
            result["meta"]["error_type"] = "sql_execution"
            result["meta"]["latency_ms"] = self._elapsed_ms(start_time)
            return result

        answer = sql_res.get("summary", _ERR_ZERO_ROWS)

        # Deterministic visualizations
        if use_visuals:
            template = self.live_data._resolve_template(intent)
            if template and "visualize" in template:
                df = pd.DataFrame(sql_res.get("data", []))
                chart_block = self.visualizer.generate_chart_block(
                    df, template["visualize"]
                )
                if chart_block:
                    answer = chart_block + "\n\n" + answer

        result["answer"] = answer
        result["meta"]["route"] = f"sql:{intent}"
        result["meta"]["routing_tier"] = routing_tier
        result["meta"]["latency_ms"] = self._elapsed_ms(start_time)
        result["meta"]["sql_value"] = sql_res.get("value")
        result["meta"]["sql_execution_ms"] = sql_res.get("execution_latency_ms")
        return result

    # ── LLM-based intent classification (Tier 2 fallback) ─────────────────

    def _llm_classify_intents(self, message: str) -> Dict[str, Dict[str, Any]]:
        """
        Use the LLM to classify a query into one or more manifest intents,
        including specific filters for each.
        """
        intents_info = []
        all_intent_names = []
        for domain_templates in self.live_data.templates.values():
            for name, details in domain_templates.items():
                desc = details.get("description", "Data report")
                intents_info.append(f"- {name}: {desc}")
                all_intent_names.append(name)

        prompt = f"""You are a specialized intent classifier for a Lab Information Management System (LIMS). 

Map the user query to the most appropriate operational report(s). 
If the user asks multiple questions, identify ALL relevant reports and their specific filters (dates, status, etc.).

Available Reports:
{chr(10).join(intents_info)}

User Query: "{message}"

Rules:
1. Return a JSON object where keys are report names and values are their specific filters (e.g. date_label, status, limit).
2. If no report is a clear match, return '{{"none": {{}}}}'.
3. Do NOT provide any explanation or preamble.

Example: {{"sample_count_today": {{"date_label": "today"}}, "analyst_verifications": {{"date_label": "last month"}}}}

Classification:"""

        try:
            import concurrent.futures

            with concurrent.futures.ThreadPoolExecutor(max_workers=1) as executor:
                future = executor.submit(
                    self.ollama.generate, prompt=prompt
                )
                raw_response = future.result(timeout=_LLM_ROUTE_TIMEOUT)
                
                # Parse JSON response
                try:
                    # Strip any potential markdown wrappers if the LLM ignores rules
                    clean_res = raw_response.strip()
                    if clean_res.startswith("```json"):
                        clean_res = clean_res[7:-3].strip()
                    elif clean_res.startswith("```"):
                        clean_res = clean_res[3:-3].strip()
                    
                    mapping = json.loads(clean_res)
                    # Filter for valid intent names
                    valid_mapping = {k: v for k, v in mapping.items() if k in all_intent_names or k == "none"}
                    
                    if valid_mapping and "none" not in valid_mapping:
                        logger.info(f"SimpleAssistant: LLM classified intents with filters: {valid_mapping}")
                        return valid_mapping
                except json.JSONDecodeError:
                    # Fallback to simple comma-separated check if LLM fails JSON
                    logger.warning("LLM failed to return JSON, falling back to simple parsing")
                    parts = [p.strip().lower() for p in raw_response.split(",")]
                    return {p: {} for p in parts if p in all_intent_names}

                return {}

        except concurrent.futures.TimeoutError:
            logger.warning("SimpleAssistant: LLM routing timed out, skipping")
            return {}
        except Exception as e:
            logger.warning(f"SimpleAssistant: LLM routing failed: {e}")
            return {}

    def _llm_classify_intent(self, message: str) -> Optional[str]:
        """
        Use the LLM to classify a query into a manifest intent.
        Has a timeout to avoid blocking on slow LLM responses.
        """
        intents_info = []
        all_intent_names = []
        for domain_templates in self.live_data.templates.values():
            for name, details in domain_templates.items():
                desc = details.get("description", "Data report")
                intents_info.append(f"- {name}: {desc}")
                all_intent_names.append(name)

        prompt = f"""You are a specialized intent classifier for a Lab Information Management System (LIMS). 
Map the user query to the most appropriate operational report.

Available Reports:
{chr(10).join(intents_info)}

User Query: "{message}"

Rules:
1. Return ONLY the report name (the part before the colon).
2. If no report is a clear match, return 'none'.
3. Do NOT provide any explanation or preamble.

Classification:"""

        try:
            import concurrent.futures

            with concurrent.futures.ThreadPoolExecutor(max_workers=1) as executor:
                future = executor.submit(
                    self.ollama.generate, prompt=prompt
                )
                mapped = future.result(timeout=_LLM_ROUTE_TIMEOUT)
                mapped = mapped.strip().lower().replace("'", "").replace('"', "")

                if mapped in all_intent_names:
                    logger.info(
                        f"SimpleAssistant: LLM classified intent '{mapped}'"
                    )
                    return mapped

                logger.info(
                    f"SimpleAssistant: LLM returned '{mapped}' — no manifest match"
                )
                return None

        except concurrent.futures.TimeoutError:
            logger.warning(
                "SimpleAssistant: LLM routing timed out after "
                f"{_LLM_ROUTE_TIMEOUT}s, skipping to RAG/chat"
            )
            return None
        except Exception as e:
            logger.warning(f"SimpleAssistant: LLM routing failed: {e}")
            return None

    # ── RAG synthesis ─────────────────────────────────────────────────────

    def _synthesize_rag_answer(
        self, message: str, chunks: List[Dict[str, Any]]
    ) -> str:
        """Build an answer from retrieved knowledge base chunks."""
        context = "\n\n".join(
            [
                f"Source: {c.get('collection_name')}\nContent: {c['content']}"
                for c in chunks
            ]
        )
        prompt = f"""You are a lab assistant. Use the following context to answer the user's question concisely.
Context:
{context}

Question: "{message}"

Rules:
1. Answer in 2-3 sentences max.
2. Be direct and professional.
3. If not in context, say: "I couldn't find a matching knowledge document for that request. Please contact the lab supervisor."
"""
        try:
            answer = self.ollama.generate(prompt=prompt)
            # Ensure consistent appending of sources if needed
            if answer and not answer.startswith("I couldn't find"):
                answer += "\n\n---\n**Sources:**\n"
                seen_sources = set()
                for c in chunks:
                    source_key = f"{c.get('collection_name')} ({c.get('entity_type')})"
                    if source_key not in seen_sources:
                        answer += f"- {source_key}\n"
                        seen_sources.add(source_key)
            return answer
        except Exception as e:
            logger.error(f"SimpleAssistant: RAG synthesis failed: {e}")
            return _ERR_AI_UNAVAILABLE

    # ── Conversational generation ─────────────────────────────────────────

    def _generate_conversational(self, message: str) -> str:
        """Generate a conversational response (greetings, general chat)."""
        try:
            return self.ollama.generate(
                prompt=message,
                system=(
                    "You are Imarachat AI, a professional LIMS assistant. "
                    "Be concise and helpful. You have access to lab data and SOPs. "
                    "If the user greets you, respond warmly and briefly."
                ),
            )
        except Exception as e:
            logger.error(f"SimpleAssistant: Conversational generation failed: {e}")
            return _ERR_AI_UNAVAILABLE

    # ── Source formatting ─────────────────────────────────────────────────

    def _format_sources(
        self, chunks: List[Dict[str, Any]]
    ) -> List[Dict[str, Any]]:
        sources = []
        for c in chunks:
            sources.append(
                {
                    "label": f"{c.get('collection_name')} · {c.get('entity_type')}",
                    "entity_id": c.get("entity_id"),
                    "collection_name": c.get("collection_name"),
                    "entity_type": c.get("entity_type"),
                }
            )
        return sources

    # ── Utilities ─────────────────────────────────────────────────────────

    @staticmethod
    def _elapsed_ms(start: float) -> int:
        return int((time.time() - start) * 1000)

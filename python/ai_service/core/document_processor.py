import re
from typing import List, Dict, Any, Optional

class DocumentProcessor:
    """
    Advanced Document Processor for Section-Aware Chunking.
    
    Implements a hierarchy-aware splitting strategy:
    1. Detects logical sections based on headings and patterns.
    2. Groups text into blocks by section.
    3. Recursively splits blocks that exceed the target token/char limit.
    4. Preservers section-level metadata for every chunk.
    """

    def __init__(self, 
                 chunk_size: int = 800, 
                 chunk_overlap: int = 100,
                 token_ratio: float = 4.0):
        self.chunk_size = chunk_size
        self.chunk_overlap = chunk_overlap
        # Approx characters per token (default is conservative 4 chars/token)
        self.char_limit = int(chunk_size * token_ratio)
        self.char_overlap = int(chunk_overlap * token_ratio)
        
        # Regex for common heading patterns (e.g., "1.0", "Section 1", "INTRODUCTION")
        self.heading_patterns = [
            r"^\d+\.\d*(?:\d+)*\s+[A-Z\s]+", # 1.1 SCOPE
            r"^[A-Z\s]{5,25}$",               # ALL CAPS HEADING
            r"^Section\s+\d+[:.]?",           # Section 1:
            r"^\d+\.\s+[A-Z]",                # 1. Introduction
            r"^[IVXLCDM]+\.\s+[A-Z]",         # I. Background
        ]

    def process_document(self, 
                         content: str, 
                         document_id: Any, 
                         base_metadata: Optional[Dict[str, Any]] = None) -> List[Dict[str, Any]]:
        """
        Process a full document into semantic chunks.
        """
        if not content:
            return []

        # 1. Detect Sections
        sections = self._split_into_sections(content)
        
        all_chunks = []
        for section in sections:
            title = section["title"]
            text = section["text"]
            
            # 2. Split section into chunks if too large
            section_chunks = self._split_text(text)
            
            for i, chunk_text in enumerate(section_chunks):
                chunk_metadata = (base_metadata or {}).copy()
                chunk_metadata.update({
                    "document_id": document_id,
                    "section_title": title,
                    "chunk_index": i,
                    "total_section_chunks": len(section_chunks)
                })
                
                all_chunks.append({
                    "content": chunk_text,
                    "metadata": chunk_metadata
                })
        
        return all_chunks

    def _split_into_sections(self, content: str) -> List[Dict[str, str]]:
        """
        Identify headings and split text into section blocks.
        """
        lines = content.splitlines()
        sections = []
        current_title = "Introduction"
        current_buffer = []

        for line in lines:
            stripped = line.strip()
            if not stripped:
                continue

            is_heading = any(re.match(pattern, stripped) for pattern in self.heading_patterns)
            
            if is_heading:
                # Save previous section
                if current_buffer:
                    sections.append({
                        "title": current_title,
                        "text": "\n".join(current_buffer)
                    })
                current_title = stripped
                current_buffer = []
            else:
                current_buffer.append(line)

        # Add last section
        if current_buffer:
            sections.append({
                "title": current_title,
                "text": "\n".join(current_buffer)
            })

        return sections

    def _split_text(self, text: str) -> List[str]:
        """
        Recursively split text into manageable chunks with overlap.
        """
        if len(text) <= self.char_limit:
            return [text.strip()]

        chunks = []
        start = 0
        while start < len(text):
            end = start + self.char_limit
            
            # If not at the end of text, try to find a good breaking point
            if end < len(text):
                # Look for last newline or paragraph break within the limit
                break_point = text.rfind("\n", start, end)
                if break_point == -1 or break_point < start + (self.char_limit * 0.7):
                    # Fallback to last sentence period if newline is absent or too far back
                    break_point = text.rfind(". ", start, end)
                
                if break_point != -1 and break_point > start:
                    end = break_point + 1 # Include the period or newline

            chunk = text[start:end].strip()
            if chunk:
                chunks.append(chunk)
            
            # Move start back by overlap
            start = end - self.char_overlap
            
            # Safety break to avoid infinite loop
            if start < 0: start = 0
            if start >= len(text) - self.char_overlap:
                remaining = text[end:].strip()
                if remaining and len(remaining) > (self.char_limit * 0.2):
                    # Only add if it's a significant chunk, or merge with previous?
                    # For now, just break if we're basically done
                    break
                break

        return chunks

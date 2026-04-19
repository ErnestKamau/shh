import io
import re
import pandas as pd
from pypdf import PdfReader
import logging
from typing import Optional, Dict, Any

logger = logging.getLogger(__name__)

class FileParser:
    """
    Utility class for extracting clean text from various file formats.
    Supported: PDF, DOCX, CSV.
    """
    
    def extract_text(self, file_content: bytes, filename: str) -> str:
        """
        Detect file type by extension and extract text.
        """
        ext = filename.split('.')[-1].lower()
        
        try:
            if ext == 'pdf':
                return self._parse_pdf(file_content)
            elif ext in ['docx', 'doc']:
                return self._parse_docx(file_content)
            elif ext == 'csv':
                return self._parse_csv(file_content)
            elif ext in ['txt', 'md']:
                return file_content.decode('utf-8', errors='ignore')
            else:
                raise ValueError(f"Unsupported file extension: {ext}")
        except Exception as e:
            logger.error(f"Failed to parse {filename}: {e}")
            raise

    def _parse_pdf(self, content: bytes) -> str:
        """
        Extract text from PDF pages using pypdf.
        """
        reader = PdfReader(io.BytesIO(content))
        text = ""
        for page in reader.pages:
            extracted = page.extract_text()
            if extracted:
                text += extracted + "\n\n"
        return text.strip()

    def _parse_docx(self, content: bytes) -> str:
        """
        Extract text from Word paragraphs using python-docx.
        """
        try:
            import docx
            doc = docx.Document(io.BytesIO(content))
            return "\n".join([para.text for para in doc.paragraphs if para.text.strip()])
        except ImportError:
            logger.error("python-docx is not installed but DOCX parsing was requested.")
            return "[Error: Word document parser not available on server]"

    def _parse_csv(self, content: bytes) -> str:
        """
        Extract tabular data and convert to Markdown for semantic awareness.
        """
        try:
            # We use semicolon or comma detection
            df = pd.read_csv(io.BytesIO(content), sep=None, engine='python')
            
            # Limit to first 100 rows to avoid token explosion
            if len(df) > 100:
                df = df.head(100)
                footer = "\n\n(Truncated: Showing first 100 rows)"
            else:
                footer = ""
                
            return df.to_markdown(index=False) + footer
        except Exception as e:
            logger.error(f"CSV parsing failed: {e}")
            return f"[Error parsing CSV: {str(e)}]"

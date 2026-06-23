import subprocess
import sys

# Attempt 1: pdftotext
try:
    res = subprocess.run(["pdftotext", "/home/kaarr/polucon-amspec-dubai/Report_Pages_1_2 (1).pdf", "-"], capture_output=True, text=True)
    if res.returncode == 0 and res.stdout.strip():
        print("--- EXTRACTED VIA pdftotext ---")
        print(res.stdout)
        sys.exit(0)
except Exception as e:
    print("pdftotext failed:", e)

# Attempt 2: PyPDF2 / pypdf
try:
    import pypdf
    reader = pypdf.PdfReader("/home/kaarr/polucon-amspec-dubai/Report_Pages_1_2 (1).pdf")
    for i, page in enumerate(reader.pages):
        print(f"--- PAGE {i+1} ---")
        print(page.extract_text())
    sys.exit(0)
except ImportError:
    pass

try:
    import PyPDF2 as pypdf
    reader = pypdf.PdfReader("/home/kaarr/polucon-amspec-dubai/Report_Pages_1_2 (1).pdf")
    for i, page in enumerate(reader.pages):
        print(f"--- PAGE {i+1} ---")
        print(page.extract_text())
    sys.exit(0)
except ImportError:
    pass

print("No PDF extraction tools available.")

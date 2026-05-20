import openpyxl
import os

filepath = '/home/kaarr/polucon/pricelist.xlsx'
if not os.path.exists(filepath):
    print(f"File not found: {filepath}")
    sys.exit(1)

wb = openpyxl.load_workbook(filepath, read_only=True)
print("Sheets:", wb.sheetnames)
for sheet in wb.sheetnames:
    ws = wb[sheet]
    print(f"\nSheet '{sheet}' - first 10 rows:")
    row_count = 0
    for row in ws.iter_rows(values_only=True):
        if row_count >= 15:
            break
        # Print non-empty row elements or truncate to avoid long outputs
        print(row)
        row_count += 1

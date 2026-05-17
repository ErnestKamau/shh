import openpyxl
import sys

wb = openpyxl.load_workbook('/home/kaarr/polucon/full_data.xlsx', read_only=True)
print("Sheets:", wb.sheetnames)
for sheet in wb.sheetnames:
    ws = wb[sheet]
    print(f"\nSheet '{sheet}' - first 3 rows:")
    row_count = 0
    for row in ws.iter_rows(values_only=True):
        if row_count >= 5:
            break
        print(row)
        row_count += 1

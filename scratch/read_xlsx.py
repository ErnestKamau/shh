import openpyxl
import pandas as pd

file_path = '/home/kaarr/polucon/ENVIRONMENT_INVENTORY.xlsx'
xl = pd.ExcelFile(file_path)
print("Sheet names:", xl.sheet_names)

for sheet in xl.sheet_names:
    print(f"\n--- Sheet: {sheet} ---")
    df = xl.parse(sheet)
    print("Columns:", df.columns.tolist())
    print("Shape:", df.shape)
    print("First 3 rows:\n", df.head(3))

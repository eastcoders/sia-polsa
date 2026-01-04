
import openpyxl
import json
import sys

file_path = 'public/Format Nilai PRODI TI.xlsx'

try:
    wb = openpyxl.load_workbook(file_path, data_only=True)
    sheet = wb.active
    
    data = []
    # Read first 15 rows to get headers and some student data
    for row in sheet.iter_rows(min_row=1, max_row=15, values_only=True):
        data.append(row)
        
    print(json.dumps(data, default=str))

except Exception as e:
    print(f"Error: {e}")

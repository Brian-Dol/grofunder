# Bulk Import Operations - Growfunder Loan Management System

## Overview

The Bulk Import feature allows administrators and agents to import multiple borrowers and loans into the system via CSV files, significantly reducing manual data entry time and improving operational efficiency.

## Features

✅ **Bulk Borrower Import** - Import multiple borrowers with a single CSV file
✅ **Bulk Loan Import** - Import multiple loans with a single CSV file
✅ **CSV Validation** - Automatic validation of CSV structure and data
✅ **Error Tracking** - Detailed error and warning reports after import
✅ **Success Reporting** - Summary statistics including success rate percentage
✅ **Import Logging** - All imports tracked in database for audit trail
✅ **Sample Templates** - Downloadable CSV templates for proper format
✅ **Data Integrity** - Duplicate detection and prevention
✅ **Date Flexibility** - Multiple date format support (YYYY-MM-DD, DD/MM/YYYY, MM/DD/YYYY)

## Accessing the Feature

### Admin Users
1. Navigate to **Filament Admin Panel**
2. Go to **Operations** menu group
3. Select either:
   - **Bulk Import Borrowers** - for farmer/borrower imports
   - **Bulk Import Loans** - for loan record imports

### Agent Users
Agents can import data for their assigned cooperative only:
1. Same navigation path (Operations menu)
2. Cooperative automatically pre-selected based on their profile
3. Can only import borrowers and loans for their cooperative

## Bulk Borrower Import

### CSV Format

**Required Columns:**
- `name` - Borrower full name (string, required)
- `mobile_number` - Phone number in E.164 format (string, required, unique per cooperative)

**Optional Columns:**
- `email` - Email address (string, optional)

### Sample CSV Format

```csv
name,mobile_number,email
John Doe,+256701234567,john@example.com
Jane Smith,+256702345678,jane@example.com
Peter Johnson,+256703456789,peter@example.com
```

### Phone Number Format

Mobile numbers can be provided in multiple formats:
- **E.164 (Recommended)**: `+256701234567`
- **Local format**: `0701234567` (will be converted to E.164)
- **Country code**: `256701234567`

### Import Process

1. **Download Template** (Optional)
   - Click "Download Template" button to get sample CSV
   - Use as reference for formatting

2. **Select Cooperative** (Admins only)
   - Choose which cooperative to import borrowers into
   - Agents: automatically set to their cooperative

3. **Upload CSV File**
   - Click file input to select CSV file
   - Maximum file size: 10MB
   - Supported formats: .csv, .txt

4. **Import**
   - Click "Import Borrowers" button
   - System validates CSV structure
   - Imports valid rows one by one
   - Tracks errors and warnings

5. **Review Report**
   - View import summary:
     - **Total Rows**: All data rows in CSV
     - **Successful**: Imported borrowers count
     - **Failed**: Rows with errors
     - **Success Rate**: Percentage imported successfully
   - **Errors section**: Details on failed imports
   - **Warnings section**: Issues that didn't block import (e.g., duplicates)

### Validation Rules

- **name**: Required, must be string
- **mobile_number**: 
  - Required
  - Must be unique within the cooperative
  - Must be valid phone format
  - Cannot duplicate existing borrower
- **email**: Optional, must be valid email format if provided

### Error Scenarios

| Error | Cause | Solution |
|-------|-------|----------|
| Missing required columns | CSV headers don't match | Use template or add `name` and `mobile_number` columns |
| Validation failed: name required | Empty name field | Fill in borrower name in CSV |
| Validation failed: mobile_number required | Empty mobile number | Add mobile number in valid format |
| Borrower with mobile number already exists | Duplicate in system | Remove duplicate row or use different mobile number |
| Row X: Validation failed | Data doesn't meet requirements | Check row data and correct format |
| File not found | Uploaded file missing | Re-upload CSV file |

## Bulk Loan Import

### CSV Format

**Required Columns:**
- `borrower_mobile_number` - Farmer's mobile number (must exist in system)
- `loan_number` - Unique loan identifier (string, unique per borrower)
- `principal_amount` - Loan amount in ZMW (numeric, minimum 0)
- `interest_rate` - Annual interest rate % (numeric, 0-100)
- `loan_term_months` - Loan duration in months (integer, minimum 1)
- `loan_status` - Current status (one of: pending, approved, completed, defaulted)

**Optional Columns:**
- `disbursement_date` - Date loan was disbursed (defaults to today if not provided)

### Sample CSV Format

```csv
borrower_mobile_number,loan_number,principal_amount,interest_rate,loan_term_months,loan_status,disbursement_date
+256701234567,LN-001,1000000,15,12,approved,2026-01-15
+256702345678,LN-002,1500000,15,18,approved,2026-01-20
+256703456789,LN-003,2000000,18,24,pending,2026-02-01
```

### Loan Status Values

- **pending** - Loan application under review
- **approved** - Loan approved and ready to disburse
- **completed** - Loan fully repaid
- **defaulted** - Borrower defaulted on payments

### Date Formats Accepted

The system automatically detects date format:
- `YYYY-MM-DD` (2026-01-15) - ISO format
- `DD/MM/YYYY` (15/01/2026) - European format
- `MM/DD/YYYY` (01/15/2026) - US format
- `DD-MM-YYYY` (15-01-2026)
- `MM-DD-YYYY` (01-15-2026)

### Import Process

1. **Select Cooperative** (Admins only)
   - Choose cooperative with borrowers
   - Agents: auto-selected

2. **Upload CSV File**
   - File must reference borrowers by mobile number
   - Borrowers must already exist in system
   - Maximum file size: 10MB

3. **Import**
   - System validates CSV structure
   - Verifies borrowers exist
   - Creates loans with calculated due dates
   - Due date = Disbursement date + Loan term months

4. **Review Report**
   - View summary statistics
   - Review any errors or warnings
   - Successfully imported loans appear in system immediately

### Validation Rules

- **borrower_mobile_number**: Must exist in selected cooperative
- **loan_number**: 
  - Must be unique per borrower
  - Required string format
- **principal_amount**: 
  - Required
  - Must be numeric
  - Minimum 0
- **interest_rate**: 
  - Required
  - Must be numeric
  - Must be between 0 and 100
- **loan_term_months**: 
  - Required
  - Must be integer
  - Minimum 1 month
- **loan_status**: 
  - Required
  - Must be one of: pending, approved, completed, defaulted
- **disbursement_date**: 
  - Optional (defaults to today)
  - Must be valid date

### Error Scenarios

| Error | Cause | Solution |
|-------|-------|----------|
| Borrower with mobile number not found | Phone number doesn't match any borrower | Verify mobile number spelling and format |
| Loan already exists for this borrower | Duplicate loan_number for same borrower | Use unique loan number |
| Invalid date format | Unrecognized date | Use one of accepted formats (YYYY-MM-DD, DD/MM/YYYY) |
| Validation failed: loan_status must be one of | Invalid loan status | Use: pending, approved, completed, or defaulted |

## Import Logging & History

All imports are tracked in the database:

- **User ID** - Who performed the import
- **Import Type** - 'borrower' or 'loan'
- **File Name** - Name of uploaded CSV
- **Total Rows** - Number of data rows processed
- **Successful Imports** - Successfully imported records
- **Failed Imports** - Records that failed
- **Success Rate** - Percentage (0-100%)
- **Errors** - JSON array of error messages
- **Warnings** - JSON array of warning messages
- **Status** - 'completed' or 'partial'
- **Created At** - Timestamp of import

**Audit Trail**
- All imports logged via Spatie ActivityLog
- Track who imported what and when
- Useful for compliance and debugging

## Best Practices

### 1. Data Preparation
- Clean data before import (remove duplicates)
- Validate phone numbers in correct format
- Verify all required fields are populated
- Test with small sample first

### 2. File Naming
- Use descriptive names: `borrowers_batch_1.csv`
- Include date: `loans_2026_01_15.csv`
- Avoid special characters

### 3. Large Imports
- For >1000 records, split into multiple files
- Import one batch at a time
- Monitor success rates between batches
- Check error patterns

### 4. Mobile Numbers
- Always use E.164 format for consistency
- Include country code (+256 for Uganda)
- Verify against existing borrowers first
- Use unique numbers only

### 5. Error Handling
- Review all errors after each import
- Address data quality issues
- Don't ignore warnings about duplicates
- Keep import logs for reference

## API & Service Integration

### BulkImportService

The import logic is encapsulated in `App\Services\BulkImportService`:

```php
use App\Services\BulkImportService;

$service = new BulkImportService();

// Import borrowers
$report = $service->importBorrowers($filePath, $cooperativeId);

// Import loans
$report = $service->importLoans($filePath, $cooperativeId);

// Validate CSV structure
$validation = $service->validateCsvStructure($filePath, 'borrower');

// Generate sample CSV
$csv = $service->generateSampleCsv('borrower');
```

### Import Report Structure

```php
[
    'type' => 'Borrowers',
    'total_rows' => 100,
    'successful' => 95,
    'failed' => 5,
    'success_rate' => 95.0,
    'errors' => [
        'Row 5: Mobile number required',
        'Row 12: Borrower already exists'
    ],
    'warnings' => [
        'Row 8: Borrower with mobile number already exists (skipped)'
    ]
]
```

## Troubleshooting

### Common Issues

**Issue: "CSV structure is invalid - Missing required columns"**
- Check column headers match exactly (case-sensitive)
- Column names should be: `name`, `mobile_number`, `email` for borrowers
- Or: `borrower_mobile_number`, `loan_number`, `principal_amount`, etc. for loans

**Issue: "File size exceeds maximum (10MB)"**
- Split CSV into multiple files
- Try again with smaller file

**Issue: "Borrower not found" when importing loans**
- Verify borrower exists in system
- Check mobile number formatting matches exactly
- Borrower must be created before importing their loans

**Issue: Some rows imported but others failed**
- This is normal - check Errors section for failed rows
- Fix data issues and re-import failed rows in new CSV

**Issue: Duplicate warnings after successful import**
- Duplicates were skipped during import
- Check if borrower already exists in system
- Remove duplicates from CSV before importing

## Files

- **Service**: `app/Services/BulkImportService.php`
- **Pages**: 
  - `app/Filament/Pages/BorrowerBulkImportPage.php`
  - `app/Filament/Pages/LoanBulkImportPage.php`
- **Views**:
  - `resources/views/filament/pages/borrower-bulk-import-page.blade.php`
  - `resources/views/filament/pages/loan-bulk-import-page.blade.php`
- **Model**: `app/Models/BulkImportLog.php`
- **Migration**: `database/migrations/2026_07_17_000002_create_bulk_import_logs_table.php`

## Performance

- Small imports (<100 records): < 1 second
- Medium imports (100-1000 records): 1-10 seconds
- Large imports (>1000 records): 10+ seconds
- Validation happens before import for early error detection
- Import process is transactional (all or nothing per row)

## Security

- File uploads restricted to CSV format only
- Maximum file size limited to 10MB
- Role-based access control (admin/agent only)
- Agents can only import for their cooperative
- All imports audit-logged with user ID and timestamp
- Phone numbers normalized and validated
- SQL injection prevented via Eloquent ORM

## Future Enhancements

- [ ] Async import for very large files
- [ ] Progress bar for long-running imports
- [ ] Email notification when import completes
- [ ] Bulk update/edit via CSV
- [ ] Export data to CSV
- [ ] Schedule recurring imports
- [ ] Multi-file batch import
- [ ] Import preview before confirming

## Support

For issues or questions about bulk imports:
1. Check error messages in import report
2. Review sample CSV templates
3. Verify data format matches requirements
4. Check application logs in `storage/logs/`
5. Contact system administrator

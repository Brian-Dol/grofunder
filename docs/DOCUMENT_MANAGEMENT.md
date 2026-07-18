# Document Management System

## Overview

The Document Management System provides centralized storage and organization of all loan-related documents including:
- Loan agreements
- KYC (Know Your Customer) documents
- Identification documents
- Proof of address
- Income statements
- Collateral documentation
- Guarantor documents
- Repayment schedules
- Loan settlement forms
- Bank statements
- Business licenses
- Other supporting documents

## Features

### 1. Document Categories
Pre-configured document types for better organization:
- **Loan Agreement** - Signed agreements between borrower and lender
- **KYC Document** - Customer verification documentation
- **ID Document** - National ID, passport, etc.
- **Proof of Address** - Residential address verification
- **Income Statement** - Financial information from borrower
- **Collateral Document** - Pledged asset documentation
- **Guarantor Document** - Co-signer identification and pledge
- **Repayment Schedule** - Loan payment terms
- **Loan Settlement Form** - Loan completion documentation
- **Bank Statement** - Financial institution records
- **Business License** - Business registration docs
- **Other Document** - Miscellaneous supporting documents

### 2. File Upload & Storage
- **File Size Limit**: 10MB per document
- **Supported Formats**: PDF, JPEG, PNG, DOC, DOCX, XLS, XLSX
- **Storage Location**: `storage/app/documents/` (private)
- **Metadata Tracking**: File size, MIME type, upload date, uploader

### 3. Document Associations
Link documents to:
- **Borrowers** - Primary document holder
- **Loans** - Specific loan documents
- **Uploaders** - Track who uploaded document
- **Branches** - Organizational location
- **Organization** - Broader organizational scope

### 4. Expiration Management
- **Expiry Date Tracking** - Set document expiration dates
- **Status Indicators**:
  - `active` - Document is current
  - `archived` - Document no longer in use
  - `expired` - Document past expiration date
- **Helper Methods**:
  - `isExpired()` - Check if document expired
  - `daysUntilExpiry()` - Days remaining before expiration
  - `isExpiringSoon()` - Alert if expiring within 30 days

### 5. Access Control
Documents follow cooperative-scoped access control:
- **Admins/Super Admins**: Full access to all documents
- **Agents**: Can only view/upload documents for their cooperative's borrowers
- **Authorization**: Automatic filtering based on user role and cooperative

### 6. Activity Logging
All document operations are logged via Spatie Activity Log:
- Document creation
- Status changes
- Expiration updates
- File uploads
- Metadata modifications

## Usage

### Upload a Document

1. Navigate to **Management → Documents**
2. Click **Create**
3. Fill in document details:
   - Select **Category** (required)
   - Select **Borrower** (optional but recommended)
   - Select **Loan** (optional)
   - Add **Description** (optional)
   - Set **Expiry Date** (optional)
   - Set **Status** (default: Active)
4. Upload file (max 10MB)
5. Click **Save**

### Download a Document

1. Go to **Management → Documents**
2. Find document in list
3. Click **Download** action button
4. File downloads to your device

### Filter Documents

Available filters:
- **Category** - By document type
- **Status** - Active, Archived, or Expired
- **Borrower** - By specific borrower

### Bulk Actions

Supported bulk operations:
- **Delete Multiple** - Remove multiple documents at once

### Search

Search documents by:
- File name
- Document category
- Borrower name
- Loan number

### Edit Document

1. Click **Edit** on the document row
2. Modify details as needed
3. Click **Save**

### Archive Documents

1. Edit document
2. Change status to **Archived**
3. Save

Archived documents remain accessible but are filtered from main view.

## API Access

### Download Document (Authenticated)
```
GET /documents/{document}/download
```
Downloads document file with proper authorization checks.

### View Document (Authenticated)
```
GET /documents/{document}/view
```
Display document inline for supported formats.

### Delete Document (Admin Only)
```
DELETE /documents/{document}
```
Permanently remove document and associated file.

## Database Schema

### document_categories table
```
- id (Primary Key)
- name (Unique)
- description (Optional)
- slug (Unique)
- icon (Optional - Heroicon reference)
```

### documents table
```
- id (Primary Key)
- document_category_id (Foreign Key → document_categories)
- loan_id (Foreign Key → loans, nullable)
- borrower_id (Foreign Key → borrowers, nullable)
- organization_id (Integer, nullable)
- branch_id (Foreign Key → branches)
- uploaded_by (Foreign Key → users)
- file_path (String - storage path)
- file_name (String - original filename)
- file_size (BigInteger - bytes)
- file_mime_type (String - file type)
- status (String: active, archived, expired)
- description (Text, optional)
- expiry_date (DateTime, optional)
- created_at, updated_at (Timestamps)

Indices on: category_id, loan_id, borrower_id, organization_id, branch_id, status, uploaded_by, created_at
```

## File Storage Configuration

### Storage Disk: documents
- **Driver**: Local filesystem
- **Root Directory**: `storage/app/documents/`
- **Visibility**: Private (requires authentication to access)
- **Access Pattern**: Via authenticated download routes

### File Organization
Files are organized by upload order in the storage/app/documents directory. Each file is referenced by its path stored in the documents table.

## Models & Relationships

### DocumentCategory Model
```php
// Relationships
- documents() → HasMany(Document)

// Key Methods
- None specific (lightweight category model)
```

### Document Model
```php
// Relationships
- category() → BelongsTo(DocumentCategory)
- loan() → BelongsTo(Loan)
- borrower() → BelongsTo(Borrower)
- uploadedBy() → BelongsTo(User)
- organization() → Referenced via organization_id
- branch() → BelongsTo(Branches)

// Helper Methods
- isExpired() - Check expiration status
- daysUntilExpiry() - Calculate days until expiration
- isExpiringSoon() - Check if expiring within 30 days
- getReadableFileSizeAttribute() - Format bytes to KB/MB/GB
- getActivitylogOptions() - Configure activity logging

// Query Scopes
- active() - Only active documents
- orgBranch() - Filter by user's org/branch
```

## Permissions

Document management uses resource-based permissions:
- `view_document` - View documents
- `view_any_document` - View all documents
- `create_document` - Upload documents
- `update_document` - Edit document metadata
- `delete_document` - Remove documents

Access controlled via:
1. Role-based authorization (HasRoles trait)
2. Cooperative scoping (agents only see own cooperative)
3. Method-level checks in DocumentResource

## Security Considerations

### File Security
- Files stored in private storage directory (not web-accessible)
- Access only through authenticated routes
- Download routes verify authorization before serving

### Access Control
- Automatic cooperative scoping for agents
- Admins have full access
- Unauthorized users cannot access documents

### Activity Tracking
- All document operations logged to activity_log table
- Tracks: file_name, status, description, expiry_date changes
- Audit trail available for compliance

## Best Practices

### Document Organization
1. **Consistent Naming** - Use descriptive file names
2. **Proper Categorization** - Select correct document type
3. **Link to Borrowers** - Associate with borrowers/loans when possible
4. **Set Expiration Dates** - For time-sensitive documents (KYC, licenses)
5. **Regular Archival** - Archive completed documents to reduce active list

### Document Upload Workflow
1. Verify borrower exists before uploading
2. Confirm document category is appropriate
3. Set expiry dates for documents with expiration (KYC, licenses)
4. Add descriptive notes in description field
5. Check file format is supported (PDF recommended)

### Maintenance
- Regular review of documents approaching expiration
- Archive completed loan documents
- Clean up duplicate uploads
- Monitor storage usage

## Troubleshooting

### File Upload Fails
- Verify file size < 10MB
- Confirm file format is supported
- Check storage permissions on server
- Clear browser cache and retry

### Cannot Download Document
- Verify authentication (logged in)
- Confirm user role has access
- Check file exists on server (`storage/app/documents/`)
- Verify user is in correct cooperative/branch

### Document Not Appearing in List
- Check filter settings (status, category)
- Verify user role (agents only see own cooperative)
- Search by borrower name or file name
- Check if document was archived

### Expiration Alerts Not Showing
- Verify expiry_date is set on document
- Check document status is 'active'
- Note: Automated alerts for expiring documents not yet implemented (scheduled for Phase 7)

## Future Enhancements

### Planned Features
1. **Document Preview** - View PDF/image files inline before download
2. **Bulk Upload** - Drag-and-drop multiple files at once
3. **Version Control** - Track document revisions
4. **Expiration Alerts** - Scheduled notifications for expiring documents
5. **Digital Signatures** - Add signature capability
6. **OCR Integration** - Extract text from documents
7. **Automated Archival** - Archive documents after loan completion

## File Locations

### Key Files
- **Model**: `app/Models/Document.php`
- **Model**: `app/Models/DocumentCategory.php`
- **Resource**: `app/Filament/Resources/DocumentResource.php`
- **Controller**: `app/Http/Controllers/DocumentController.php`
- **Migrations**: `database/migrations/2026_07_17_000003_create_document_categories_table.php`
- **Migrations**: `database/migrations/2026_07_17_000004_create_documents_table.php`
- **Seeder**: `database/seeders/DocumentCategorySeeder.php`
- **Storage Config**: `config/filesystems.php`

### Routes
- All routes: `routes/web.php` under `/documents` prefix
- All protected by `auth:sanctum` middleware

## Related Features

The Document Management System integrates with:
- **Borrower Management** - Documents linked to borrowers
- **Loan Management** - Documents associated with specific loans
- **User Authorization** - Role-based access control
- **Activity Logging** - Audit trail tracking
- **Branch Management** - Branch-scoped document filtering

---

**Last Updated**: July 17, 2026
**Version**: 1.0
**Status**: Implementation Complete

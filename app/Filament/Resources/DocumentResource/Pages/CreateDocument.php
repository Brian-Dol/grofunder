<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $data['uploaded_by'] = $user->id;
        $data['organization_id'] = $user->organization_id;
        
        // Set branch_id - use user's branch or default to organization's main branch (ID 1)
        // This ensures the NOT NULL constraint is satisfied
        if ($user->branch_id) {
            $data['branch_id'] = $user->branch_id;
        } else {
            // Default to branch 1 if user doesn't have a branch assigned
            // For super_admin users without a specific branch
            $data['branch_id'] = 1;
        }

        // Handle file path and get file information
        if (isset($data['file_path']) && !empty($data['file_path'])) {
            $filePath = $data['file_path'];
            
            // Get the disk
            $disk = Storage::disk('documents');
            
            // Extract filename from the path (handles both 'uploads/filename.pdf' and 'filename.pdf')
            $filename = basename($filePath);
            $data['file_name'] = $filename;
            
            // Get file size and mime type from storage
            try {
                if ($disk->exists($filePath)) {
                    $data['file_size'] = $disk->size($filePath);
                    // Try to get mime type, fallback to octet-stream
                    $mimeType = $disk->mimeType($filePath);
                    $data['file_mime_type'] = $mimeType ?? 'application/octet-stream';
                } else {
                    // File might be on the default disk or temp location
                    $data['file_size'] = 0;
                    $data['file_mime_type'] = 'application/octet-stream';
                }
            } catch (\Exception $e) {
                $data['file_size'] = 0;
                $data['file_mime_type'] = 'application/octet-stream';
            }
        }

        return $data;
    }
}

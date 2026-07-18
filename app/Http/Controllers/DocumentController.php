<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class DocumentController extends Controller
{
    /**
     * Download a document
     */
    public function download(Document $document)
    {
        // Authorization check - user can only download their cooperative's documents or if admin
        $user = Auth::user();

        if (!$user->hasRole(['super_admin', 'admin'])) {
            // Agent can only download their cooperative's documents
            if ($user->hasRole('agent') && $user->cooperative_id) {
                $hasAccess = $document->borrower?->cooperative_id == $user->cooperative_id ||
                    $document->loan?->borrower?->cooperative_id == $user->cooperative_id;
                
                if (!$hasAccess) {
                    throw new AuthorizationException('Unauthorized');
                }
            } else {
                throw new AuthorizationException('Unauthorized');
            }
        }

        // Verify file exists
        if (!Storage::disk('documents')->exists($document->file_path)) {
            abort(404, 'Document file not found');
        }

        return Storage::disk('documents')->download($document->file_path, $document->file_name);
    }

    /**
     * View a document inline (for supported formats)
     */
    public function view(Document $document)
    {
        // Authorization check
        $user = Auth::user();

        if (!$user->hasRole(['super_admin', 'admin'])) {
            if ($user->hasRole('agent') && $user->cooperative_id) {
                $hasAccess = $document->borrower?->cooperative_id == $user->cooperative_id ||
                    $document->loan?->borrower?->cooperative_id == $user->cooperative_id;
                
                if (!$hasAccess) {
                    throw new AuthorizationException('Unauthorized');
                }
            } else {
                throw new AuthorizationException('Unauthorized');
            }
        }

        // Verify file exists
        if (!Storage::disk('documents')->exists($document->file_path)) {
            abort(404, 'Document file not found');
        }

        return response()->file(Storage::disk('documents')->path($document->file_path));
    }

    /**
     * Delete a document
     */
    public function delete(Document $document)
    {
        // Authorization check
        $user = Auth::user();

        if (!$user->hasRole(['super_admin', 'admin'])) {
            throw new AuthorizationException('Unauthorized');
        }

        // Delete file if exists
        if (Storage::disk('documents')->exists($document->file_path)) {
            Storage::disk('documents')->delete($document->file_path);
        }

        // Delete database record
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }
}

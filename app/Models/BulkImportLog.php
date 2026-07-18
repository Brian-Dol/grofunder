<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BulkImportLog extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'import_type',
        'file_name',
        'total_rows',
        'successful_imports',
        'failed_imports',
        'success_rate',
        'errors',
        'warnings',
        'status',
    ];

    protected $casts = [
        'errors' => 'json',
        'warnings' => 'json',
    ];

    /**
     * Get the user who performed the import
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['import_type', 'file_name', 'successful_imports', 'failed_imports', 'status'])
            ->useLogName('bulk_imports');
    }
}

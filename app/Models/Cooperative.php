<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Cooperative extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'region',
        'contact_email',
        'contact_phone',
        'established_date',
        'status',
        'organization_id',
        'branch_id',
    ];

    protected $casts = [
        'established_date' => 'date',
        'status' => 'string',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    /**
     * Get all borrowers/farmers belonging to this cooperative
     */
    public function borrowers(): HasMany
    {
        return $this->hasMany(Borrower::class, 'cooperative_id');
    }

    /**
     * Get all agents managing this cooperative
     */
    public function agents(): HasMany
    {
        return $this->hasMany(User::class, 'cooperative_id')
            ->where('role', 'agent');
    }

    /**
     * Get the branch this cooperative belongs to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branches::class, 'branch_id');
    }
}

<?php

namespace App\Models;

use App\Traits\CacheableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, CacheableModel;

    public const STATUSES = [
        'active'    => 'Actif',
        'on_hold'   => 'En attente',
        'completed' => 'Terminé',
        'cancelled' => 'Annulé',
    ];

    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'due_date',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date'   => 'date',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}

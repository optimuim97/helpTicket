<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class InterventionSheet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'site',
        'building',
        'agent_name',
        'reported_fault',
        'observation',
        'incidence',
        'concerned_services',
        'work_done',
        'supplies_used',
        'start_date',
        'end_date',
        'epi_used',
        'client_satisfaction',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'concerned_services' => 'array',
        'epi_used'           => 'array',
        'start_date'         => 'datetime',
        'end_date'           => 'datetime',
    ];

    const INCIDENCES = ['critique', 'majeur', 'mineur'];
    const STATUSES   = ['brouillon', 'valide', 'signe'];

    const INCIDENCE_LABELS = [
        'critique' => 'Critique',
        'majeur'   => 'Majeur',
        'mineur'   => 'Mineur',
    ];

    const STATUS_LABELS = [
        'brouillon' => 'Brouillon',
        'valide'    => 'Validé',
        'signe'     => 'Signé',
    ];

    const AVAILABLE_SERVICES = [
        'Informatique',
        'Réseau',
        'Téléphonie',
        'Electricité',
        'Climatisation',
        'Sécurité',
        'Autre',
    ];

    const AVAILABLE_EPI = [
        'Casque',
        'Gants',
        'Gilet',
        'Chaussures de sécurité',
        'Lunettes',
        'Masque',
        'Autre',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->reference)) {
                $year  = now()->year;
                $count = static::whereYear('created_at', $year)->withTrashed()->count() + 1;
                $model->reference = sprintf('FTI-%s-%03d', $year, $count);
            }
        });
    }

    public function validations(): HasMany
    {
        return $this->hasMany(InterventionSheetValidation::class);
    }

    public function tickets(): MorphMany
    {
        return $this->morphMany(Ticket::class, 'linkable');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getIsFullyValidatedAttribute(): bool
    {
        return $this->validations()->whereNotNull('validated_at')->count() === 3;
    }

    public function getValidationCountAttribute(): int
    {
        return $this->validations()->whereNotNull('validated_at')->count();
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('reference', 'like', "%{$search}%")
              ->orWhere('agent_name', 'like', "%{$search}%")
              ->orWhere('site', 'like', "%{$search}%")
              ->orWhere('building', 'like', "%{$search}%");
        });
    }
}

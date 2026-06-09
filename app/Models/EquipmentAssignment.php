<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class EquipmentAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'equipment_type',
        'equipment_model',
        'equipment_serial',
        'equipment_mac',
        'agent_matricule',
        'agent_name',
        'agent_direction',
        'agent_department',
        'operation_type',
        'old_equipment_serial',
        'notes',
        'status',
        'created_by',
    ];

    const OPERATION_TYPES = ['affectation', 'remplacement'];
    const STATUSES        = ['brouillon', 'valide', 'signe'];

    const STATUS_LABELS = [
        'brouillon' => 'Brouillon',
        'valide'    => 'Validé',
        'signe'     => 'Signé',
    ];

    const OPERATION_LABELS = [
        'affectation' => 'Affectation',
        'remplacement' => 'Remplacement',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->reference)) {
                $year  = now()->year;
                $count = static::whereYear('created_at', $year)->withTrashed()->count() + 1;
                $model->reference = sprintf('FTE-%s-%03d', $year, $count);
            }
        });
    }

    public function validations(): HasMany
    {
        return $this->hasMany(EquipmentAssignmentValidation::class);
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
              ->orWhere('agent_matricule', 'like', "%{$search}%")
              ->orWhere('equipment_serial', 'like', "%{$search}%");
        });
    }
}

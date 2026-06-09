<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterventionSheetValidation extends Model
{
    protected $fillable = [
        'intervention_sheet_id',
        'validator_role',
        'validator_name',
        'validated_at',
        'comment',
        'signature',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    const ROLES = [
        'chef_atelier' => 'Chef d\'atelier',
        'utilisateur'  => 'Utilisateur',
        'chef_service' => 'Chef de service',
    ];

    public function interventionSheet(): BelongsTo
    {
        return $this->belongsTo(InterventionSheet::class);
    }
}

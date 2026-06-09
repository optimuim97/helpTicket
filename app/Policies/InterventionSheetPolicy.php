<?php

namespace App\Policies;

use App\Models\InterventionSheet;
use App\Models\User;

class InterventionSheetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_intervention_sheets');
    }

    public function view(User $user, InterventionSheet $sheet): bool
    {
        return $user->can('view_any_intervention_sheets')
            || $sheet->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('create_intervention_sheets');
    }

    public function update(User $user, InterventionSheet $sheet): bool
    {
        return $user->can('update_intervention_sheets')
            && $sheet->status === 'brouillon';
    }

    public function delete(User $user, InterventionSheet $sheet): bool
    {
        return $user->can('delete_intervention_sheets');
    }

    public function validate(User $user, InterventionSheet $sheet): bool
    {
        return $user->can('validate_intervention_sheets');
    }
}

<?php

namespace App\Policies;

use App\Models\EquipmentAssignment;
use App\Models\User;

class EquipmentAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_equipment_assignments');
    }

    public function view(User $user, EquipmentAssignment $assignment): bool
    {
        return $user->can('view_any_equipment_assignments')
            || $assignment->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('create_equipment_assignments');
    }

    public function update(User $user, EquipmentAssignment $assignment): bool
    {
        return $user->can('update_equipment_assignments')
            && $assignment->status === 'brouillon';
    }

    public function delete(User $user, EquipmentAssignment $assignment): bool
    {
        return $user->can('delete_equipment_assignments');
    }

    public function validate(User $user, EquipmentAssignment $assignment): bool
    {
        return $user->can('validate_equipment_assignments');
    }
}

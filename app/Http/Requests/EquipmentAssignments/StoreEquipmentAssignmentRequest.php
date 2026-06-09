<?php

namespace App\Http\Requests\EquipmentAssignments;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create_equipment_assignments') ?? false;
    }

    public function rules(): array
    {
        return [
            'equipment_type'      => ['required', 'string', 'max:255'],
            'equipment_model'     => ['required', 'string', 'max:255'],
            'equipment_serial'    => ['required', 'string', 'max:255'],
            'equipment_mac'       => ['nullable', 'string', 'max:255'],
            'agent_matricule'     => ['required', 'string', 'max:100'],
            'agent_name'          => ['required', 'string', 'max:255'],
            'agent_direction'     => ['required', 'string', 'max:255'],
            'agent_department'    => ['required', 'string', 'max:255'],
            'operation_type'      => ['required', 'in:affectation,remplacement'],
            'old_equipment_serial'=> ['nullable', 'required_if:operation_type,remplacement', 'string', 'max:255'],
            'notes'               => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'equipment_type.required'       => 'Le type d\'équipement est obligatoire.',
            'equipment_model.required'      => 'Le modèle est obligatoire.',
            'equipment_serial.required'     => 'Le numéro de série est obligatoire.',
            'agent_matricule.required'      => 'Le matricule de l\'agent est obligatoire.',
            'agent_name.required'           => 'Le nom de l\'agent est obligatoire.',
            'agent_direction.required'      => 'La direction est obligatoire.',
            'agent_department.required'     => 'Le département est obligatoire.',
            'operation_type.required'       => 'Le type d\'opération est obligatoire.',
            'operation_type.in'             => 'Le type d\'opération doit être affectation ou remplacement.',
            'old_equipment_serial.required_if' => 'L\'ancien numéro de série est requis pour un remplacement.',
        ];
    }
}

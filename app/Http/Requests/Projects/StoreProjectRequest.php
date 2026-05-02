<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create_projects') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status'      => ['required', 'in:active,on_hold,completed,cancelled'],
            'start_date'  => ['nullable', 'date'],
            'due_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'Le nom du projet est obligatoire.',
            'name.max'               => 'Le nom ne peut pas dépasser 255 caractères.',
            'status.required'        => 'Le statut est obligatoire.',
            'status.in'              => 'Le statut sélectionné est invalide.',
            'due_date.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
        ];
    }
}

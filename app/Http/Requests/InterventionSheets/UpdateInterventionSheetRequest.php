<?php

namespace App\Http\Requests\InterventionSheets;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInterventionSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update_intervention_sheets') ?? false;
    }

    public function rules(): array
    {
        return [
            'site'                => ['required', 'string', 'max:255'],
            'building'            => ['required', 'string', 'max:255'],
            'agent_name'          => ['required', 'string', 'max:255'],
            'reported_fault'      => ['required', 'string', 'max:2000'],
            'observation'         => ['nullable', 'string', 'max:2000'],
            'incidence'           => ['required', 'in:critique,majeur,mineur'],
            'concerned_services'  => ['nullable', 'array'],
            'concerned_services.*'=> ['string', 'max:255'],
            'work_done'           => ['nullable', 'string', 'max:2000'],
            'supplies_used'       => ['nullable', 'string', 'max:2000'],
            'start_date'          => ['required', 'date'],
            'end_date'            => ['nullable', 'date', 'after_or_equal:start_date'],
            'epi_used'            => ['nullable', 'array'],
            'epi_used.*'          => ['string', 'max:255'],
            'client_satisfaction' => ['nullable', 'integer', 'min:1', 'max:4'],
            'notes'               => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'site.required'           => 'Le site est obligatoire.',
            'building.required'       => 'Le bâtiment est obligatoire.',
            'agent_name.required'     => 'Le nom de l\'agent est obligatoire.',
            'reported_fault.required' => 'La panne signalée est obligatoire.',
            'incidence.required'      => 'L\'incidence est obligatoire.',
            'incidence.in'            => 'L\'incidence doit être critique, majeur ou mineur.',
            'start_date.required'     => 'La date de début est obligatoire.',
            'end_date.after_or_equal' => 'La date de fin doit être postérieure à la date de début.',
            'client_satisfaction.min' => 'La satisfaction doit être entre 1 et 4.',
            'client_satisfaction.max' => 'La satisfaction doit être entre 1 et 4.',
        ];
    }
}

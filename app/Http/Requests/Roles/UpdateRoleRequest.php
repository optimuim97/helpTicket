<?php

namespace App\Http\Requests\Roles;

use App\DTOs\Roles\RoleUpdateDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('edit_roles');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $roleId = $this->route('role');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId)
            ],
            'guard_name' => ['nullable', 'string', 'in:web,api'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }

    /**
     * Get custom validation messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du rôle est obligatoire.',
            'name.unique' => 'Ce nom de rôle existe déjà.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'guard_name.in' => 'Le guard doit être web ou api.',
            'permissions.array' => 'Les permissions doivent être un tableau.',
            'permissions.*.exists' => 'Une des permissions sélectionnées n\'existe pas.',
        ];
    }

    /**
     * Get custom attribute names for error messages
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'guard_name' => 'guard',
            'permissions' => 'permissions',
        ];
    }

    /**
     * Convert validated data to DTO
     *
     * @return RoleUpdateDTO
     */
    public function toDTO(): RoleUpdateDTO
    {
        return RoleUpdateDTO::fromRequest($this->validated());
    }
}

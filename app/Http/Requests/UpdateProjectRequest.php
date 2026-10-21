<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('project')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $requiredOrSometimes = $this->isJson() || $this->is('api/*') ? 'sometimes' : 'required';

        return [
            'name' => [$requiredOrSometimes, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => [$requiredOrSometimes, 'string', Rule::enum(ProjectStatus::class)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'has_members' => ['sometimes', 'boolean'],
            'members' => ['sometimes', 'array'],
            'members.*.id' => ['nullable', 'integer', 'exists:users,id'],
            'members.*.role' => ['nullable', 'string', 'in:member,manager'],
        ];
    }
}

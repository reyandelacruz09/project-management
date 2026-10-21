<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'commentable_type' => ['required', 'string', 'in:App\Models\Project,App\Models\Task'],
            'commentable_id' => ['required', 'integer'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }
}

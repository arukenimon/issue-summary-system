<?php

namespace App\Http\Requests;

use App\Models\Issue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'priority'    => ['required', Rule::in(Issue::priorities())],
            'category'    => ['required', Rule::in(Issue::categories())],
            'status'      => ['sometimes', Rule::in(Issue::statuses())],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'A title is required.',
            'title.min'            => 'The title must be at least 3 characters.',
            'description.required' => 'A description is required.',
            'description.min'      => 'Please provide at least 10 characters of description.',
            'priority.required'    => 'Priority is required.',
            'priority.in'          => 'Priority must be one of: low, medium, high, critical.',
            'category.required'    => 'Category is required.',
            'category.in'          => 'Category must be one of: bug, feature, infrastructure, security, other.',
            'status.in'            => 'Status must be one of: open, in_progress, resolved, closed.',
        ];
    }
}

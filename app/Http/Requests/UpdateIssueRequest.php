<?php

namespace App\Http\Requests;

use App\Models\Issue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'min:3', 'max:255'],
            'description' => ['sometimes', 'string', 'min:10'],
            'priority'    => ['sometimes', Rule::in(Issue::priorities())],
            'category'    => ['sometimes', Rule::in(Issue::categories())],
            'status'      => ['sometimes', Rule::in(Issue::statuses())],
        ];
    }

    public function messages(): array
    {
        return [
            'title.min'       => 'The title must be at least 3 characters.',
            'description.min' => 'Please provide at least 10 characters of description.',
            'priority.in'     => 'Priority must be one of: low, medium, high, critical.',
            'category.in'     => 'Category must be one of: bug, feature, infrastructure, security, other.',
            'status.in'       => 'Status must be one of: open, in_progress, resolved, closed.',
        ];
    }
}

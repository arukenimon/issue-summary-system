<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Laravel's TrimStrings middleware runs before validation, so a whitespace-only
        // value is trimmed to '' and rejected by 'required'. No empty strings get through.
        return [
            'author_name' => ['required', 'string', 'max:255'],
            'body'        => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'author_name.required' => 'A comment author name is required.',
            'body.required'        => 'A comment body is required.',
        ];
    }
}

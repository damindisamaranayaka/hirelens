<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJobPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
    return [
        'title'        => ['sometimes', 'string', 'max:255'],
        'company_name' => ['sometimes', 'string', 'max:255'],
        'description'  => ['sometimes', 'string', 'max:10000'],
        'location'     => ['sometimes', 'string', 'max:255'],
        'type'         => ['sometimes', 'in:full-time,part-time,contract,internship'],
        'salary_min'   => ['nullable', 'integer', 'min:0'],
        'salary_max'   => ['nullable', 'integer', 'min:0', 'gte:salary_min'],
        'status'       => ['sometimes', 'in:open,closed'],
    ];
    }
}

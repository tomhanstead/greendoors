<?php

namespace App\Http\Requests;

use App\Exceptions\InvalidProductFilterException;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category' => 'nullable|string|exists:categories,name',
            'sort' => 'nullable|string|in:asc,desc',
            'search' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'sort.in' => 'Sort value must be either "asc" or "desc".',
            'category.exists' => 'The selected category does not exist.',
        ];
    }

    public function passedValidation()
    {
        $allowedFilters = ['category', 'sort', 'search', 'page', 'limit'];
        $extraFilters = array_diff(array_keys($this->query()), $allowedFilters);

        if (!empty($extraFilters)) {
            throw new InvalidProductFilterException('Invalid filter(s) applied: ' . implode(', ', $extraFilters));
        }
    }

}

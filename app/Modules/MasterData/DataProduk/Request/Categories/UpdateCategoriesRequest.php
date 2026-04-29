<?php

namespace App\Modules\MasterData\DataProduk\Request\Categories;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoriesRequest extends FormRequest
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
            'category_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'category_name')->ignore($this->currentCategoryId()),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private function currentCategoryId(): ?string
    {
        $category = $this->route('category')
            ?? $this->route('categories')
            ?? $this->route('id');

        if (is_object($category) && method_exists($category, 'getKey')) {
            return (string) $category->getKey();
        }

        return $category !== null ? (string) $category : null;
    }
    
    public function messages(): array
    {
        return [
            'category_name.unique' => 'The category name has already been taken.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroceryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'sometimes|required|numeric|gt:0|decimal:0,2',
            'stock' => 'sometimes|required|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ];
    }
}

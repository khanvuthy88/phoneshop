<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\FormType;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'form_type' => ['required', Rule::in([FormType::CREATE_TYPE, FormType::EDIT_TYPE])],
            'name' => ['required', Rule::unique('products')->ignore(request('product'))],
            'product_code' => ['required', 'size:8', Rule::unique('products', 'code')->ignore(request('product'))],
            'category' => 'required|integer',
            'brand' => 'required|integer',
            'cost' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'alert_quantity' => 'required|integer|min:0',
            'photo' => ['nullable', Rule::requiredIf(request('form_type') == FormType::CREATE_TYPE), 'image', 'mimes:jpg,jpeg,png'],
        ];
    }
}

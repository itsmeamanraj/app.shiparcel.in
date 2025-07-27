<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWarehouseRequest extends FormRequest
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
            'address_title' => 'required|string|max:255',
            'sender_name' => 'required|string|max:255',
            'full_address' => 'required|string|max:500',
            'phone' => 'required|regex:/^[0-9]{10,15}$/',
            'pincode' => 'required|digits:6',
        ];
    }


    public function messages(): array
    {
        return [
            'address_title.required' => 'The address title is required.',
            'sender_name.required' => 'The sender name is required.',
            'full_address.required' => 'The full address is required.',
            'phone.required' => 'The phone number is required.',
            'phone.regex' => 'The phone number must be between 10 to 15 digits.',
            'pincode.required' => 'The pincode is required.',
            'pincode.digits' => 'The pincode must be exactly 6 digits.',
        ];
    }
}

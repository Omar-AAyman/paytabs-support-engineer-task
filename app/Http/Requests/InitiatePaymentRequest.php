<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|min:2|max:128',
            'customer_email' => 'required|email|max:128',
            'customer_phone' => 'required|string|min:2|max:32',
            'address' => 'required|string|min:2|max:128', 
            'city' => 'required|string|min:2|max:128',
            'shipping_method' => 'required|in:pickup,shipping',
        ];
    }
}

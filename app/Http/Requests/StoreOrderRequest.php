<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'products.required' => 'Please select at least one product.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($validator->getData()['products'] as $index => $item) {
                if (($item['quantity'] ?? 0) <= 0) continue;

                $product = \App\Models\Product::find($item['id'] ?? null);
                
                if ($product && $item['quantity'] > $product->stock) {
                     $validator->errors()->add(
                        "products.{$index}.quantity",
                        "Only {$product->stock} left for {$product->name}"
                    );
                }
            }
        });
    }
}

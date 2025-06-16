<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: authorisation logix
        return $this->user() !== null;
    }

    public function rules(): array
    {
        /**
         * Get the validation rules that apply to the request.
         *
         * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
         */
        // Define the base validation rules for a single order item.
        $itemRules = [
            'product_id' => 'sometimes|integer',
            'quantity' => 'required|integer|min:1',
            'expected_delivery_date' => 'required|date|after_or_equal:today',
            'purchase_cycle_id' => 'sometimes|integer',
            'requires_storage_acknowledged' => 'sometimes|boolean',
            'final_line_price' => 'required|numeric|min:0',
            'storage_cost_applied' => 'sometimes|numeric|min:0',
            'daily_storage_price' => 'sometimes|string|min:0',
            'product_subtotal' => 'required|numeric|min:0',
        ];

        // Check if the request contains a cart with multiple items.
        if ($this->has('items')) {
            $cartRules = [
                'items' => 'required|array|min:1', // Ensure 'items' is a non-empty array.
            ];

            // Apply the item rules to each element in the 'items' array.
            foreach ($itemRules as $field => $rule) {
                $cartRules['items.*.'.$field] = $rule;
            }

            return $cartRules;
        }

        // Otherwise, apply the rules for a single item order.
        return $itemRules;
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: authorisation logix
        return true;
    }

    public function rules(): array
    {
        /**
         * Get the validation rules that apply to the request.
         *
         * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
         */
        return [
            'product_id' => 'sometimes',
            'quantity' => 'required|integer',
            'expected_delivery_date' => 'required|date',
            'purchase_cycle_id' => 'sometimes|integer',
            'requires_storage_acknowledged' => 'sometimes|boolean',
            'final_line_price' => 'required|decimal:2',
            'storage_cost_applied' => 'sometimes|decimal:2',
            'daily_storage_price' => 'sometimes|decimal:2',
            'product_subtotal' => 'required|decimal:2',
        ];
    }
}

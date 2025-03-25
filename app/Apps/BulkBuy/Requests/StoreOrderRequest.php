<?php

namespace App\Apps\BulkBuy\Requests;

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
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer',
            'expected_delivery_date' => 'required|date',
        ];
    }
}

<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class PayOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'string', 'in:bank_transfer,momo,card'],
        ];
    }

    public function attributes(): array
    {
        return [
            'payment_method' => 'Phương thức thanh toán',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in' => 'Phương thức thanh toán được chọn không hợp lệ.',
        ];
    }
}

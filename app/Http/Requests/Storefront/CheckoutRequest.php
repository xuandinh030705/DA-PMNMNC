<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tickets' => ['required', 'array'],
            'tickets.*' => ['nullable', 'integer', 'min:0'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'string', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function attributes(): array
    {
        return [
            'tickets' => 'Danh sách số lượng vé',
            'customer_name' => 'Tên người nhận vé',
            'customer_email' => 'Email nhận vé',
            'customer_phone' => 'Số điện thoại',
        ];
    }

    public function messages(): array
    {
        return [
            'tickets.required' => 'Vui lòng chọn số lượng vé muốn đặt.',
            'customer_name.required' => 'Vui lòng nhập tên người nhận vé.',
            'customer_email.required' => 'Vui lòng nhập địa chỉ email nhận vé.',
            'customer_email.email' => 'Địa chỉ email nhận vé không hợp lệ.',
        ];
    }
}

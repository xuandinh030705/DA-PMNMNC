<?php

namespace App\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

class TicketTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ticketType = $this->route('ticket_type');
        $minQuantity = $ticketType ? $ticketType->sold : 1;

        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', "min:{$minQuantity}"],
            'sale_start' => ['required', 'date'],
            'sale_end' => ['required', 'date', 'after:sale_start'],
            'max_per_order' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Tên loại vé',
            'price' => 'Giá vé',
            'quantity' => 'Số lượng vé phát hành',
            'sale_start' => 'Thời gian mở bán',
            'sale_end' => 'Thời gian kết thúc bán',
            'max_per_order' => 'Số vé tối đa mỗi đơn',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên loại vé.',
            'price.required' => 'Vui lòng nhập giá vé.',
            'price.min' => 'Giá vé không được âm.',
            'quantity.required' => 'Vui lòng nhập số lượng vé.',
            'quantity.min' => 'Số lượng vé không được nhỏ hơn số vé đã bán ra (:min vé).',
            'sale_start.required' => 'Vui lòng chọn thời gian mở bán.',
            'sale_end.required' => 'Vui lòng chọn thời gian kết thúc bán.',
            'sale_end.after' => 'Thời gian kết thúc bán phải sau thời gian mở bán.',
            'max_per_order.required' => 'Vui lòng giới hạn số vé trên mỗi đơn.',
            'max_per_order.min' => 'Số vé tối đa trên mỗi đơn phải ít nhất là 1.',
        ];
    }
}

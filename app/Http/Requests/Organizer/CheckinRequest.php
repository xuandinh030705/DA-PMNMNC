<?php

namespace App\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

class CheckinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_code' => ['required', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'ticket_code' => 'Mã vé điểm danh',
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_code.required' => 'Vui lòng nhập mã vé hoặc quét mã QR cần kiểm tra.',
            'ticket_code.max' => 'Dữ liệu quét được quá dài, vui lòng thử lại.',
        ];
    }
}

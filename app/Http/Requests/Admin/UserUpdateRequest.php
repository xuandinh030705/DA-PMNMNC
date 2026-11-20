<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Họ và tên',
            'phone' => 'Số điện thoại',
            'role' => 'Vai trò',
            'is_active' => 'Trạng thái hoạt động',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'role.in' => 'Vai trò được chọn không hợp lệ.',
        ];
    }
}

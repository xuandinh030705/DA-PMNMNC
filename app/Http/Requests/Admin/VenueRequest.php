<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Tên địa điểm',
            'address' => 'Địa chỉ chi tiết',
            'city' => 'Tỉnh/Thành phố',
            'capacity' => 'Sức chứa tối đa',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên địa điểm.',
            'address.required' => 'Vui lòng nhập địa chỉ chi tiết của địa điểm.',
            'city.required' => 'Vui lòng nhập tỉnh/thành phố.',
            'capacity.required' => 'Vui lòng nhập sức chứa.',
            'capacity.min' => 'Sức chứa phải lớn hơn 0.',
        ];
    }
}

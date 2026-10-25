<?php

namespace App\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'venue_id' => ['required', 'exists:venues,id'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'description' => ['required', 'string'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'Tiêu đề sự kiện',
            'category_id' => 'Danh mục sự kiện',
            'venue_id' => 'Địa điểm tổ chức',
            'start_time' => 'Thời gian bắt đầu',
            'end_time' => 'Thời gian kết thúc',
            'description' => 'Mô tả sự kiện',
            'banner' => 'Ảnh banner sự kiện',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập tiêu đề sự kiện.',
            'category_id.required' => 'Vui lòng chọn danh mục sự kiện.',
            'category_id.exists' => 'Danh mục được chọn không hợp lệ.',
            'venue_id.required' => 'Vui lòng chọn địa điểm tổ chức.',
            'venue_id.exists' => 'Địa điểm được chọn không hợp lệ.',
            'start_time.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'start_time.after' => 'Thời gian bắt đầu sự kiện phải ở thời điểm tương lai.',
            'end_time.required' => 'Vui lòng chọn thời gian kết thúc.',
            'end_time.after' => 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.',
            'description.required' => 'Vui lòng nhập mô tả chi tiết cho sự kiện.',
            'banner.image' => 'File tải lên phải là hình ảnh hợp lệ.',
            'banner.mimes' => 'Banner chỉ chấp nhận các định dạng: jpg, jpeg, png, webp.',
            'banner.max' => 'Dung lượng ảnh banner không được vượt quá 2MB (2048 KB).',
        ];
    }
}

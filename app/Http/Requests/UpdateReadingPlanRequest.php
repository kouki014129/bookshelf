<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReadingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'deadline' => [
                'required',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'deadline.required' => '期日は必須です。',
            'deadline.date' => '正しい日付を入力してください。',
        ];
    }
}

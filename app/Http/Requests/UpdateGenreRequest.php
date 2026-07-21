<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGenreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:genres,name,' . $this->genre->id],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'ジャンル名は必須です。',
            'name.max'      => 'ジャンル名は50文字以内で入力してください。',
            'name.unique'   => 'このジャンル名はすでに登録されています。',
        ];
    }
}
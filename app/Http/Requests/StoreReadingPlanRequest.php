<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingPlanRequest extends FormRequest
{
    /**
     * 読書計画登録リクエストを許可する。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 読書計画登録フォームの入力値を検証する。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'book_id' => [
                'required',
                'integer',
                'exists:books,id',
            ],

            'deadline' => [
                'required',
                'date',
                'date_format:Y-m-d',
            ],
        ];
    }

    /**
     * 読書計画登録フォームのバリデーションエラーメッセージを返す。
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.integer' => '書籍は正しい値を選択してください。',
            'book_id.exists' => '選択された書籍が存在しません。',
            'deadline.required' => '期日は必須です。',
            'deadline.date' => '正しい日付を入力してください。',
            'deadline.date_format' => '期日はYYYY-MM-DD形式で入力してください。',
        ];
    }
}

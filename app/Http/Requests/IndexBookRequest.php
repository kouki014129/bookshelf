<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexBookRequest extends FormRequest
{
    /**
     * 書籍一覧の検索・絞り込み・ソートリクエストを許可する。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーション前に検索キーワードの前後空白を除去する。
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('keyword')) {
            $this->merge([
                'keyword' => trim((string) $this->input('keyword')),
            ]);
        }
    }

    /**
     * 書籍一覧の検索・絞り込み・ソート条件を検証する。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'keyword' => [
                'nullable',
                'string',
                'max:255',
            ],

            'genre' => [
                'nullable',
                'integer',
                'exists:genres,id',
            ],

            'sort' => [
                'nullable',
                Rule::in([
                    'latest',
                    'oldest',
                    'title',
                    'rating',
                ]),
            ],
        ];
    }

    /**
     * バリデーションエラーメッセージを返す。
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'keyword.string' => 'キーワードは文字列で入力してください。',
            'keyword.max' => 'キーワードは255文字以内で入力してください。',

            'genre.integer' => 'ジャンルは正しい値を選択してください。',
            'genre.exists' => '選択されたジャンルは存在しません。',

            'sort.in' => '並び順の指定が正しくありません。',
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V1;

class StoreBookRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'author' => [
                'required',
                'string',
                'max:255',
            ],

            'isbn' => [
                'nullable',
                'digits:13',
                'unique:books,isbn',
            ],

            'published_date' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'image_url' => [
                'nullable',
                'url',
            ],

            'genres' => [
                'required',
                'array',
                'min:1',
            ],

            'genres.*' => [
                'integer',
                'exists:genres,id',
                'distinct',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルは必須です。',
            'title.string' => 'タイトルは文字列で入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'author.required' => '著者名は必須です。',
            'author.string' => '著者名は文字列で入力してください。',
            'author.max' => '著者名は255文字以内で入力してください。',
            'isbn.digits' => 'ISBNは13桁で入力してください。',
            'isbn.unique' => 'このISBNはすでに登録されています。',
            'published_date.date' => '有効な日付を入力してください。',
            'published_date.date_format' => '出版日はYYYY-MM-DD形式で入力してください。',
            'description.string' => '説明文は文字列で入力してください。',
            'description.max' => '説明文は1000文字以内で入力してください。',
            'image_url.url' => '有効な画像URLを入力してください。',
            'genres.required' => 'ジャンルを1つ以上選択してください。',
            'genres.array' => 'ジャンルは配列形式で指定してください。',
            'genres.min' => 'ジャンルを1つ以上選択してください。',
            'genres.*.integer' => 'ジャンルIDは整数で指定してください。',
            'genres.*.exists' => '選択されたジャンルは存在しません。',
            'genres.*.distinct' => '同じジャンルを重複して選択することはできません。',
        ];
    }
}

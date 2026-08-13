<?php

namespace App\Http\Requests\Api\V1;

class IndexBookRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('keyword')) {
            $this->merge([
                'keyword' => trim((string) $this->input('keyword')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string'],
            'genre_id' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'between:1,50'],
        ];
    }

    public function messages(): array
    {
        return [
            'keyword.string' => 'キーワードは文字列で指定してください。',
            'genre_id.integer' => 'ジャンルIDは整数で指定してください。',
            'page.integer' => 'ページ番号は1以上の整数で指定してください。',
            'page.min' => 'ページ番号は1以上の整数で指定してください。',
            'per_page.integer' => '1ページの件数は1以上50以下で指定してください。',
            'per_page.between' => '1ページの件数は1以上50以下で指定してください。',
        ];
    }
}

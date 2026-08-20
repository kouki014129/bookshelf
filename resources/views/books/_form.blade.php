@php
    $bookGenreIds = isset($book) ? $book->genres->pluck('id')->toArray() : [];
@endphp

@csrf

{{-- 新規登録時のみISBN検索を表示 --}}
@unless(isset($book))
    <div class="mb-6 rounded-lg border border-indigo-100 bg-indigo-50 px-5 py-4">
        <div class="mb-3">
            <h3 class="text-sm font-semibold text-gray-800">
                ISBNから書籍情報を自動入力
            </h3>

            <p class="mt-1 whitespace-nowrap text-xs text-gray-600">
                13桁のISBNを入力すると、Google Books APIから書籍情報を取得してフォームを自動補完します。
            </p>
        </div>

        <div class="flex items-center gap-2">
            <input
                type="text"
                id="isbn_search"
                inputmode="numeric"
                maxlength="13"
                class="min-w-0 flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="例: 9784101010014"
            >

            <button
                type="button"
                id="isbn_search_button"
                class="shrink-0 rounded bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                検索
            </button>
        </div>

        <p
            id="isbn_search_message"
            class="mt-2 hidden text-sm"
        ></p>
    </div>
@endunless

<div class="space-y-6">
    <div>
        <label for="title" class="mb-1 block text-sm font-medium text-gray-700">
            タイトル <span class="text-red-500">*</span>
        </label>

        <input
            type="text"
            name="title"
            id="title"
            value="{{ old('title', $book->title ?? '') }}"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="書籍のタイトルを入力"
        >

        @error('title')
            <p class="mt-1 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label for="author" class="mb-1 block text-sm font-medium text-gray-700">
            著者 <span class="text-red-500">*</span>
        </label>

        <input
            type="text"
            name="author"
            id="author"
            value="{{ old('author', $book->author ?? '') }}"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="著者名を入力"
        >

        @error('author')
            <p class="mt-1 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label for="isbn" class="mb-1 block text-sm font-medium text-gray-700">
            ISBN-13 <span class="text-red-500">*</span>
        </label>

        <input
            type="text"
            name="isbn"
            id="isbn"
            value="{{ old('isbn', $book->isbn ?? '') }}"
            inputmode="numeric"
            maxlength="13"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="9784000000000"
        >

        <p class="mt-1 text-xs text-gray-500">
            13桁のISBNコードを入力してください
        </p>

        @error('isbn')
            <p class="mt-1 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label for="published_date" class="mb-1 block text-sm font-medium text-gray-700">
            出版日 <span class="text-red-500">*</span>
        </label>

        <input
            type="date"
            name="published_date"
            id="published_date"
            value="{{ old('published_date', $book->published_date ?? '') }}"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >

        @error('published_date')
            <p class="mt-1 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label for="description" class="mb-1 block text-sm font-medium text-gray-700">
            説明
        </label>

        <textarea
            name="description"
            id="description"
            rows="4"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="書籍の説明を入力（任意）"
        >{{ old('description', $book->description ?? '') }}</textarea>

        @error('description')
            <p class="mt-1 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label for="image_url" class="mb-1 block text-sm font-medium text-gray-700">
            画像URL
        </label>

        <input
            type="text"
            name="image_url"
            id="image_url"
            value="{{ old('image_url', $book->image_url ?? '') }}"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="https://example.com/image.jpg"
        >

        <p class="mt-1 text-xs text-gray-500">
            書籍の表紙画像のURLを入力してください（任意）
        </p>

        @error('image_url')
            <p class="mt-1 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">
            ジャンル <span class="text-red-500">*</span>
        </label>

        <div class="rounded-md bg-gray-50 p-4">
            @if($genres->isEmpty())
                <p class="text-sm text-gray-500">
                    ジャンルが登録されていません。先にジャンルを登録してください。
                </p>
            @else
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                    @foreach($genres as $genre)
                        <label class="inline-flex cursor-pointer items-center rounded p-2 hover:bg-gray-100">
                            <input
                                type="checkbox"
                                name="genres[]"
                                value="{{ $genre->id }}"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                @if(in_array($genre->id, old('genres', $bookGenreIds))) checked @endif
                            >

                            <span class="ml-2 text-sm text-gray-700">
                                {{ $genre->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        @error('genres')
            <p class="mt-1 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror

        @error('genres.*')
            <p class="mt-1 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>
</div>

@unless(isset($book))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('isbn_search');
            const searchButton = document.getElementById('isbn_search_button');
            const message = document.getElementById('isbn_search_message');

            const titleInput = document.getElementById('title');
            const authorInput = document.getElementById('author');
            const isbnInput = document.getElementById('isbn');
            const publishedDateInput = document.getElementById('published_date');
            const descriptionInput = document.getElementById('description');
            const imageUrlInput = document.getElementById('image_url');

            searchButton.addEventListener('click', async function () {
                const isbn = searchInput.value.trim();

                message.classList.add('hidden');

                if (!/^\d{13}$/.test(isbn)) {
                    message.textContent = '13桁のISBNを入力してください。';
                    message.className = 'mt-2 text-sm text-red-600';

                    return;
                }

                searchButton.disabled = true;
                searchButton.textContent = '検索中...';

                try {
                    const response = await fetch(
                        `/books/isbn/${encodeURIComponent(isbn)}`,
                        {
                            headers: {
                                'Accept': 'application/json',
                            },
                        }
                    );

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            data.message ?? '書籍情報を取得できませんでした。'
                        );
                    }

                    titleInput.value = data.title ?? '';
                    authorInput.value = data.author ?? '';
                    isbnInput.value = isbn;
                    descriptionInput.value = data.description ?? '';
                    imageUrlInput.value = data.image_url ?? '';

                    if (
                        data.published_date
                        && /^\d{4}-\d{2}-\d{2}$/.test(data.published_date)
                    ) {
                        publishedDateInput.value = data.published_date;
                    } else {
                        publishedDateInput.value = '';
                    }

                    message.textContent = '書籍情報を取得しました。';
                    message.className = 'mt-2 text-sm text-green-600';
                } catch (error) {
                    message.textContent = error.message;
                    message.className = 'mt-2 text-sm text-red-600';
                } finally {
                    searchButton.disabled = false;
                    searchButton.textContent = '検索';
                }
            });
        });
    </script>
@endunless
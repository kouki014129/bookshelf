<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('新規読書計画作成') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <form
                        action="{{ route('reading-plans.store') }}"
                        method="POST"
                        novalidate
                    >
                        @csrf

                        <div class="space-y-6">

                            <div>
                                <label
                                    for="book_id"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    書籍
                                    <span class="text-red-500">*</span>
                                </label>

                                <select
                                    name="book_id"
                                    id="book_id"
                                    class="block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">
                                        -- 書籍を選択 --
                                    </option>

                                    @foreach ($books as $book)
                                        <option
                                            value="{{ $book->id }}"
                                            @selected(old('book_id') == $book->id)
                                        >
                                            {{ $book->title }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('book_id')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="deadline"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    期日
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="date"
                                    name="deadline"
                                    id="deadline"
                                    value="{{ old('deadline') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm"
                                >

                                @error('deadline')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>

                        <div class="mt-8 flex items-center justify-end gap-4">
                            <a
                                href="{{ route('reading-plans.index') }}"
                                class="text-gray-600 hover:text-gray-900"
                            >
                                キャンセル
                            </a>

                            <button
                                type="submit"
                                class="rounded bg-blue-500 px-5 py-2 font-bold text-white hover:bg-blue-700"
                            >
                                登録
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('読書計画編集') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <form
                        action="{{ route('reading-plans.update', $readingPlan) }}"
                        method="POST"
                        novalidate
                    >
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <p class="text-sm text-gray-600">
                                対象書籍:
                                <span class="font-semibold text-gray-800">
                                    {{ $readingPlan->book->title }}
                                </span>
                            </p>

                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-sm text-gray-600">
                                    現在の状態:
                                </span>

                                @if ($readingPlan->status === 'planning')
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700">
                                        計画中
                                    </span>
                                @elseif($readingPlan->status === 'reading')
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs text-blue-700">
                                        進行中
                                    </span>
                                @elseif($readingPlan->status === 'completed')
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs text-green-700">
                                        読了
                                    </span>
                                @endif
                            </div>
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
                                value="{{ old('deadline', optional($readingPlan->deadline)->format('Y-m-d')) }}"
                                class="block w-full rounded-md border-gray-300 shadow-sm"
                            >

                            @error('deadline')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
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
                                更新
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>

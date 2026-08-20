<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('読書計画') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-5 flex items-center justify-between">
                <form
                    method="GET"
                    action="{{ route('reading-plans.index') }}"
                    class="flex items-center gap-3"
                >
                    <label
                        for="status"
                        class="text-sm text-gray-600"
                    >
                        状態:
                    </label>

                    <select
                        name="status"
                        id="status"
                        onchange="this.form.submit()"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">
                            すべて
                        </option>

                        <option
                            value="planning"
                            @selected($status === 'planning')
                        >
                            計画中
                        </option>

                        <option
                            value="completed"
                            @selected($status === 'completed')
                        >
                            読了
                        </option>

                        <option
                            value="expired"
                            @selected($status === 'expired')
                        >
                            期限切れ
                        </option>
                    </select>
                </form>

                <a
                    href="{{ route('reading-plans.create') }}"
                    class="rounded-md bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700"
                >
                    新規計画作成
                </a>
            </div>

            @if ($readingPlans->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-sm text-gray-500">
                        該当する読書計画はありません。
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($readingPlans as $readingPlan)
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between gap-6">

                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg font-bold text-gray-800">
                                            {{ $readingPlan->book?->title ?? '書籍情報なし' }}
                                        </h3>

                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ $readingPlan->book?->author ?? '' }}
                                        </p>

                                        <div class="mt-4 flex items-center gap-8">
                                            <div>
                                                <p class="text-xs text-gray-500">
                                                    期日
                                                </p>

                                                <p class="mt-1 font-medium text-gray-800">
                                                    {{ optional($readingPlan->deadline)->format('Y/m/d') ?? '-' }}
                                                </p>
                                            </div>

                                            <div>
                                                <p class="text-xs text-gray-500">
                                                    状態
                                                </p>

                                                <div class="mt-1">
                                                    @if ($readingPlan->status === \App\Enums\ReadingPlanStatus::Planning)
                                                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                                            計画中
                                                        </span>
                                                    @elseif($readingPlan->status === \App\Enums\ReadingPlanStatus::Completed)
                                                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                                            読了
                                                        </span>
                                                    @elseif($readingPlan->status === \App\Enums\ReadingPlanStatus::Expired)
                                                        <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                                                            期限切れ
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-3">
                                        @if ($readingPlan->status === \App\Enums\ReadingPlanStatus::Planning)
                                            <form
                                                action="{{ route('reading-plans.complete', $readingPlan) }}"
                                                method="POST"
                                            >
                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="rounded-md border border-green-300 px-4 py-2 text-sm font-medium text-green-600 hover:bg-green-50"
                                                >
                                                    読了する
                                                </button>
                                            </form>

                                            <a
                                                href="{{ route('reading-plans.edit', $readingPlan) }}"
                                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                            >
                                                編集
                                            </a>
                                        @endif

                                        <form
                                            action="{{ route('reading-plans.destroy', $readingPlan) }}"
                                            method="POST"
                                            onsubmit="return confirm('この読書計画を削除しますか？');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                                            >
                                                削除
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
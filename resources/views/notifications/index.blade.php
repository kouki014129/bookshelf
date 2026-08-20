<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            通知一覧
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($notifications->isEmpty())
                    <div class="min-h-[200px] flex flex-col items-center justify-center text-gray-400">
                        <svg
                            class="w-10 h-10 mb-4"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a3 3 0 1 1-5.714 0"
                            />
                        </svg>

                        <p class="text-sm">通知はありません。</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach ($notifications as $notification)
                            <div class="flex items-start justify-between gap-4 px-6 py-5 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }}">
                                <a
                                    href="{{ $notification->data['url'] ?? route('reading-plans.index') }}"
                                    class="min-w-0 flex-1 hover:opacity-80"
                                >
                                    <div class="flex items-center gap-2">
                                        @unless ($notification->read_at)
                                            <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">
                                                未読
                                            </span>
                                        @endunless

                                        <p class="font-semibold text-gray-800">
                                            {{ $notification->data['message'] }}
                                        </p>
                                    </div>

                                    <p class="mt-1 text-sm text-gray-500">
                                        期限日：{{ $notification->data['deadline'] }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-400">
                                        {{ $notification->created_at->format('Y/m/d H:i') }}
                                    </p>
                                </a>

                                @unless ($notification->read_at)
                                    <form
                                        action="{{ route('notifications.read', $notification) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="rounded-md border border-blue-300 px-3 py-1.5 text-sm font-medium text-blue-600 hover:bg-blue-50"
                                        >
                                            既読にする
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            通知一覧
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
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
                            <a
                                href="{{ $notification->data['url'] ?? route('reading-plans.index') }}"
                                class="block px-6 py-5 hover:bg-gray-50"
                            >
                                <p class="font-semibold text-gray-800">
                                    {{ $notification->data['message'] }}
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    期限日：{{ $notification->data['deadline'] }}
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    {{ $notification->created_at->format('Y/m/d H:i') }}
                                </p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
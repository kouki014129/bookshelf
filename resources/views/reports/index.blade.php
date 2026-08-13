<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('マイ読書レポート') }}
        </h2>
    </x-slot>

    <style>
        .report-page {
            background: #f3f6f8;
            padding: 44px 24px 48px;
        }

        .report-container {
            width: 100%;
            max-width: 1140px;
            margin: 0 auto;
        }

        .report-panel {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .report-heading {
            margin: 0;
            color: #1f2937;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.4;
        }

        /* =========================
           基本統計
        ========================= */

        .summary-panel {
            padding: 24px 22px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .summary-card {
            height: 108px;
            border: 1px solid #e1e5e9;
            border-radius: 7px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #ffffff;
        }

        .summary-number {
            margin: 0;
            font-size: 36px;
            font-weight: 700;
            line-height: 1;
        }

        .summary-number.blue {
            color: #2f80ed;
        }

        .summary-number.green {
            color: #20b15a;
        }

        .summary-number.yellow {
            color: #f2b500;
        }

        .summary-label {
            margin-top: 13px;
            color: #7c838d;
            font-size: 14px;
            line-height: 1;
        }

        /* =========================
           中央2カラム
        ========================= */

        .middle-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
            margin-top: 22px;
        }

        .middle-panel {
            min-height: 304px;
            padding: 24px 22px;
        }

        /* =========================
           評価分布
        ========================= */

        .rating-list {
            margin-top: 24px;
            display: flex;
            flex-direction: column;
            gap: 13px;
        }

        .rating-row {
            display: grid;
            grid-template-columns: 62px minmax(0, 1fr) 38px;
            gap: 10px;
            align-items: center;
        }

        .rating-stars {
            color: #f2b500;
            font-size: 16px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
        }

        .rating-track {
            height: 18px;
            overflow: hidden;
            background: #e3e6e9;
            border-radius: 999px;
        }

        .rating-fill {
            height: 100%;
            background: #ffc800;
            border-radius: 999px;
        }

        .rating-count {
            color: #68717c;
            font-size: 13px;
            text-align: right;
            white-space: nowrap;
        }

        /* =========================
           高評価書籍
        ========================= */

        .top-books {
            margin-top: 17px;
            display: flex;
            flex-direction: column;
            gap: 11px;
        }

        .top-book-card {
            height: 66px;
            padding: 0 13px;
            border: 1px solid #e1e5e9;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .top-book-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .rank-circle {
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
        }

        .rank-1 {
            background: #ffc800;
        }

        .rank-2 {
            background: #9ca3af;
        }

        .rank-3 {
            background: #ec7600;
        }

        .rank-other {
            color: #56606b;
            background: #e5e8eb;
        }

        .book-info {
            min-width: 0;
        }

        .book-title {
            overflow: hidden;
            margin: 0;
            color: #2b333d;
            font-size: 15px;
            font-weight: 500;
            line-height: 1.35;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .book-author {
            overflow: hidden;
            margin: 3px 0 0;
            color: #7a828c;
            font-size: 12px;
            line-height: 1.25;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .book-stars {
            flex-shrink: 0;
            color: #f2b500;
            font-size: 15px;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        /* =========================
           ジャンル
        ========================= */

        .genre-panel {
            margin-top: 22px;
            padding: 22px;
        }

        .genre-description {
            margin: 4px 0 17px;
            color: #808791;
            font-size: 12px;
        }

        .genre-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px 16px;
        }

        .genre-card {
            height: 72px;
            padding: 0 14px;
            border: 1px solid #e1e5e9;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .genre-left {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
        }

        .genre-name {
            overflow: hidden;
            margin: 0;
            color: #303842;
            font-size: 15px;
            font-weight: 500;
            line-height: 1.25;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .genre-count {
            margin: 3px 0 0;
            color: #7b838d;
            font-size: 12px;
            line-height: 1.2;
        }

        .genre-rating {
            flex-shrink: 0;
            text-align: right;
        }

        .genre-rating-number {
            margin: 0;
            color: #f2b500;
            font-size: 18px;
            font-weight: 700;
            line-height: 1;
        }

        .genre-rating-label {
            margin: 5px 0 0;
            color: #a0a6ad;
            font-size: 10px;
            line-height: 1;
        }

        @media (max-width: 900px) {
            .middle-grid {
                grid-template-columns: 1fr;
            }

            .genre-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .report-page {
                padding: 24px 16px 32px;
            }

            .summary-grid,
            .genre-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="report-page">
        <div class="report-container">

            {{-- 基本統計 --}}
            <section class="report-panel summary-panel">
                <h3 class="report-heading">
                    基本統計
                </h3>

                <div class="summary-grid">
                    <div class="summary-card">
                        <p class="summary-number blue">
                            {{ $totalReviews }}
                        </p>

                        <p class="summary-label">
                            総レビュー数
                        </p>
                    </div>

                    <div class="summary-card">
                        <p class="summary-number green">
                            {{ $completedBooks }}
                        </p>

                        <p class="summary-label">
                            読了冊数
                        </p>
                    </div>

                    <div class="summary-card">
                        <p class="summary-number yellow">
                            {{ number_format($averageRating, 1) }}
                        </p>

                        <p class="summary-label">
                            平均評価
                        </p>
                    </div>
                </div>
            </section>

            {{-- 評価分布 / 高評価書籍 --}}
            <div class="middle-grid">

                {{-- 評価分布 --}}
                <section class="report-panel middle-panel">
                    <h3 class="report-heading">
                        評価分布
                    </h3>

                    <div class="rating-list">
                        @foreach($ratingDistribution as $star => $count)
                            @php
                                $percentage = $totalReviews > 0
                                    ? ($count / $totalReviews) * 100
                                    : 0;
                            @endphp

                            <div class="rating-row">
                                <div class="rating-stars">
                                    {{ str_repeat('★', $star) }}
                                </div>

                                <div class="rating-track">
                                    <div
                                        class="rating-fill"
                                        style="width: {{ $percentage }}%;"
                                    ></div>
                                </div>

                                <div class="rating-count">
                                    {{ $count }}件
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- 高評価書籍 --}}
                <section class="report-panel middle-panel">
                    <h3 class="report-heading">
                        高評価書籍 TOP5
                    </h3>

                    @if($topRatedBooks->isEmpty())
                        <p class="text-sm text-gray-500 mt-4">
                            レビューした書籍がありません。
                        </p>
                    @else
                        <div class="top-books">
                            @foreach($topRatedBooks as $review)
                                @php
                                    $rankClass = match($loop->iteration) {
                                        1 => 'rank-1',
                                        2 => 'rank-2',
                                        3 => 'rank-3',
                                        default => 'rank-other',
                                    };
                                @endphp

                                <div class="top-book-card">
                                    <div class="top-book-left">
                                        <div class="rank-circle {{ $rankClass }}">
                                            {{ $loop->iteration }}
                                        </div>

                                        <div class="book-info">
                                            <p class="book-title">
                                                {{ $review->book->title }}
                                            </p>

                                            <p class="book-author">
                                                {{ $review->book->author }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="book-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                ★
                                            @else
                                                ☆
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

            </div>

            {{-- ジャンル別評価傾向 --}}
            <section class="report-panel genre-panel">
                <h3 class="report-heading">
                    ジャンル別評価傾向 TOP5
                </h3>

                <p class="genre-description">
                    どのジャンルを高く評価する傾向があるかを表示
                </p>

                @if($genreStatistics->isEmpty())
                    <p class="text-sm text-gray-500">
                        ジャンル別の評価データがありません。
                    </p>
                @else
                    <div class="genre-grid">

                        @foreach($genreStatistics as $genre)
                            @php
                                $rankClass = match($loop->iteration) {
                                    1 => 'rank-1',
                                    2 => 'rank-2',
                                    3 => 'rank-3',
                                    default => 'rank-other',
                                };
                            @endphp

                            <div class="genre-card">
                                <div class="genre-left">

                                    <div class="rank-circle {{ $rankClass }}">
                                        {{ $loop->iteration }}
                                    </div>

                                    <div style="min-width: 0;">
                                        <p class="genre-name">
                                            {{ $genre->name }}
                                        </p>

                                        <p class="genre-count">
                                            {{ $genre->reviews_count }}件のレビュー
                                        </p>
                                    </div>
                                </div>

                                <div class="genre-rating">
                                    <p class="genre-rating-number">
                                        {{ number_format($genre->average_rating, 1) }}
                                    </p>

                                    <p class="genre-rating-label">
                                        平均評価
                                    </p>
                                </div>
                            </div>
                        @endforeach

                    </div>
                @endif
            </section>

        </div>
    </div>
</x-app-layout>
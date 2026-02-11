@props(['manual'])

@php
    $currentLang = app()->getLocale();
    $title = $manual->getTranslation('name', $currentLang);
    $description = $manual->getTranslation('description', $currentLang) ?? '暫無描述';
    $createdDate = $manual->created_at->format('Y-m-d');
    $updatedDate = $manual->updated_at->format('Y-m-d');
    $manualUrl = route('frontend.manual', ['slug' => $manual->url_slug]);
@endphp

<div class="col-md-6 col-lg-4 manual-card-wrapper">
    <a
        href="{{ $manualUrl }}"
        class="card h-100 border-0 bg-light-card p-4 cursor-pointer hover-shadow text-decoration-none text-dark"
        role="article"
        data-manual-id="{{ $manual->id }}"
        aria-label="查看 {{ $title }} 文檔"
    >
        <!-- 手冊圖標 -->
        <div class="fs-3 mb-3" aria-hidden="true">📚</div>

        <!-- 手冊標題 -->
        <h2 class="card-title fw-bold mb-2">
            {{ $title }}
        </h2>

        <!-- 手冊描述 -->
        <p class="card-text text-muted small mb-3">
            {{ $description }}
        </p>

        <!-- 元數據 (建立日期和更新日期) -->
        <div class="small text-secondary">
            <div class="mb-2">
                <strong>建立:</strong>
                <time datetime="{{ $manual->created_at->toIso8601String() }}">
                    {{ $createdDate }}
                </time>
            </div>
            <div>
                <strong>更新:</strong>
                <time datetime="{{ $manual->updated_at->toIso8601String() }}">
                    {{ $updatedDate }}
                </time>
            </div>
        </div>
    </a>
</div>

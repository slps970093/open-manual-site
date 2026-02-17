<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $manual->getTranslation('name', app()->getLocale()) }} - Docs</title>
    @vite(['resources/css/manuals.css', 'resources/js/manual.js'])
</head>
<body>
<!-- 文檔詳情頁 -->
<div class="d-flex" style="min-height: 100vh;">
    <!-- 側邊欄 -->
    <nav class="sidebar bg-light-gray d-none d-md-block" id="desktopSidebar" style="width: 280px; overflow-y: auto;">
        <div class="p-3 border-bottom" style="background-color: var(--primary-color);">
            <h5 class="fw-bold text-white mb-0">{{ $manual->getTranslation('name', app()->getLocale()) }}</h5>
        </div>
        <div class="p-3">
            <input type="text" class="form-control form-control-sm" id="menuSearchInput" placeholder="搜尋...">
        </div>
        <nav class="menu-tree nav flex-column" id="menuTree" role="tree">
            @forelse($menus as $menu)
                @include('frontend.partials.menu-item', ['item' => $menu, 'level' => 0, 'currentLang' => $currentLang, 'currentPageId' => $currentPageId, 'manualSlug' => $manual->url_slug])
            @empty
                <div class="text-center text-muted py-3">
                    <p class="mb-0">暫無菜單項</p>
                </div>
            @endforelse
        </nav>
    </nav>

    <!-- 主內容區 -->
    <div class="flex-grow-1 d-flex flex-column w-100" style="min-width: 0;">
        <!-- 頂部導航 -->
        <div class="bg-light-gray border-bottom p-3 sticky-top d-none d-md-block">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <!-- Breadcrumb Navigation -->
                <div class="flex-grow-1">
                    <x-breadcrumb :breadcrumbs="$breadcrumbs" :manualSlug="$manual->url_slug" :simplified="false" />
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('frontend.manuals') }}" class="btn btn-sm btn-light">← 清單</a>
                    <x-language-selector id="languageSelector" size="sm" />
                </div>
            </div>
        </div>

        <!-- 內容區 -->
        <div class="flex-grow-1 overflow-y-auto p-4 p-md-5 bg-light" id="contentArea">
            <div class="manual-empty-state" id="emptyState">
                <div class="manual-empty-state-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3 class="manual-empty-state-title">選擇一個頁面開始</h3>
                <p class="mb-0">從左側菜單中選擇一個頁面來查看其內容</p>
            </div>
        </div>
    </div>
</div>

<!-- 手機版頂部導航 -->
<div class="d-md-none position-fixed top-0 start-0 w-100 border-bottom z-3 bg-light-gray mobile-header">
    <div class="d-flex justify-content-between align-items-center p-2 gap-2">
        <button class="btn btn-sm" id="mobileMenuBtn" style="background: none; border: none; font-size: 1.5rem; z-index: 1001; color: var(--text-dark); padding: 0; flex-shrink: 0;">☰</button>
        <span class="fw-bold text-center flex-grow-1" style="font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $manual->getTranslation('name', app()->getLocale()) }}</span>
        <a href="{{ route('frontend.manuals') }}" class="btn btn-sm btn-light" style="white-space: nowrap; flex-shrink: 0;">← 清單</a>
    </div>
    <div class="px-2 pb-2">
        <x-language-selector id="mobileLanguageSelector" size="sm" />
    </div>
</div>

<!-- 調整內容區頂部邊距以適應手機版頭部 -->
<style>
    @media (max-width: 767.98px) {
        #contentArea {
            margin-top: 5.5rem;
        }
    }
</style>

<!-- 手機版側邊欄 -->
<nav class="sidebar bg-light-gray d-md-none position-fixed start-0 top-0 h-100" id="mobileSidebar" style="width: 280px; overflow-y: auto; z-index: 999; transform: translateX(-100%); transition: transform 0.3s ease;">
    <div class="p-3 border-bottom" style="background-color: var(--primary-color); margin-top: 3.5rem;">
        <h5 class="fw-bold text-white mb-0">{{ $manual->getTranslation('name', app()->getLocale()) }}</h5>
    </div>
    <div class="p-3">
        <input type="text" class="form-control form-control-sm" id="mobileMenuSearchInput" placeholder="搜尋...">
    </div>
    <nav class="menu-tree nav flex-column" id="mobileMenuTree" role="tree">
        @forelse($menus as $menu)
            @include('frontend.partials.menu-item', ['item' => $menu, 'level' => 0, 'currentLang' => $currentLang, 'currentPageId' => $currentPageId, 'manualSlug' => $manual->url_slug])
        @empty
            <div class="text-center text-muted py-3">
                <p class="mb-0">暫無菜單項</p>
            </div>
        @endforelse
    </nav>
</nav>

</body>
</html>

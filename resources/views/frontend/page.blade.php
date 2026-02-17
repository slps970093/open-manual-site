<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageInfo->getTranslation('title', app()->getLocale()) }} - {{ $manual->getTranslation('name', app()->getLocale()) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
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
                @include('frontend.partials.menu-item', ['item' => $menu, 'level' => 0, 'currentLang' => $currentLang, 'currentPageId' => $id, 'manualSlug' => $manual->url_slug])
            @empty
                <div class="text-center text-muted py-3">
                    <p class="mb-0">暫無菜單項</p>
                </div>
            @endforelse
        </nav>
    </nav>

    <!-- 主內容區 -->
    <div class="flex-grow-1 d-flex flex-column w-100">
        <!-- 頂部導航 -->
        <div class="bg-light-gray border-bottom p-3 sticky-top d-none d-md-block">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <!-- Breadcrumb Navigation -->
                <div class="flex-grow-1">
                    <x-breadcrumb :breadcrumbs="$breadcrumbs" :manualSlug="$manual->url_slug" :simplified="false" />
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('frontend.manual', ['slug' => $manual->url_slug]) }}" class="btn btn-sm btn-light">← 清單</a>
                    <x-language-selector id="languageSelector" size="sm" />
                </div>
            </div>
        </div>

        <!-- 內容區 -->
        <div class="flex-grow-1 overflow-y-auto p-4 p-md-5 bg-light" id="contentArea">
            <article class="manual-page-content">
                <!-- Page Title -->
                <h1 class="mb-3">{{ $pageInfo->getTranslation('title', $currentLang) }}</h1>

                <!-- Page Metadata -->
                <div class="page-metadata mb-4 pb-3 border-bottom" style="font-size: 0.9rem; color: var(--text-light);">
                    @if($processedContent && $processedContent->created_at)
                        <span class="me-3">
                            <i class="fas fa-calendar-alt"></i>
                            建立: {{ $processedContent->created_at->format('Y-m-d') }}
                        </span>
                    @endif
                    @if($processedContent && $processedContent->updated_at && $processedContent->updated_at !== $processedContent->created_at)
                        <span>
                            <i class="fas fa-sync-alt"></i>
                            更新: {{ $processedContent->updated_at->format('Y-m-d') }}
                        </span>
                    @endif
                </div>

                <!-- Page Content -->
                <div class="page-body" style="line-height: 1.8; color: var(--text-dark);">
                    {!! $processedContent->content !!}
                </div>
            </article>
        </div>
    </div>
</div>

<!-- 手機版頂部導航 -->
<div class="d-md-none position-fixed top-0 start-0 w-100 border-bottom p-3 z-3 bg-light-gray">
    <div class="d-flex justify-content-between align-items-center gap-2">
        <button class="btn btn-sm" id="mobileMenuBtn" style="background: none; border: none; font-size: 1.5rem; z-index: 1001; color: var(--text-dark); padding: 0;">☰</button>
        <span class="fw-bold flex-grow-1 text-center" style="font-size: 0.9rem;">{{ $manual->getTranslation('name', app()->getLocale()) }}</span>
        <x-language-selector id="mobileLanguageSelector" size="sm" />
    </div>
</div>

<!-- 手機版麵包屑導航 -->
<div class="d-md-none position-fixed top-0 start-0 w-100 border-bottom p-2 z-2 bg-light-gray" style="margin-top: 3.5rem;">
    <x-breadcrumb :breadcrumbs="$breadcrumbs" :manualSlug="$manual->url_slug" :simplified="true" />
</div>

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
            @include('frontend.partials.menu-item', ['item' => $menu, 'level' => 0, 'currentLang' => $currentLang, 'currentPageId' => $id, 'manualSlug' => $manual->url_slug])
        @empty
            <div class="text-center text-muted py-3">
                <p class="mb-0">暫無菜單項</p>
            </div>
        @endforelse
    </nav>
</nav>

<style>
    .menu-tree {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .menu-tree-item {
        margin: 0;
    }

    .menu-tree-item-content {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        cursor: pointer;
        user-select: none;
        transition: background-color 0.2s;
        color: var(--text-dark);
        border-bottom: 1px solid var(--border-color);
        text-decoration: none;
    }

    .menu-tree-item-content:hover {
        background-color: var(--bg-light-card);
    }

    .menu-tree-item-content.active {
        background-color: rgba(37, 99, 235, 0.1);
        color: var(--primary-color);
        font-weight: 600;
    }

    .menu-tree-toggle {
        width: 1.5rem;
        height: 1.5rem;
        padding: 0;
        margin-right: 0.5rem;
        border: none;
        background: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-light);
        transition: transform 0.2s;
        flex-shrink: 0;
    }

    .menu-tree-toggle.expanded {
        transform: rotate(90deg);
    }

    .menu-tree-toggle:hover {
        color: var(--text-dark);
    }

    .menu-tree-toggle:disabled {
        display: none;
    }

    .menu-tree-item-label {
        flex: 1;
        font-size: 0.95rem;
    }

    .menu-tree-item-icon {
        margin-left: 0.5rem;
        font-size: 0.75rem;
        color: var(--text-light);
    }

    .menu-tree-children {
        list-style: none;
        padding: 0;
        margin: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .menu-tree-children.expanded {
        max-height: 10000px;
    }

    .menu-tree-children .menu-tree-item-content {
        padding-left: 2.5rem;
        font-size: 0.9rem;
    }

    .menu-tree-children .menu-tree-children .menu-tree-item-content {
        padding-left: 4rem;
    }

    .manual-page-content {
        max-width: 900px;
    }

    .page-metadata {
        font-size: 0.9rem;
        color: var(--text-light);
    }

    .page-body {
        line-height: 1.8;
        color: var(--text-dark);
    }

    .page-body h2 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .page-body h3 {
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .page-body p {
        margin-bottom: 1rem;
    }

    .page-body code {
        background-color: var(--bg-light-card);
        padding: 0.2rem 0.4rem;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
        color: var(--primary-color);
    }

    .page-body pre {
        background-color: var(--bg-light-card);
        padding: 1rem;
        border-radius: 5px;
        overflow-x: auto;
        margin-bottom: 1rem;
    }

    .page-body pre code {
        background-color: transparent;
        padding: 0;
        color: var(--text-dark);
    }

    .page-body table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }

    .page-body table th,
    .page-body table td {
        border: 1px solid var(--border-color);
        padding: 0.75rem;
        text-align: left;
    }

    .page-body table th {
        background-color: var(--bg-light-card);
        font-weight: 600;
    }

    .page-body img {
        max-width: 100%;
        height: auto;
        border-radius: 5px;
        margin: 1rem 0;
    }

    @media (max-width: 767.98px) {
        #contentArea {
            margin-top: 6.5rem;
        }

        .manual-page-content {
            max-width: 100%;
        }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const menuSearchInput = document.getElementById('menuSearchInput');
    const mobileMenuSearchInput = document.getElementById('mobileMenuSearchInput');
    const menuTree = document.getElementById('menuTree');
    const mobileMenuTree = document.getElementById('mobileMenuTree');

    // Auto-open sidebar on mobile for detail pages
    const isMobile = window.innerWidth < 768;
    if (isMobile) {
        mobileSidebar.style.transform = 'translateX(0px)';
    }

    // Mobile menu toggle
    mobileMenuBtn.addEventListener('click', function() {
        const isOpen = mobileSidebar.style.transform === 'translateX(0px)';
        mobileSidebar.style.transform = isOpen ? 'translateX(-100%)' : 'translateX(0px)';
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        const isOpen = mobileSidebar.style.transform === 'translateX(0px)';
        if (isOpen && !mobileSidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
            mobileSidebar.style.transform = 'translateX(-100%)';
        }
    });

    // Menu search functionality
    function setupSearch(searchInput, menuTreeElement) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterMenuItems(searchTerm, menuTreeElement);
        });
    }

    function filterMenuItems(searchTerm, menuTreeElement) {
        const items = menuTreeElement.querySelectorAll('.menu-tree-item');
        let visibleCount = 0;

        items.forEach(item => {
            const label = item.querySelector('.menu-tree-item-label');
            const text = label ? label.textContent.toLowerCase() : '';
            const isMatch = text.includes(searchTerm);

            if (searchTerm === '') {
                item.style.display = '';
            } else if (isMatch) {
                item.style.display = '';
                visibleCount++;
                expandAncestors(item, menuTreeElement);
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCount === 0 && searchTerm.length > 0) {
            menuTreeElement.style.display = 'none';
        } else {
            menuTreeElement.style.display = '';
        }
    }

    function expandAncestors(item, menuTreeElement) {
        let parent = item.parentElement;
        while (parent && parent !== menuTreeElement) {
            if (parent.classList.contains('menu-tree-children')) {
                parent.classList.add('expanded');
                const toggle = parent.previousElementSibling.querySelector('.menu-tree-toggle');
                if (toggle) {
                    toggle.classList.add('expanded');
                }
            }
            parent = parent.parentElement;
        }
    }

    // Setup menu item click handlers
    function setupMenuItems(menuTreeElement) {
        menuTreeElement.querySelectorAll('.menu-tree-item-content').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const toggle = this.querySelector('.menu-tree-toggle');
                const children = this.nextElementSibling;
                const menuItem = this.closest('.menu-tree-item');
                const itemId = menuItem ? menuItem.dataset.itemId : null;

                // If it's a toggle button, expand/collapse
                if (toggle && !toggle.disabled) {
                    const isExpanded = toggle.classList.contains('expanded');
                    toggle.classList.toggle('expanded');
                    if (children && children.classList.contains('menu-tree-children')) {
                        children.classList.toggle('expanded');
                    }
                    // Save state to localStorage
                    if (itemId) {
                        saveExpandState(itemId, !isExpanded);
                    }
                    // Update ARIA attribute
                    if (menuItem) {
                        menuItem.setAttribute('aria-expanded', !isExpanded);
                    }
                }

                // If it has a link, navigate
                const link = this.dataset.link;
                if (link) {
                    window.location.href = link;
                }
            });
        });

        menuTreeElement.querySelectorAll('.menu-tree-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const isExpanded = this.classList.contains('expanded');
                this.classList.toggle('expanded');
                const children = this.closest('.menu-tree-item-content').nextElementSibling;
                if (children && children.classList.contains('menu-tree-children')) {
                    children.classList.toggle('expanded');
                }
                // Save state to localStorage
                const menuItem = this.closest('.menu-tree-item');
                if (menuItem) {
                    const itemId = menuItem.dataset.itemId;
                    if (itemId) {
                        saveExpandState(itemId, !isExpanded);
                    }
                    // Update ARIA attribute
                    menuItem.setAttribute('aria-expanded', !isExpanded);
                }
            });
        });
    }

    // Save expand/collapse state to localStorage
    function saveExpandState(itemId, isExpanded) {
        const expandedItems = JSON.parse(localStorage.getItem('menuExpandedItems') || '{}');
        if (isExpanded) {
            expandedItems[itemId] = true;
        } else {
            delete expandedItems[itemId];
        }
        localStorage.setItem('menuExpandedItems', JSON.stringify(expandedItems));
    }

    // Restore expand/collapse state from localStorage
    function restoreExpandState(menuTreeElement) {
        const expandedItems = JSON.parse(localStorage.getItem('menuExpandedItems') || '{}');

        menuTreeElement.querySelectorAll('.menu-tree-item').forEach(item => {
            const itemId = item.dataset.itemId;
            if (itemId && expandedItems[itemId]) {
                const toggle = item.querySelector('.menu-tree-toggle');
                const children = item.querySelector('.menu-tree-children');

                if (toggle && !toggle.disabled) {
                    toggle.classList.add('expanded');
                    item.setAttribute('aria-expanded', 'true');
                }
                if (children) {
                    children.classList.add('expanded');
                }
            }
        });
    }

    // Ensure active menu item and its ancestors are expanded
    // This implements Property 5: Active Menu Item Highlighting
    function ensureActiveMenuItemExpanded(menuTreeElement) {
        const activeItem = menuTreeElement.querySelector('.menu-tree-item-content.active');
        if (activeItem) {
            // Expand all ancestors of the active item
            let parent = activeItem.closest('.menu-tree-item').parentElement;
            while (parent && parent !== menuTreeElement) {
                if (parent.classList.contains('menu-tree-children')) {
                    parent.classList.add('expanded');
                    const toggle = parent.previousElementSibling.querySelector('.menu-tree-toggle');
                    if (toggle && !toggle.disabled) {
                        toggle.classList.add('expanded');
                    }
                }
                parent = parent.parentElement;
            }
        }
    }

    // Initialize
    setupSearch(menuSearchInput, menuTree);
    setupSearch(mobileMenuSearchInput, mobileMenuTree);
    restoreExpandState(menuTree);
    restoreExpandState(mobileMenuTree);
    ensureActiveMenuItemExpanded(menuTree);
    ensureActiveMenuItemExpanded(mobileMenuTree);
    setupMenuItems(menuTree);
    setupMenuItems(mobileMenuTree);
});
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>

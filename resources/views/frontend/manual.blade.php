<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $manual->getTranslation('name', app()->getLocale()) }} - Docs</title>
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
                @include('frontend.partials.menu-item', ['item' => $menu, 'level' => 0, 'currentLang' => $currentLang])
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
                <span class="text-muted small" id="breadcrumbNav" style="cursor: pointer;">
                    <a href="javascript:void(0)" class="text-muted text-decoration-none" id="breadcrumbToggle">首頁</a>
                    <span class="mx-2">/</span>
                    <span>{{ $manual->getTranslation('name', app()->getLocale()) }}</span>
                </span>
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
<div class="d-md-none position-fixed top-0 start-0 w-100 border-bottom p-3 z-3 bg-light-gray">
    <div class="d-flex justify-content-between align-items-center gap-2">
        <button class="btn btn-sm" id="mobileMenuBtn" style="background: none; border: none; font-size: 1.5rem; z-index: 1001; color: var(--text-dark); padding: 0;">☰</button>
        <span class="fw-bold flex-grow-1 text-center">{{ $manual->getTranslation('name', app()->getLocale()) }}</span>
        <x-language-selector id="mobileLanguageSelector" size="sm" />
    </div>
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
            @include('frontend.partials.menu-item', ['item' => $menu, 'level' => 0, 'currentLang' => $currentLang])
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

    .manual-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 400px;
        text-align: center;
        color: var(--text-muted);
    }

    .manual-empty-state-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .manual-empty-state-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--text-dark);
    }

    @media (max-width: 767.98px) {
        #contentArea {
            margin-top: 3.5rem;
        }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const breadcrumbToggle = document.getElementById('breadcrumbToggle');
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

    // Breadcrumb toggle - open sidebar (desktop only)
    if (breadcrumbToggle) {
        breadcrumbToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const desktopSidebar = document.getElementById('desktopSidebar');
            if (desktopSidebar) {
                desktopSidebar.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

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

                // If it's a toggle button, expand/collapse
                if (toggle && !toggle.disabled) {
                    toggle.classList.toggle('expanded');
                    if (children && children.classList.contains('menu-tree-children')) {
                        children.classList.toggle('expanded');
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
                this.classList.toggle('expanded');
                const children = this.closest('.menu-tree-item-content').nextElementSibling;
                if (children && children.classList.contains('menu-tree-children')) {
                    children.classList.toggle('expanded');
                }
            });
        });
    }

    // Initialize
    setupSearch(menuSearchInput, menuTree);
    setupSearch(mobileMenuSearchInput, mobileMenuTree);
    setupMenuItems(menuTree);
    setupMenuItems(mobileMenuTree);
});
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

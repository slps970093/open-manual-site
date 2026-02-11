<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>基礎教學 - Docs</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<!-- 文檔詳情頁 -->
<div class="d-flex" style="min-height: 100vh;">
    <!-- 側邊欄 -->
    <nav class="sidebar bg-sidebar d-none d-md-block" style="width: 280px; overflow-y: auto; background-color: #CBD5E1 !important;">
        <div class="p-3 border-bottom" style="background-color: #2563EB;">
            <h5 class="fw-bold text-white mb-0">Docs</h5>
        </div>
        <div class="p-3">
            <input type="text" class="form-control form-control-sm" placeholder="搜尋...">
        </div>
        <div class="nav flex-column">
            <a class="nav-link text-dark p-3 border-bottom" href="#">快速開始</a>
            <div class="ps-4 small text-muted">
                <a class="nav-link text-muted p-2" href="#">• 安裝</a>
                <a class="nav-link text-muted p-2" href="#">• 基本設置</a>
            </div>
            <a class="nav-link text-dark p-3 border-bottom" href="#">API 文檔</a>
            <div class="ps-4 small text-muted">
                <a class="nav-link text-muted p-2" href="#">• 認證</a>
                <a class="nav-link text-muted p-2" href="#">• 端點</a>
            </div>
            <a class="nav-link text-primary p-3 border-bottom bg-light" href="#">教學指南</a>
            <div class="ps-4 small text-muted">
                <a class="nav-link text-muted p-2" href="#">• 基礎教學</a>
                <a class="nav-link text-muted p-2" href="#">• 進階用法</a>
            </div>
        </div>
    </nav>

    <!-- 主內容區 -->
    <div class="flex-grow-1 d-flex flex-column w-100">
        <!-- 頂部導航 -->
        <div class="bg-light-gray border-bottom p-3 sticky-top">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small">教學指南 / 基礎教學</span>
                <div class="d-flex align-items-center gap-2">
                    <a href="list.html" class="btn btn-sm btn-light">← 清單</a>
                    <span class="text-muted small">語言:</span>
                    <select class="form-select form-select-sm" style="width: auto;">
                        <option selected>繁體中文</option>
                        <option>English</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 內容區 -->
        <div class="flex-grow-1 overflow-y-auto p-4 p-md-5">
            <h1 class="display-6 fw-bold mb-2">基礎教學</h1>
            <p class="text-muted fs-5 mb-4">學習如何開始使用我們的平台</p>

            <div class="card border-0 bg-light-card p-4 mb-4">
                <h5 class="card-title fw-bold mb-3">第一步：安裝</h5>
                <p class="card-text mb-3">使用 npm 或 yarn 安裝我們的套件。這是開始的第一步。</p>
                <div class="bg-dark p-3 rounded">
                    <code class="text-success">npm install @docs/package</code>
                </div>
            </div>

            <div class="card border-0 bg-light-card p-4 mb-4">
                <h5 class="card-title fw-bold mb-3">第二步：配置</h5>
                <p class="card-text">根據你的需求配置基本設置。詳細說明請參考 API 文檔。</p>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">下一步</button>
                <button class="btn btn-outline-primary">返回</button>
            </div>
        </div>
    </div>
</div>

<!-- 手機版頂部導航 -->
<div class="d-md-none position-fixed top-0 start-0 w-100 bg-light-gray border-bottom p-3 z-3" style="padding-top: 1rem !important;">
    <div class="d-flex justify-content-between align-items-center">
        <button class="btn btn-sm" id="mobileMenuBtn" style="background: none; border: none; font-size: 1.5rem; z-index: 1001;">☰</button>
        <span class="fw-bold">Docs</span>
        <select class="form-select form-select-sm" style="width: auto;">
            <option selected>中</option>
            <option>En</option>
        </select>
    </div>
</div>

<!-- 手機版側邊欄 -->
<nav class="sidebar bg-sidebar d-md-none position-fixed start-0 top-0 h-100" id="mobileSidebar" style="width: 280px; overflow-y: auto; z-index: 999; transform: translateX(-100%); transition: transform 0.3s ease; background-color: #CBD5E1 !important;">
    <div class="p-3 border-bottom" style="background-color: #2563EB; margin-top: 3.5rem;">
        <h5 class="fw-bold text-white mb-0">Docs</h5>
    </div>
    <div class="p-3">
        <input type="text" class="form-control form-control-sm" placeholder="搜尋...">
    </div>
    <div class="nav flex-column">
        <a class="nav-link text-dark p-3 border-bottom" href="#">快速開始</a>
        <div class="ps-4 small text-muted">
            <a class="nav-link text-muted p-2" href="#">• 安裝</a>
            <a class="nav-link text-muted p-2" href="#">• 基本設置</a>
        </div>
        <a class="nav-link text-dark p-3 border-bottom" href="#">API 文檔</a>
        <div class="ps-4 small text-muted">
            <a class="nav-link text-muted p-2" href="#">• 認證</a>
            <a class="nav-link text-muted p-2" href="#">• 端點</a>
        </div>
        <a class="nav-link text-primary p-3 border-bottom bg-light" href="#">教學指南</a>
        <div class="ps-4 small text-muted">
            <a class="nav-link text-muted p-2" href="#">• 基礎教學</a>
            <a class="nav-link text-muted p-2" href="#">• 進階用法</a>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // 手機版漢堡菜單
    document.getElementById('mobileMenuBtn').addEventListener('click', function() {
        const sidebar = document.getElementById('mobileSidebar');
        const isOpen = sidebar.style.transform === 'translateX(0px)';
        sidebar.style.transform = isOpen ? 'translateX(-100%)' : 'translateX(0px)';
    });

    // 點擊側邊欄外關閉菜單
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('mobileSidebar');
        const menuBtn = document.getElementById('mobileMenuBtn');
        const isOpen = sidebar.style.transform === 'translateX(0px)';

        // 如果點擊的不是側邊欄也不是菜單按鈕，就關閉側邊欄
        if (isOpen && !sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
            sidebar.style.transform = 'translateX(-100%)';
        }
    });
</script>
</body>
</html>

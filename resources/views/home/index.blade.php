<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>技術文檔 - Docs</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        .navbar {
            padding: 0.5rem 1rem !important;
        }
        .navbar-brand {
            padding: 0 !important;
            margin: 0 !important;
        }
        .navbar-toggler {
            padding: 0.25rem 0.5rem !important;
        }
        .content-wrapper {
            padding: 2rem 1rem;
        }
        @media (min-width: 768px) {
            .content-wrapper {
                padding: 3rem 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- 文檔清單頁 -->
    <div id="listPage" class="page">
        <!-- 導航欄 -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light-gray sticky-top">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold" href="list.html">Docs</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">語言:</span>
                        <select class="form-select form-select-sm" style="width: auto;">
                            <option selected>繁體中文</option>
                            <option>English</option>
                            <option>简体中文</option>
                        </select>
                    </div>
                </div>
            </div>
        </nav>

        <!-- 主內容 -->
        <div class="content-wrapper">
            <div class="container-fluid">
                <h1 class="display-5 fw-bold mb-2">技術文檔</h1>
                <p class="text-muted fs-5 mb-4">選擇你要查看的文檔類別</p>

                <!-- 搜尋框 -->
                <div class="mb-5">
                    <input type="text" class="form-control form-control-lg" placeholder="搜尋文檔..." style="max-width: 400px;">
                </div>

                <!-- 快速開始 -->
                <div class="mb-5">
                    <h2 class="h4 fw-bold mb-3">快速開始</h2>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <a href="doc.html" class="card h-100 border-0 bg-light-card p-4 cursor-pointer hover-shadow text-decoration-none text-dark">
                                <div class="fs-3 mb-3">📦</div>
                                <h5 class="card-title fw-bold">安裝指南</h5>
                                <p class="card-text text-muted small">了解如何安裝和設置</p>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="doc.html" class="card h-100 border-0 bg-light-card p-4 cursor-pointer hover-shadow text-decoration-none text-dark">
                                <div class="fs-3 mb-3">🚀</div>
                                <h5 class="card-title fw-bold">快速開始</h5>
                                <p class="card-text text-muted small">5 分鐘內開始使用</p>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="doc.html" class="card h-100 border-0 bg-light-card p-4 cursor-pointer hover-shadow text-decoration-none text-dark">
                                <div class="fs-3 mb-3">⚙️</div>
                                <h5 class="card-title fw-bold">基本配置</h5>
                                <p class="card-text text-muted small">配置你的第一個項目</p>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- API 文檔 -->
                <div class="mb-5">
                    <h2 class="h4 fw-bold mb-3">API 文檔</h2>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <a href="doc.html" class="card h-100 border-0 bg-light-card p-4 cursor-pointer hover-shadow text-decoration-none text-dark">
                                <div class="fs-3 mb-3">�</div>
                                <h5 class="card-title fw-bold">認證</h5>
                                <p class="card-text text-muted small">API 認證和授權</p>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="doc.html" class="card h-100 border-0 bg-light-card p-4 cursor-pointer hover-shadow text-decoration-none text-dark">
                                <div class="fs-3 mb-3">📡</div>
                                <h5 class="card-title fw-bold">端點</h5>
                                <p class="card-text text-muted small">所有可用的 API 端點</p>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="doc.html" class="card h-100 border-0 bg-light-card p-4 cursor-pointer hover-shadow text-decoration-none text-dark">
                                <div class="fs-3 mb-3">�</div>
                                <h5 class="card-title fw-bold">參考</h5>
                                <p class="card-text text-muted small">完整的 API 參考文檔</p>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 教學指南 -->
                <div class="mb-5">
                    <h2 class="h4 fw-bold mb-3">教學指南</h2>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <a href="doc.html" class="card h-100 border-0 bg-light-card p-4 cursor-pointer hover-shadow text-decoration-none text-dark">
                                <div class="fs-3 mb-3">�</div>
                                <h5 class="card-title fw-bold">基礎教學</h5>
                                <p class="card-text text-muted small">初學者必讀指南</p>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="doc.html" class="card h-100 border-0 bg-light-card p-4 cursor-pointer hover-shadow text-decoration-none text-dark">
                                <div class="fs-3 mb-3">🎓</div>
                                <h5 class="card-title fw-bold">進階用法</h5>
                                <p class="card-text text-muted small">深入學習高級功能</p>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="doc.html" class="card h-100 border-0 bg-light-card p-4 cursor-pointer hover-shadow text-decoration-none text-dark">
                                <div class="fs-3 mb-3">💡</div>
                                <h5 class="card-title fw-bold">最佳實踐</h5>
                                <p class="card-text text-muted small">行業最佳實踐和技巧</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

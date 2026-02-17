<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>技術文檔 - Docs</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- 文檔清單頁 -->
    <div id="listPage" class="page">
        <!-- 導航欄 -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light-gray sticky-top">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold" href="{{ route('frontend.manuals') }}">Docs</a>
                <div class="ms-auto">
                    <x-language-selector id="languageSelector" size="sm" />
                </div>
            </div>
        </nav>

        <!-- 主內容 -->
        <div class="content-wrapper">
            <div class="container-fluid">
                <h1 class="display-5 fw-bold mb-2">技術文檔</h1>
                <p class="text-muted fs-5 mb-4">選擇你要查看的文檔</p>

                <!-- 搜尋框 -->
                <div class="mb-5">
                    <input
                        type="text"
                        class="form-control form-control-lg"
                        id="searchInput"
                        placeholder="搜尋文檔..."
                        style="max-width: 400px;"
                        aria-label="搜尋文檔"
                    >
                </div>

                <!-- 手冊卡片網格 -->
                <div class="row g-3" id="manualGrid">
                    @forelse($manuals as $manual)
                        <x-manual-card :manual="$manual" />
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info text-center py-5" role="status">
                                <h3 class="mb-3">暫無文檔</h3>
                                <p class="mb-0">目前沒有可用的公開文檔。請稍後再試。</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- 空搜尋狀態 -->
                <div class="row" id="emptySearchState" style="display: none;">
                    <div class="col-12">
                        <div class="alert alert-warning text-center py-5" role="status">
                            <h3 class="mb-3">未找到結果</h3>
                            <p class="mb-0">沒有文檔符合你的搜尋條件。請嘗試不同的搜尋詞或語言。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

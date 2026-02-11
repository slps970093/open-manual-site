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

    <script src="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const manualGrid = document.getElementById('manualGrid');
            const emptySearchState = document.getElementById('emptySearchState');
            const manualCards = document.querySelectorAll('.manual-card-wrapper');

            // 搜尋功能
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                let visibleCount = 0;

                manualCards.forEach(card => {
                    const title = card.querySelector('.card-title').textContent.toLowerCase();
                    const description = card.querySelector('.card-text').textContent.toLowerCase();

                    if (title.includes(searchTerm) || description.includes(searchTerm)) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // 顯示/隱藏空狀態
                if (visibleCount === 0 && searchTerm.length > 0) {
                    manualGrid.style.display = 'none';
                    emptySearchState.style.display = 'block';
                } else {
                    manualGrid.style.display = 'grid';
                    emptySearchState.style.display = 'none';
                }

                // 更新 URL 搜尋參數
                if (searchTerm) {
                    const url = new URL(window.location);
                    url.searchParams.set('q', searchTerm);
                    window.history.replaceState({}, '', url);
                } else {
                    const url = new URL(window.location);
                    url.searchParams.delete('q');
                    window.history.replaceState({}, '', url);
                }
            });

            // 從 URL 恢復搜尋查詢
            const urlParams = new URLSearchParams(window.location.search);
            const searchQuery = urlParams.get('q');
            if (searchQuery) {
                searchInput.value = searchQuery;
                searchInput.dispatchEvent(new Event('input'));
            }
        });
    </script>
</body>
</html>

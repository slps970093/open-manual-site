import './bootstrap';
import * as bootstrap from 'bootstrap';


// 頁面切換
function showPage(pageId) {
    document.querySelectorAll('.page').forEach(page => {
        page.classList.add('d-none');
    });
    document.getElementById(pageId).classList.remove('d-none');
}

// 卡片點擊事件
document.querySelectorAll('.bg-light-card').forEach(card => {
    card.addEventListener('click', function() {
        showPage('docPage');
        window.scrollTo(0, 0);
    });
});

// 返回清單按鈕
document.querySelectorAll('.btn-light').forEach(btn => {
    if (btn.textContent.includes('清單')) {
        btn.addEventListener('click', function() {
            showPage('listPage');
            window.scrollTo(0, 0);
        });
    }
});

// 側邊欄導航
document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        if (!this.classList.contains('text-muted')) {
            e.preventDefault();
            document.querySelectorAll('.sidebar .nav-link').forEach(l => {
                l.classList.remove('active', 'bg-light', 'text-primary');
            });
            this.classList.add('active', 'bg-light', 'text-primary');
        }
    });
});

// 搜尋功能
document.querySelectorAll('input[placeholder*="搜尋"]').forEach(input => {
    input.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        if (searchTerm.length > 0) {
            console.log('搜尋:', searchTerm);
            // 可以在這裡添加搜尋邏輯
        }
    });
});

// 語言切換
document.querySelectorAll('select').forEach(select => {
    select.addEventListener('change', function(e) {
        console.log('語言已切換為:', e.target.value);
        // 可以在這裡添加語言切換邏輯
    });
});

// 漢堡菜單（手機版）
document.addEventListener('DOMContentLoaded', function() {
    const toggler = document.querySelector('.navbar-toggler');
    if (toggler) {
        toggler.addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('show');
            }
        });
    }
});

// 響應式調整
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.remove('show');
        }
    }
});

// 手機版菜單控制
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const mobileOverlay = document.getElementById('mobileOverlay');

    if (mobileMenuBtn && mobileSidebar) {
        // 打開菜單
        mobileMenuBtn.addEventListener('click', function() {
            mobileSidebar.style.transform = 'translateX(0)';
            if (mobileOverlay) {
                mobileOverlay.style.display = 'block';
            }
        });

        // 關閉菜單
        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', function() {
                mobileSidebar.style.transform = 'translateX(-100%)';
                mobileOverlay.style.display = 'none';
            });
        }

        // 點擊菜單項目後關閉
        mobileSidebar.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                mobileSidebar.style.transform = 'translateX(-100%)';
                if (mobileOverlay) {
                    mobileOverlay.style.display = 'none';
                }
            });
        });
    }

    // 設置第一個導航項目為活躍
    const firstNavLink = document.querySelector('.sidebar .nav-link:not(.text-muted)');
    if (firstNavLink) {
        firstNavLink.classList.add('active', 'bg-light', 'text-primary');
    }
});

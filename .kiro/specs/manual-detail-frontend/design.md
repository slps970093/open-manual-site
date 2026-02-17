# 設計文件：手冊詳情頁前端

## 概述

手冊詳情頁前端是一個響應式 Web 介面，用於展示技術文檔手冊的詳細內容。該設計基於現有的 Bootstrap 5 模板和 ClosureTable 樹狀結構，提供了一個直觀的用戶介面，支持層級菜單導航、多語言內容、麵包屑導航和響應式設計。該功能與現有的 Laravel 後端集成，使用 Spatie Translatable 進行多語言支持。

## 架構

### 技術棧

- **前端框架**: Laravel Blade 模板引擎
- **CSS 框架**: Bootstrap 5（通過 Vite 集成）
- **JavaScript**: Vanilla JavaScript（無框架依賴）
- **構建工具**: Vite（模塊化 JavaScript 管理）
- **後端集成**: Laravel 路由和控制器
- **多語言**: Spatie Translatable（已在 Manual 模型中實現）
- **樹狀結構**: ClosureTable（已在 ManualMenu 模型中實現）
- **資料庫**: Manual、ManualMenu、ManualPageInfo、ManualPageContent 模型

### 頁面流程

```
用戶訪問 /manuals/{slug}
    ↓
FrontendController 加載手冊詳情頁
    ↓
檢索手冊、菜單樹、頁面內容（按語言篩選）
    ↓
渲染左側邊欄菜單、麵包屑、主內容區
    ↓
用戶可以：
  - 點擊菜單項導航到不同頁面
  - 展開/摺疊菜單項
  - 更改語言
  - 在菜單中搜尋
```

### 組件架構

```
ManualDetailPage
├── Head
│   ├── @vite CSS (resources/css/app.css, resources/css/manuals.css)
│   ├── @vite JS (resources/js/manual.js)
│   ├── Font Awesome Icons
│   └── Meta Tags
├── Body
│   ├── Desktop Layout (Flex)
│   │   ├── Sidebar (d-none d-md-block)
│   │   │   ├── Header (Primary Color Background)
│   │   │   ├── Menu Search Input
│   │   │   └── Menu Tree
│   │   │       └── Menu Item (recursive)
│   │   │           ├── Expand/Collapse Button
│   │   │           ├── Menu Item Label
│   │   │           ├── External Link Icon
│   │   │           └── Child Menu Items
│   │   └── Main Content Area
│   │       ├── Top Navigation Bar (d-none d-md-block)
│   │       │   ├── Breadcrumb Navigation (flex-grow-1)
│   │       │   ├── Back to List Button
│   │       │   └── Language Selector (x-language-selector)
│   │       └── Content Area
│   │           ├── Page Title
│   │           ├── Page Metadata
│   │           └── Page Content (HTML)
│   ├── Mobile Header (d-md-none, position-fixed, top-0)
│   │   ├── Row 1: Menu Button | Title (centered) | Back Button
│   │   │   ├── Menu Toggle Button (☰)
│   │   │   ├── Manual/Page Title (flex-grow-1, text-center, ellipsis)
│   │   │   └── Back to List Button (← 清單)
│   │   └── Row 2: Language Selector (full width)
│   │       └── Language Selector (x-language-selector)
│   └── Mobile Sidebar (d-md-none, position-fixed, start-0, top-0)
│       ├── Header (Primary Color Background, margin-top: 3.5rem)
│       ├── Menu Search Input
│       └── Menu Tree (same as desktop)
└── Scripts
    └── Vite Bundle (resources/js/manual.js)
        ├── Bootstrap Module
        ├── Language Switcher Module
        └── Menu Manager Module
```

## 組件和介面

### 1. 菜單樹組件

**責任**: 顯示手冊的層級菜單結構

**輸入**:
- `menuItems`: ManualMenu 模型實例的集合
- `currentPageId`: 當前正在查看的頁面 ID
- `language`: 當前選擇的語言代碼

**輸出**:
- 渲染的 HTML 菜單樹，包含：
  - 展開/摺疊按鈕（如果有子項）
  - 菜單項標籤
  - 外部鏈接圖標（如果 click_action 為 external）
  - 活動菜單項高亮
  - 遞迴子菜單項

**樣式**:
- Bootstrap nav 類
- 懸停效果
- 活動項高亮（背景色、文字顏色、字體粗細）
- 展開/摺疊動畫

**實現細節**:
- 使用 ClosureTable 的 `descendants()` 方法高效查詢菜單樹
- 使用 JavaScript 處理展開/摺疊邏輯
- 使用 localStorage 保存展開/摺疊狀態

### 2. 麵包屑導航組件

**責任**: 顯示用戶在菜單樹中的當前位置

**輸入**:
- `currentMenuItem`: 當前菜單項
- `manualName`: 手冊名稱

**輸出**:
- 渲染的 HTML 麵包屑，包含：
  - 手冊名稱（鏈接到手冊首頁）
  - 從根到當前項的完整路徑
  - 分隔符（>）
  - 可點擊的鏈接

**樣式**:
- Bootstrap breadcrumb 類
- 小字體大小
- 灰色文字顏色

**實現細節**:
- 使用 ClosureTable 的 `ancestors()` 方法獲取祖先菜單項
- 按深度排序祖先項以構建正確的路徑
- 在移動設備上簡化顯示（只顯示當前項和父項）

### 3. 菜單搜尋組件

**責任**: 提供菜單內搜尋功能

**功能**:
- 監聽輸入事件
- 執行不區分大小寫的文本匹配
- 篩選菜單項
- 自動展開包含匹配項的父菜單項
- 高亮搜尋匹配的文本

**實現**:
- 使用 JavaScript 事件監聽器
- 客戶端篩選（無需伺服器往返）
- 防抖搜尋輸入以提高性能

### 4. 頁面內容區組件

**責任**: 顯示頁面的實際內容

**輸入**:
- `pageContent`: ManualPageContent 模型實例
- `pageInfo`: ManualPageInfo 模型實例

**輸出**:
- 渲染的 HTML 內容，包含：
  - 頁面標題
  - 頁面元數據（建立日期、最後更新日期）
  - 頁面內容（HTML）
  - 導航按鈕（上一頁、下一頁）

**樣式**:
- Bootstrap 排版類
- 代碼塊語法高亮
- 圖像響應式縮放
- 一致的間距和邊距

**實現細節**:
- 使用 HTML Purifier 清理用戶提交的 HTML 內容
- 使用 Markdown 解析器（如果內容為 Markdown 格式）
- 應用 CSS 類以確保代碼塊、表格等正確格式化

### 5. 語言選擇器組件

**責任**: 允許用戶選擇語言

**功能**:
- 顯示可用語言列表（10 種語言）
- 持久化選擇到 localStorage
- 重新加載頁面以應用語言變更
- 篩選內容以顯示所選語言

**實現**:
- 使用共用 Blade 元件 `<x-language-selector>`
- 支持自訂 ID 和大小（sm、md、lg）
- 包含 10 種語言：繁體中文、English、简体中文、日本語、한국어、Español、Français、Deutsch、Русский、العربية
- JavaScript 事件監聽器處理語言變更
- localStorage API 用於持久化

### 6. 響應式側邊欄組件

**責任**: 在移動設備上提供可摺疊的側邊欄

**功能**:
- 在桌面設備上始終顯示
- 在移動設備上隱藏並提供漢堡菜單按鈕
- 點擊漢堡菜單按鈕時顯示/隱藏側邊欄
- 點擊側邊欄外時自動關閉側邊欄

**實現**:
- 使用 Bootstrap 的 d-none d-md-block 類
- 使用 CSS transform 和 transition 實現平滑動畫
- 使用 JavaScript 處理點擊事件

## JavaScript 架構

### Vite 構建系統

該項目使用 Vite 作為前端構建工具，管理 CSS 和 JavaScript 資源。

**Vite 配置** (`vite.config.js`):
```javascript
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',      // 首頁專用
                'resources/js/manual.js',   // 手冊/頁面專用
            ],
            refresh: true,
        }),
    ],
});
```

### 模塊化 JavaScript 結構

所有 JavaScript 代碼被組織成模塊化文件，通過 Vite 進行管理和打包。

#### 1. 入口點

**`resources/js/app.js`** - 首頁專用
```javascript
import './bootstrap';
import * as bootstrap from 'bootstrap';
import { initSearchManager } from './search-manager';

document.addEventListener('DOMContentLoaded', function() {
    initSearchManager(
        '#searchInput',
        '#manualGrid',
        '#emptySearchState'
    );
});
```

**`resources/js/manual.js`** - 手冊/頁面詳情專用
```javascript
import './bootstrap';
import * as bootstrap from 'bootstrap';
import { initLanguageSwitcher } from './language-switcher';
import { initMenuManager } from './menu-manager';

document.addEventListener('DOMContentLoaded', function() {
    initLanguageSwitcher();
    
    initMenuManager(
        '#menuTree',
        '#menuSearchInput',
        '#mobileMenuBtn',
        '#mobileSidebar'
    );
    
    initMenuManager(
        '#mobileMenuTree',
        '#mobileMenuSearchInput',
        null,
        null
    );
});
```

#### 2. 共享模塊

**`resources/js/bootstrap.js`** - Bootstrap 和全局配置
- 導入 Bootstrap CSS 和 JavaScript
- 設置全局配置
- 所有入口點都導入此模塊

**`resources/js/language-switcher.js`** - 語言切換功能
- 導出 `initLanguageSwitcher()` 函數
- 監聽語言選擇器變更事件
- 更新 localStorage 並重新加載頁面

**`resources/js/menu-manager.js`** - 菜單管理功能
- 導出 `initMenuManager(menuSelector, searchSelector, mobileBtn, mobileSidebar)` 函數
- 處理菜單展開/摺疊
- 實現菜單搜尋功能
- 管理 localStorage 中的展開狀態

**`resources/js/search-manager.js`** - 搜尋功能（首頁）
- 導出 `initSearchManager(searchSelector, gridSelector, emptyStateSelector)` 函數
- 實現手冊卡片搜尋功能

### 視圖集成

#### 首頁 (`resources/views/frontend/index.blade.php`)
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

#### 手冊詳情頁 (`resources/views/frontend/manual.blade.php`)
```blade
@vite(['resources/css/app.css', 'resources/css/manuals.css', 'resources/js/manual.js'])
```

#### 頁面詳情頁 (`resources/views/frontend/page.blade.php`)
```blade
@vite(['resources/css/app.css', 'resources/css/manuals.css', 'resources/js/manual.js'])
```

### 構建和開發

**開發模式**:
```bash
npm run dev
```
- Vite 開發服務器運行在 http://localhost:5173
- 自動熱模塊替換 (HMR)
- 源代碼映射用於調試

**生產構建**:
```bash
npm run build
```
- 生成優化的生產包
- 代碼最小化和樹搖動
- 資源哈希用於緩存破壞
- 輸出到 `public/build/` 目錄

### 優勢

1. **模塊化**: 代碼被組織成可重用的模塊
2. **性能**: Vite 提供快速的開發體驗和優化的生產構建
3. **可維護性**: 清晰的文件結構和職責分離
4. **無 CDN 依賴**: Bootstrap 通過 npm 管理，不依賴外部 CDN
5. **一致性**: 所有 JavaScript 通過統一的構建系統管理

## CSS 架構

### 文件組織

CSS 被分為兩個主要文件，通過 Vite 進行管理：

**`resources/css/app.css`** - 全局樣式
- Bootstrap 5 導入
- CSS 變量定義（顏色、間距、排版）
- 全局組件樣式（導航欄、卡片、表單、按鈕）
- 響應式設計基礎
- 深色模式支持

**`resources/css/manuals.css`** - 手冊/頁面詳情專用
- 菜單樹樣式（展開/摺疊、活動狀態、懸停效果）
- 空狀態樣式
- 頁面內容樣式（標題、代碼塊、表格、圖像）
- 頁面元數據樣式
- 麵包屑導航樣式
- 移動響應式調整
- 深色模式支持

### 移動響應式設計

#### 桌面佈局 (≥768px)
- 側邊欄始終顯示（寬度 280px）
- 頂部導航欄顯示麵包屑、返回按鈕、語言選擇器
- 主內容區佔據剩餘空間
- 使用 `d-none d-md-block` 類控制可見性

#### 移動佈局 (<768px)
- 側邊欄隱藏，使用漢堡菜單按鈕切換
- 頂部導航欄分為兩行：
  - **第一行**: 菜單按鈕 | 標題（居中） | 返回按鈕
    - 使用 `justify-content-between` 分佈
    - 標題使用 `flex-grow-1` 佔據中間空間
    - 標題使用 `text-overflow: ellipsis` 處理長文本
    - 按鈕使用 `flex-shrink: 0` 防止壓縮
  - **第二行**: 語言選擇器（全寬）
    - 使用 `px-2 pb-2` 提供邊距
- 內容區頂部邊距調整為 `6rem` 以適應固定頭部
- 使用 `position-fixed` 和 `z-index` 管理層級

#### 移動側邊欄動畫
- 使用 `transform: translateX(-100%)` 隱藏側邊欄
- 使用 `transition: transform 0.3s ease` 提供平滑動畫
- 點擊菜單按鈕時切換 `translateX(0)`
- 使用 `z-index: 999` 確保在內容上方

### Vite 配置

```javascript
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',      // 全局樣式
                'resources/css/manuals.css',  // 手冊頁面專用
                'resources/js/app.js',        // 首頁 JS
                'resources/js/manual.js',     // 手冊頁面 JS
            ],
            refresh: true,
        }),
    ],
});
```

### CSS 變量

所有顏色和設計令牌都定義為 CSS 變量，便於維護和主題化：

```css
:root {
    --primary-color: #2563EB;
    --bg-light: #E2E8F0;
    --bg-light-gray: #F1F5F9;
    --bg-light-card: #F8FAFC;
    --text-dark: #1E293B;
    --text-muted: #64748B;
    --text-light: #94A3B8;
    --border-color: #E2E8F0;
}
```

### 優勢

1. **分離關注點**: 全局樣式和頁面特定樣式分開管理
2. **可重用性**: 全局樣式在所有頁面中使用
3. **性能**: 只加載必要的 CSS
4. **可維護性**: 清晰的文件結構便於修改
5. **主題化**: CSS 變量支持輕鬆的主題切換
6. **移動優先**: 清晰的移動響應式設計，簡化的兩行頭部佈局

## 移動頭部佈局設計

### 概述

移動版本採用簡化的兩行頭部設計，優化了小屏幕空間利用。

### 第一行：導航欄

**結構**:
```
[☰ Menu] [Title (centered)] [← Back]
```

**組件**:
- **菜單按鈕** (左側)
  - 按鈕樣式: `btn btn-sm`
  - 背景: 無 (`background: none`)
  - 邊框: 無 (`border: none`)
  - 字體大小: `1.5rem`
  - Z-index: `1001`
  - 顏色: `var(--text-dark)`
  - 內邊距: `0`
  - 縮放: `flex-shrink: 0`
  - 功能: 點擊時切換側邊欄可見性

- **標題** (中間)
  - 容器: `span.fw-bold.text-center`
  - 字體大小: `0.85rem`
  - 寬度: `flex-grow-1`
  - 溢出處理: `text-overflow: ellipsis`
  - 空白: `white-space: nowrap`
  - 內容: 手冊名稱或頁面標題

- **返回按鈕** (右側)
  - 按鈕樣式: `btn btn-sm btn-light`
  - 文本: `← 清單`
  - 空白: `white-space: nowrap`
  - 縮放: `flex-shrink: 0`
  - 功能: 導航回手冊列表或首頁

**佈局**:
- 容器: `d-flex justify-content-between align-items-center`
- 間距: `gap-2`
- 內邊距: `p-2`
- 位置: `position-fixed top-0 start-0 w-100`
- Z-index: `z-3`
- 背景: `bg-light-gray`
- 邊框: `border-bottom`

### 第二行：語言選擇器

**結構**:
```
[Language Selector (full width)]
```

**組件**:
- **語言選擇器** (全寬)
  - 組件: `<x-language-selector>`
  - ID: `mobileLanguageSelector`
  - 大小: `sm`
  - 容器內邊距: `px-2 pb-2`
  - 功能: 允許用戶選擇語言並重新加載頁面

**佈局**:
- 容器: `div`
- 內邊距: `px-2 pb-2`
- 寬度: `100%`

### 側邊欄

**位置和尺寸**:
- 位置: `position-fixed start-0 top-0 h-100`
- 寬度: `280px`
- Z-index: `999`
- 背景: `bg-light-gray`
- 溢出: `overflow-y: auto`

**動畫**:
- 默認狀態: `transform: translateX(-100%)`
- 打開狀態: `transform: translateX(0)`
- 過渡: `transition: transform 0.3s ease`

**內容**:
- 頭部: 手冊名稱（背景色為主色）
- 搜尋框: 菜單搜尋輸入
- 菜單樹: 與桌面版相同的菜單結構

**頭部邊距**:
- 頭部上邊距: `margin-top: 3.5rem`
- 原因: 避免與固定頂部導航重疊

### 內容區調整

**移動版內容區**:
- 頂部邊距: `margin-top: 6rem`
- 原因: 為固定頭部（第一行 ~3.5rem + 第二行 ~2.5rem）預留空間
- 內邊距: `p-4`
- 背景: `bg-light`
- 溢出: `overflow-y: auto`

### 響應式斷點

- **桌面** (≥768px): 側邊欄始終顯示，頂部導航欄顯示麵包屑
- **平板** (768px-1023px): 側邊欄始終顯示，頂部導航欄顯示麵包屑
- **手機** (<768px): 側邊欄隱藏，使用漢堡菜單切換，簡化頭部佈局

### 設計優勢

1. **空間利用**: 兩行設計最大化利用有限的移動屏幕空間
2. **清晰性**: 菜單、標題、返回按鈕清晰分離
3. **可用性**: 大按鈕易於點擊，標題居中便於識別
4. **一致性**: 與桌面版共享相同的菜單和語言選擇器邏輯
5. **性能**: 使用 CSS transform 實現平滑動畫，無性能損耗

## 資料模型

### Manual 模型（現有）

```php
{
  id: integer,
  url_slug: string,
  name: translatable string,
  description: translatable string,
  is_public: boolean,
  created_at: datetime,
  updated_at: datetime,
  deleted_at: datetime (soft delete)
}
```

### ManualMenu 模型（現有，使用 ClosureTable）

```php
{
  id: integer,
  manual_id: integer (foreign key),
  name: string,
  click_action: enum('page', 'external', 'expand'),
  url: string (nullable),
  manual_page_info_id: integer (nullable, foreign key),
  created_at: datetime,
  updated_at: datetime
}
```

### ManualPageInfo 模型（現有）

```php
{
  id: integer,
  manual_id: integer (foreign key),
  title: translatable string,
  keyword: translatable string,
  created_at: datetime,
  updated_at: datetime
}
```

### ManualPageContent 模型（現有）

```php
{
  id: integer,
  manual_page_info_id: integer (foreign key),
  content: text (HTML or Markdown),
  created_at: datetime,
  updated_at: datetime
}
```

### 前端資料結構

```javascript
// 菜單項結構
{
  id: 1,
  name: "安裝指南",
  click_action: "page",
  url: "/manuals/installation/install",
  manual_page_info_id: 5,
  children: [
    {
      id: 2,
      name: "系統要求",
      click_action: "page",
      url: "/manuals/installation/requirements",
      manual_page_info_id: 6,
      children: []
    }
  ]
}

// 頁面內容結構
{
  id: 5,
  title: "安裝指南",
  keyword: "安裝, 設置, 開始",
  content: "<h1>安裝指南</h1><p>...</p>",
  created_at: "2024-01-15",
  updated_at: "2024-01-20"
}
```

## 正確性屬性

一個屬性是應該在系統的所有有效執行中保持真實的特徵或行為——本質上是關於系統應該做什麼的正式陳述。屬性充當人類可讀規範和機器可驗證正確性保證之間的橋樑。


### 屬性 1：菜單樹完整性

*對於任何* 手冊和菜單項集合，頁面應該顯示所有菜單項，包括根項和所有後代項。

**驗證: 需求 2.1**

### 屬性 2：展開/摺疊按鈕可見性

*對於任何* 菜單項，如果它有子項，應該顯示展開/摺疊按鈕；如果沒有子項，不應該顯示按鈕。

**驗證: 需求 2.2**

### 屬性 3：展開/摺疊功能

*對於任何* 有子項的菜單項，點擊展開按鈕應該使子項可見，點擊摺疊按鈕應該隱藏子項。

**驗證: 需求 2.3, 2.4**

### 屬性 4：無限深度支持

*對於任何* 菜單樹深度，系統應該正確渲染所有層級的菜單項。

**驗證: 需求 2.6**

### 屬性 5：活動菜單項高亮

*對於任何* 當前頁面，對應的菜單項應該被高亮顯示，並且所有祖先菜單項應該被自動展開。

**驗證: 需求 3.1, 3.2, 3.3**

### 屬性 6：麵包屑路徑正確性

*對於任何* 嵌套菜單項，麵包屑應該顯示從根到當前項的完整路徑，按正確的順序。

**驗證: 需求 4.2**

### 屬性 7：麵包屑導航

*對於任何* 麵包屑中的項目，點擊它應該導航到該項目對應的頁面。

**驗證: 需求 4.3**

### 屬性 8：麵包屑分隔符

*對於任何* 麵包屑，相鄰項目之間應該有分隔符（> 或 /）。

**驗證: 需求 4.4**

### 屬性 9：語言切換

*對於任何* 選擇的語言，頁面應該重新加載並顯示該語言的內容。

**驗證: 需求 5.1**

### 屬性 10：翻譯內容顯示

*對於任何* 手冊和選擇的語言，如果手冊在該語言中有翻譯，應該顯示該語言的標題、描述和內容。

**驗證: 需求 5.2**

### 屬性 11：語言選擇持久化

*對於任何* 用戶選擇的語言，在頁面重新加載後，應該恢復相同的語言選擇。

**驗證: 需求 5.4**

### 屬性 12：菜單項導航

*對於任何* 菜單項，點擊它應該在主內容區顯示該頁面的內容。

**驗證: 需求 6.1**

### 屬性 13：HTML 內容渲染

*對於任何* 頁面內容，HTML 元素（標題、段落、代碼塊等）應該被正確渲染。

**驗證: 需求 6.2**

### 屬性 14：圖像響應式縮放

*對於任何* 頁面內容中的圖像，它們應該被正確顯示並應用響應式縮放。

**驗證: 需求 6.4**

### 屬性 15：內容格式支持

*對於任何* 頁面內容，無論是 Markdown 還是 HTML 格式，都應該被正確渲染。

**驗證: 需求 6.6**

### 屬性 16：桌面佈局

*對於* 桌面視口寬度（≥1024px），頁面應該顯示左側邊欄和主內容區並排佈局。

**驗證: 需求 7.1**

### 屬性 17：平板佈局

*對於* 平板視口寬度（768px-1023px），頁面應該調整邊欄寬度或使用可摺疊邊欄。

**驗證: 需求 7.2**

### 屬性 18：移動佈局

*對於* 移動視口寬度（<768px），頁面應該隱藏邊欄並提供漢堡菜單按鈕。

**驗證: 需求 7.3**

### 屬性 19：移動菜單切換

*對於* 移動設備，點擊漢堡菜單按鈕應該顯示/隱藏側邊欄。

**驗證: 需求 7.4**

### 屬性 20：移動內容寬度

*對於* 移動設備，內容寬度應該適應屏幕寬度，不應該有水平滾動條。

**驗證: 需求 7.5**

### 屬性 21：頁面類型導航

*對於任何* click_action 為 page 的菜單項，點擊它應該導航到內部頁面。

**驗證: 需求 8.1**

### 屬性 22：外部鏈接行為

*對於任何* click_action 為 external 的菜單項，它應該有 target="_blank" 屬性以在新標籤頁中打開。

**驗證: 需求 8.2**

### 屬性 23：展開類型行為

*對於任何* click_action 為 expand 的菜單項，點擊它應該展開/摺疊而不導航。

**驗證: 需求 8.3**

### 屬性 24：分類項禁用導航

*對於任何* 沒有 url 的菜單項，點擊它不應該導航。

**驗證: 需求 8.4**

### 屬性 25：語義 HTML 結構

*對於任何* 渲染的頁面，它應該包含語義 HTML 元素（header、nav、main、section、article）。

**驗證: 需求 9.1**

### 屬性 26：標題層級正確性

*對於任何* 頁面內容，標題應該被正確嵌套（h1 > h2 > h3 等）。

**驗證: 需求 9.2**

### 屬性 27：SEO 元標籤

*對於任何* 頁面，它應該包含適當的 SEO 元標籤（title、description、keywords）。

**驗證: 需求 9.3**

### 屬性 28：圖像 Alt 文本

*對於任何* 頁面內容中的圖像，它們應該有 alt 文本或 ARIA 標籤。

**驗證: 需求 9.4**

### 屬性 29：鍵盤導航支持

*對於任何* 可交互的元素，應該支持鍵盤導航（Tab、Enter、Escape）。

**驗證: 需求 9.5**

### 屬性 30：焦點管理

*對於任何* 獲得焦點的元素，應該有視覺焦點指示。

**驗證: 需求 9.6**

### 屬性 31：螢幕閱讀器支持

*對於任何* 頁面，它應該包含適當的 ARIA 標籤、角色和狀態。

**驗證: 需求 9.8**

### 屬性 32：菜單搜尋篩選

*對於任何* 菜單搜尋查詢，搜尋結果應該只包含名稱中包含搜尋文本的菜單項（不區分大小寫）。

**驗證: 需求 12.1, 12.4**

### 屬性 33：搜尋結果展開

*對於任何* 菜單搜尋結果，包含匹配項的父菜單項應該被自動展開。

**驗證: 需求 12.2**

### 屬性 34：搜尋清除恢復

*對於任何* 活動搜尋查詢，清除搜尋輸入應該恢復菜單的原始狀態。

**驗證: 需求 12.3**

## 錯誤處理

### 手冊不存在

**場景**: 用戶訪問不存在的手冊

**處理**:
- 返回 404 錯誤頁面
- 提供返回首頁的鏈接
- 記錄錯誤以供監控

### 手冊不是公開的

**場景**: 用戶訪問非公開手冊

**處理**:
- 返回 404 錯誤頁面（不透露手冊存在）
- 提供返回首頁的鏈接
- 記錄訪問嘗試以供監控

### 菜單加載失敗

**場景**: 檢索菜單樹時資料庫連接失敗

**處理**:
- 在邊欄中顯示錯誤訊息
- 不影響主內容區
- 提供重試選項
- 記錄錯誤以供調試

### 頁面內容加載失敗

**場景**: 檢索頁面內容時資料庫連接失敗

**處理**:
- 在主內容區顯示錯誤訊息
- 提供重試選項
- 記錄錯誤以供調試

### 缺少翻譯

**場景**: 手冊在所選語言中沒有翻譯

**處理**:
- 顯示備用語言版本
- 或顯示提示信息告知用戶翻譯不可用
- 建議用戶選擇其他語言

## 測試策略

### 單元測試

**菜單樹渲染**:
- 測試菜單項正確渲染
- 測試展開/摺疊按鈕可見性
- 測試活動菜單項高亮
- 測試無限深度支持

**麵包屑導航**:
- 測試麵包屑路徑正確性
- 測試麵包屑分隔符
- 測試麵包屑導航功能

**語言切換**:
- 測試語言選擇持久化
- 測試翻譯內容顯示
- 測試缺少翻譯的備用行為

**頁面內容**:
- 測試 HTML 內容渲染
- 測試圖像響應式縮放
- 測試 Markdown 和 HTML 格式支持

**響應式設計**:
- 測試不同視口寬度的佈局
- 測試移動菜單切換
- 測試內容寬度適應

**菜單搜尋**:
- 測試搜尋篩選功能
- 測試搜尋結果展開
- 測試搜尋清除恢復

**無障礙**:
- 測試語義 HTML 結構
- 測試標題層級正確性
- 測試圖像 Alt 文本
- 測試鍵盤導航
- 測試焦點管理
- 測試 ARIA 屬性

### 屬性測試

**屬性 1：菜單樹完整性**
- 生成隨機菜單樹
- 驗證所有菜單項都被顯示
- 最少 100 次迭代

**屬性 2：展開/摺疊按鈕可見性**
- 生成具有不同子項數量的菜單項
- 驗證按鈕可見性與子項數量一致
- 最少 100 次迭代

**屬性 3：展開/摺疊功能**
- 生成隨機菜單樹
- 測試展開/摺疊操作
- 驗證子項可見性變化
- 最少 100 次迭代

**屬性 4：無限深度支持**
- 生成各種深度的菜單樹
- 驗證所有深度都能正確渲染
- 最少 100 次迭代

**屬性 5：活動菜單項高亮**
- 生成隨機菜單樹和頁面
- 驗證活動菜單項被高亮
- 驗證祖先菜單項被展開
- 最少 100 次迭代

**屬性 6-8：麵包屑導航**
- 生成隨機菜單樹和頁面
- 驗證麵包屑路徑正確性
- 驗證麵包屑分隔符
- 驗證麵包屑導航功能
- 最少 100 次迭代

**屬性 9-11：語言切換**
- 生成隨機手冊和語言
- 驗證語言切換功能
- 驗證翻譯內容顯示
- 驗證語言選擇持久化
- 最少 100 次迭代

**屬性 12-15：頁面內容**
- 生成隨機頁面內容
- 驗證菜單項導航
- 驗證 HTML 內容渲染
- 驗證圖像響應式縮放
- 驗證內容格式支持
- 最少 100 次迭代

**屬性 16-20：響應式設計**
- 測試不同視口寬度
- 驗證佈局適應性
- 驗證移動菜單切換
- 驗證內容寬度適應
- 最少 100 次迭代

**屬性 21-24：菜單項類型**
- 生成不同 click_action 類型的菜單項
- 驗證導航行為
- 驗證外部鏈接屬性
- 驗證展開行為
- 最少 100 次迭代

**屬性 25-31：無障礙**
- 驗證語義 HTML 結構
- 驗證標題層級正確性
- 驗證 SEO 元標籤
- 驗證圖像 Alt 文本
- 驗證鍵盤導航
- 驗證焦點管理
- 驗證 ARIA 屬性
- 最少 100 次迭代

**屬性 32-34：菜單搜尋**
- 生成隨機菜單樹和搜尋查詢
- 驗證搜尋篩選功能
- 驗證搜尋結果展開
- 驗證搜尋清除恢復
- 最少 100 次迭代

### 集成測試

**完整工作流**:
- 加載手冊詳情頁
- 點擊菜單項導航
- 展開/摺疊菜單項
- 更改語言
- 在菜單中搜尋
- 點擊麵包屑導航

**響應式設計**:
- 在不同設備上測試佈局
- 驗證移動菜單切換
- 驗證內容寬度適應

**無障礙**:
- 使用鍵盤導航
- 驗證焦點管理
- 驗證螢幕閱讀器兼容性
- 驗證色彩對比度

## 實現注意事項

### 性能優化

1. **資料庫查詢**:
   - 使用 eager loading 避免 N+1 查詢
   - 只檢索必要的欄位
   - 使用 ClosureTable 的高效查詢方法

2. **客戶端篩選**:
   - 使用防抖菜單搜尋輸入
   - 避免不必要的 DOM 操作
   - 考慮虛擬化大型菜單樹

3. **資源加載**:
   - 最小化 CSS 和 JavaScript（由 Vite 自動處理）
   - 使用 Vite 的資源哈希進行緩存破壞
   - 考慮延遲加載圖像

### 瀏覽器兼容性

- Chrome/Edge (最新 2 個版本)
- Firefox (最新 2 個版本)
- Safari (最新 2 個版本)
- 行動瀏覽器 (iOS Safari, Chrome Mobile)

### 無障礙標準

- WCAG 2.1 AA 級別
- 語義 HTML
- ARIA 標籤和屬性
- 鍵盤導航支持
- 足夠的色彩對比度


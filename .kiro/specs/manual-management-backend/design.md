# 設計文件：手冊管理後台系統

## 概述

手冊管理後台系統是一個基於 Laravel-Admin 的完整管理系統，用於管理多語言技術手冊。該系統利用 ClosureTable 套件管理階層式菜單結構，使用 spatie/laravel-translatable 套件處理多語言內容，並通過 Laravel-Admin 提供直觀的管理界面。

系統的核心設計原則：
- **模塊化架構**：清晰分離 Manual、Menu、PageInfo 和 PageContent 的職責
- **高效的樹狀管理**：使用 ClosureTable 進行高效的階層式菜單操作
- **簡化的多語言支持**：使用 spatie/laravel-translatable 將翻譯存儲為 JSON，無需額外表
- **用戶友好的界面**：通過 Laravel-Admin 提供一致的管理體驗

## 架構

### 系統層次結構

```
Manual Management Backend
├── Manual Layer (手冊管理)
│   ├── Manual Model (手冊模型)
│   └── Manual Controller (手冊控制器)
├── Menu Layer (菜單管理)
│   ├── ManualMenu Model (菜單模型 - 使用 ClosureTable)
│   └── ManualMenuController (菜單控制器)
├── Page Info Layer (頁面信息管理)
│   ├── ManualPageInfo Model (頁面信息模型)
│   └── ManualPageInfoController (頁面信息控制器)
└── Page Content Layer (頁面內容管理)
    ├── ManualPageContent Model (頁面內容模型)
    └── ManualPageContentController (頁面內容控制器)
```

### 數據流

```
Admin User
    ↓
Laravel-Admin Interface
    ↓
Controllers (驗證和業務邏輯)
    ↓
Models (數據操作和驗證)
    ↓
Database (持久化存儲)
```

## 組件和接口

### 1. Manual 模型

**職責**：管理手冊的基本信息和發布狀態

**屬性**：
- `id` (PK): 主鍵
- `url_slug` (UNIQUE): URL 友好的標識符
- `name` (JSON): 可翻譯的手冊名稱
- `description` (JSON): 可翻譯的手冊描述
- `is_public` (BOOLEAN): 是否對外開放（默認：false）
- `created_at`, `updated_at`: 時間戳

**表名**：`manual`

**關係**：
- `hasMany('ManualMenu')`: 一對多關係到菜單
- `hasMany('ManualPageInfo')`: 一對多關係到頁面信息

**特性**：
- 使用 `HasTranslations` trait 支持 `name` 和 `description` 的多語言
- 實現軟刪除以保護數據
- `is_public` 字段控制手冊的可見性

### 2. ManualMenu 模型

**職責**：管理手冊的階層式菜單結構

**屬性**：
- `id` (PK): 主鍵
- `manual_id` (FK): 關聯的手冊 ID
- `manual_page_info_id` (FK, 可選): 關聯的頁面信息 ID（當 click_action 為 'page' 時）
- `name` (JSON): 可翻譯的菜單項名稱
- `click_action` (ENUM): 點擊動作 ('external', 'page', 'expand')
- `url` (VARCHAR): 外部導航 URL（當 click_action 為 'external' 時必填）
- `created_at`, `updated_at`: 時間戳

**表名**：`manual_menu`

**ClosureTable 結構**：
- `manual_menu_closure` 表自動管理樹狀關係
- 字段：`ancestor`, `descendant`, `depth`

**關係**：
- `belongsTo('Manual')`: 多對一關係到手冊
- `belongsTo('ManualPageInfo')`: 多對一關係到頁面信息（可選）
- `parent()`: 獲取父菜單項
- `children()`: 獲取子菜單項
- `ancestors()`: 獲取所有祖先
- `descendants()`: 獲取所有後代

**特性**：
- 使用 `HasTranslations` trait 支持 `name` 的多語言
- 使用 ClosureTable 進行高效的樹狀操作
- 根據 click_action 驗證相應的字段

**Click Action 模式**：
- `external`: 導向外部連結，必須填充 `url` 字段，`manual_page_info_id` 為空
- `page`: 導向特定頁面，必須指定 `manual_page_info_id`，`url` 為空
- `expand`: 僅展開菜單，`url` 和 `manual_page_info_id` 都為空

### 3. ManualPageInfo 模型

**職責**：管理頁面的元數據

**屬性**：
- `id` (PK): 主鍵
- `manual_id` (FK): 關聯的手冊 ID
- `title` (JSON): 可翻譯的頁面標題
- `keyword` (JSON): 可翻譯的頁面關鍵詞
- `created_at`, `updated_at`: 時間戳

**表名**：`manual_page_info`

**關係**：
- `belongsTo('Manual')`: 多對一關係到手冊
- `hasMany('ManualPageContent')`: 一對多關係到頁面內容

**特性**：
- 使用 `HasTranslations` trait 支持 `title` 和 `keyword` 的多語言

### 4. ManualPageContent 模型

**職責**：管理特定語言的頁面內容

**屬性**：
- `id` (PK): 主鍵
- `manual_page_info_id` (FK): 關聯的頁面信息 ID
- `lang` (VARCHAR): 語言代碼 (e.g., 'en', 'zh-TW')
- `content` (LONGTEXT): 頁面內容（HTML 格式，由 CKEditor 生成）
- `created_at`, `updated_at`: 時間戳

**表名**：`manual_page_content`

**關係**：
- `belongsTo('ManualPageInfo')`: 多對一關係到頁面信息

**特性**：
- 複合唯一索引：`(manual_page_info_id, lang)`
- 使用 CKEditor 進行富文本編輯
- 支持通過 laravel-filemanager 插入圖片

## 富文本編輯和文件管理

### CKEditor 集成

系統使用 CKEditor 5 提供富文本編輯功能：

```php
// 在 Laravel-Admin 表單中集成 CKEditor
$form->textarea('content', '內容')
    ->options(['class' => 'ckeditor'])
    ->help('支持富文本編輯和圖片插入');
```

### 文件管理器集成

使用 laravel-filemanager 套件提供文件管理功能：

```bash
composer require unisharp/laravel-filemanager
```

**圖片路徑處理**：
- 系統應該支持相對路徑（例如：`/storage/manual-images/image.jpg`）
- 系統應該支持 CDN URL（例如：`https://cdn.example.com/manual-images/image.jpg`）
- 圖片路徑應該通過配置文件 `config/manual.php` 中的 `image_base_url` 進行管理
- 不應該在代碼中寫死絕對路徑

**配置示例**：

```php
// config/manual.php
return [
    'supported_languages' => [...],
    'required_languages' => [...],
    'default_language' => 'en',
    'image_base_url' => env('MANUAL_IMAGE_BASE_URL', '/storage/manual-images/'),
    // 或使用 CDN
    // 'image_base_url' => env('MANUAL_IMAGE_BASE_URL', 'https://cdn.example.com/manual-images/'),
];
```

## 數據模型

### 數據庫遷移

#### Manual 表

```php
Schema::create('manual', function (Blueprint $table) {
    $table->id();
    $table->string('url_slug')->unique();
    $table->json('name');
    $table->json('description')->nullable();
    $table->boolean('is_public')->default(false);
    $table->timestamps();
    $table->softDeletes();
});
```

#### ManualMenu 表

```php
Schema::create('manual_menu', function (Blueprint $table) {
    $table->id();
    $table->foreignId('manual_id')->constrained('manual')->onDelete('cascade');
    $table->foreignId('manual_page_info_id')->nullable()->constrained('manual_page_info')->onDelete('set null');
    $table->json('name');
    $table->enum('click_action', ['external', 'page', 'expand'])->default('expand');
    $table->string('url')->default('');
    $table->timestamps();
    $table->softDeletes();
});

// ClosureTable 自動創建
Schema::create('manual_menu_closure', function (Blueprint $table) {
    $table->unsignedBigInteger('ancestor');
    $table->unsignedBigInteger('descendant');
    $table->unsignedInteger('depth');
    
    $table->primary(['ancestor', 'descendant']);
    $table->foreign('ancestor')->references('id')->on('manual_menu')->onDelete('cascade');
    $table->foreign('descendant')->references('id')->on('manual_menu')->onDelete('cascade');
});
```

#### ManualPageInfo 表

```php
Schema::create('manual_page_info', function (Blueprint $table) {
    $table->id();
    $table->foreignId('manual_id')->constrained('manual')->onDelete('cascade');
    $table->json('title');
    $table->json('keyword')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

#### ManualPageContent 表

```php
Schema::create('manual_page_content', function (Blueprint $table) {
    $table->id();
    $table->foreignId('manual_page_info_id')->constrained('manual_page_info')->onDelete('cascade');
    $table->string('lang');
    $table->longText('content');
    $table->timestamps();
    $table->softDeletes();
    
    $table->unique(['manual_page_info_id', 'lang']);
});
```

### 多語言字段配置

spatie/laravel-translatable 使用 Laravel 的 `config/app.php` 中的 `locale` 和 `fallback_locale` 設置。

系統應該使用一個配置文件 `config/manual.php` 來管理手冊系統的多語言設置：

```php
// config/manual.php
return [
    'supported_languages' => [
        'en' => 'English',
        'zh-TW' => '繁體中文',
        'zh-CN' => '簡體中文',
        'ja' => '日本語',
    ],
    'required_languages' => ['en'],  // 必填語言
    'default_language' => 'en',
];
```

**配置說明**：
- `supported_languages`: 系統支持的所有語言及其顯示名稱
- `required_languages`: 創建或編輯記錄時必須填充的語言
- `default_language`: 默認語言

## 正確性屬性

正確性屬性是應該在系統的所有有效執行中保持真實的特性——本質上是關於系統應該做什麼的正式陳述。屬性充當人類可讀規範和機器可驗證正確性保證之間的橋樑。

### 屬性 1：手冊列表完整性

*對於任何創建的手冊，它應該出現在手冊列表中*

**驗證：需求 1.1**

### 屬性 2：手冊創建成功

*對於任何有效的手冊數據，創建後應該能夠檢索到相同的數據*

**驗證：需求 1.3**

### 屬性 3：手冊唯一性

*對於任何兩個不同的手冊記錄，它們的 url_slug 應該不同*

**驗證：需求 1.4, 5.1**

### 屬性 4：手冊編輯持久性

*對於任何手冊，編輯後的字段應該被正確保存並在後續查詢中返回*

**驗證：需求 1.5**

### 屬性 5：手冊發布狀態

*對於任何手冊，is_public 字段應該能夠被設置為 true 或 false，並在後續查詢中返回正確的值*

**驗證：需求 1.5, 1.8, 1.9**

### 屬性 6：級聯刪除一致性

*當刪除手冊時，所有相關的菜單項、頁面信息和頁面內容應該被自動刪除*

**驗證：需求 1.6, 3.4**

### 屬性 6：級聯刪除一致性

*當刪除手冊時，所有相關的菜單項、頁面信息和頁面內容應該被自動刪除*

**驗證：需求 1.6, 3.4**

### 屬性 7：菜單樹狀完整性

*對於任何菜單項，其所有祖先和後代應該通過 ClosureTable 正確記錄，並且樹狀結構應該保持無環*

**驗證：需求 2.1, 2.3, 2.4**

### 屬性 8：根菜單項創建

*對於任何創建的根菜單項，它應該沒有父項並出現在菜單樹的頂層*

**驗證：需求 2.2**

### 屬性 9：子菜單項創建

*對於任何創建的子菜單項，它應該有正確的父項並出現在父項的子項列表中*

**驗證：需求 2.3**

### 屬性 10：菜單項移動

*當菜單項被移動到新的父項時，其所有後代應該隨之移動，並且樹狀結構應該保持一致*

**驗證：需求 2.4**

### 屬性 11：菜單項刪除

*當菜單項被刪除時，它應該從樹中移除，並且其後代應該根據配置的行為被處理*

**驗證：需求 2.5**

### 屬性 12：菜單項編輯

*對於任何菜單項，編輯後的字段應該被正確保存並在後續查詢中返回*

**驗證：需求 2.6**

### 屬性 13：菜單項驗證 - External

*對於任何 click_action 為 "external" 的菜單項，url 字段應該不為空且 manual_page_info_id 應該為空*

**驗證：需求 2.8, 5.4**

### 屬性 14：菜單項驗證 - Page

*對於任何 click_action 為 "page" 的菜單項，manual_page_info_id 應該不為空且 url 應該為空*

**驗證：需求 2.9, 5.5**

### 屬性 15：菜單項驗證 - Expand

*對於任何 click_action 為 "expand" 的菜單項，url 和 manual_page_info_id 都應該為空*

**驗證：需求 2.10, 5.6**

### 屬性 16：頁面信息創建

*對於任何創建的頁面信息，它應該與指定的手冊關聯*

**驗證：需求 3.1**

### 屬性 17：頁面信息多語言支持

*對於任何頁面信息，可翻譯字段應該支持多語言輸入並通過 spatie/laravel-translatable 存儲為 JSON*

**驗證：需求 3.2, 6.2**

### 屬性 18：頁面信息編輯

*對於任何頁面信息，編輯後的字段應該被正確保存並在後續查詢中返回*

**驗證：需求 3.3**

### 屬性 19：頁面信息列表

*對於任何手冊，其所有頁面信息應該出現在頁面信息列表中*

**驗證：需求 3.5**

### 屬性 20：頁面內容創建

*對於任何創建的頁面內容，它應該與指定的頁面信息和語言代碼關聯*

**驗證：需求 4.1**

### 屬性 21：富文本編輯器支持

*對於任何頁面內容編輯表單，系統應該提供 CKEditor 富文本編輯器*

**驗證：需求 4.2**

### 屬性 22：文件管理器集成

*對於任何 CKEditor 實例，系統應該提供文件管理器以插入圖片*

**驗證：需求 4.3**

### 屬性 23：圖片路徑靈活性

*對於任何通過文件管理器插入的圖片，其路徑應該通過 config/manual.php 中的 image_base_url 進行管理，支持相對路徑和 CDN URL*

**驗證：需求 4.4**

### 屬性 24：頁面內容編輯

*對於任何頁面內容，編輯後的內容應該被正確保存並在後續查詢中返回*

**驗證：需求 4.5**

### 屬性 25：頁面內容語言版本

*對於任何頁面信息，所有可用的語言版本應該能夠被檢索*

**驗證：需求 4.6**

### 屬性 26：頁面內容語言唯一性

*對於任何頁面信息記錄，每種語言最多只能有一個頁面內容記錄*

**驗證：需求 4.7**

### 屬性 27：頁面內容刪除

*當特定語言的頁面內容被刪除時，只有該語言版本應該被移除，其他語言版本應該保留*

**驗證：需求 4.8**

### 屬性 28：頁面內容列表

*對於任何頁面信息，所有頁面內容應該出現在頁面內容列表中，並按語言分組*

**驗證：需求 4.9**

### 屬性 29：URL Slug 格式

*對於任何手冊的 url_slug，它應該只包含字母數字字符、連字符和下劃線*

**驗證：需求 5.2**

### 屬性 30：菜單項名稱驗證

*對於任何菜單項，可翻譯的 name 字段應該在至少一種語言中不為空*

**驗證：需求 5.3**

### 屬性 31：頁面信息標題驗證

*對於任何頁面信息，可翻譯的 title 字段應該在至少一種語言中不為空*

**驗證：需求 5.7**

### 屬性 32：頁面內容非空驗證

*對於任何頁面內容，content 字段應該不為空*

**驗證：需求 5.8**

### 屬性 33：語言代碼有效性

*對於任何頁面內容記錄，其語言代碼應該符合應用程序支持的語言格式（由 Laravel 的 locale 配置決定）*

**驗證：需求 5.9**

### 屬性 34：多語言字段部分填充

*對於任何可翻譯字段，系統應該允許提交，只要所有 required_languages 中的語言都有內容*

**驗證：需求 6.4, 7.5**

### 屬性 35：配置文件讀取

*系統應該能夠正確讀取 config/manual.php 中的 supported_languages 和 required_languages 配置*

**驗證：需求 7.1, 7.2**

### 屬性 36：語言字段生成

*對於任何創建或編輯表單，系統應該根據 supported_languages 配置生成相應的語言輸入字段*

**驗證：需求 7.3**

### 屬性 37：必填語言標記

*對於任何創建或編輯表單，系統應該根據 required_languages 配置標記必填語言字段*

**驗證：需求 7.4**

### 屬性 38：操作反饋

*對於任何成功的操作，系統應該顯示成功消息；對於任何失敗的操作，系統應該顯示錯誤消息*

**驗證：需求 8.3**

## 錯誤處理

### 驗證錯誤

系統應該在以下情況下返回驗證錯誤：

1. **重複 URL Slug**：當嘗試創建或更新手冊時，如果 url_slug 已存在
   - 錯誤消息：「此 URL 別名已被使用」
   - HTTP 狀態碼：422 Unprocessable Entity

2. **無效 URL Slug 格式**：當 url_slug 包含不允許的字符
   - 錯誤消息：「URL 別名只能包含字母、數字、連字符和下劃線」
   - HTTP 狀態碼：422 Unprocessable Entity

3. **缺少必需的多語言內容**：當可翻譯字段在所有語言中都為空
   - 錯誤消息：「至少需要一種語言的內容」
   - HTTP 狀態碼：422 Unprocessable Entity

4. **無效的 Click Action**：當菜單項的 click_action 為 "navigate" 但 url 為空
   - 錯誤消息：「導航菜單項必須指定 URL」
   - HTTP 狀態碼：422 Unprocessable Entity

5. **重複的語言內容**：當嘗試為同一頁面創建相同語言的內容
   - 錯誤消息：「此語言的內容已存在」
   - HTTP 狀態碼：422 Unprocessable Entity

6. **無效的語言代碼**：當指定的語言代碼不在支持的語言列表中
   - 錯誤消息：「不支持的語言代碼」
   - HTTP 狀態碼：422 Unprocessable Entity

### 業務邏輯錯誤

1. **記錄不存在**：當嘗試訪問不存在的記錄
   - 錯誤消息：「記錄未找到」
   - HTTP 狀態碼：404 Not Found

2. **無權限操作**：當用戶沒有執行操作的權限
   - 錯誤消息：「您沒有權限執行此操作」
   - HTTP 狀態碼：403 Forbidden

3. **樹狀結構操作失敗**：當菜單項移動或刪除操作失敗
   - 錯誤消息：「菜單結構操作失敗，請重試」
   - HTTP 狀態碼：500 Internal Server Error

## 測試策略

### 單元測試

單元測試應該驗證特定的例子、邊界情況和錯誤條件：

1. **Manual 模型測試**
   - 創建手冊並驗證多語言字段
   - 驗證 url_slug 唯一性約束
   - 驗證級聯刪除

2. **ManualMenu 模型測試**
   - 創建根菜單項和子菜單項
   - 驗證樹狀結構操作（移動、刪除）
   - 驗證 click_action 和 url 的驗證規則

3. **ManualPageInfo 模型測試**
   - 創建頁面信息並驗證多語言字段
   - 驗證與手冊的關係

4. **ManualPageContent 模型測試**
   - 創建頁面內容並驗證語言代碼
   - 驗證複合唯一索引

5. **Controller 測試**
   - 驗證 CRUD 操作的響應
   - 驗證驗證錯誤消息
   - 驗證權限檢查

### 屬性測試

屬性測試應該驗證通用屬性在所有輸入上成立：

1. **屬性 1：手冊唯一性**
   - 生成隨機手冊數據
   - 驗證 url_slug 在所有手冊中是唯一的

2. **屬性 2：菜單樹狀完整性**
   - 生成隨機菜單結構
   - 驗證所有祖先和後代關係正確記錄
   - 驗證樹狀結構無環

3. **屬性 3：菜單項驗證**
   - 生成隨機菜單項
   - 驗證 click_action 和 url 的驗證規則

4. **屬性 4：多語言內容完整性**
   - 生成隨機多語言數據
   - 驗證至少一種語言有內容
   - 驗證 JSON 存儲格式

5. **屬性 5：頁面內容語言唯一性**
   - 生成隨機頁面內容
   - 驗證每種語言最多一個內容記錄

6. **屬性 6：級聯刪除一致性**
   - 創建完整的手冊結構
   - 刪除手冊並驗證所有相關記錄被刪除

7. **屬性 7：URL Slug 格式**
   - 生成隨機 url_slug
   - 驗證格式符合規則

8. **屬性 8：語言代碼有效性**
   - 生成隨機語言代碼
   - 驗證代碼在支持的語言列表中

### 測試配置

- 最少 100 次迭代每個屬性測試
- 使用 Pest 或 PHPUnit 進行單元測試
- 使用 Pest 的屬性測試功能進行屬性測試
- 每個測試應該有清晰的失敗消息


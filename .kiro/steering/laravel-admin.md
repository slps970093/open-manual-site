---
inclusion: always
---

# Laravel-Admin 開發指南

## 1. 命名規範

### Controller 命名
- **位置**: `app/Admin/Controllers/{ResourceName}Controller.php`
- 命名格式: `{ModelName}Controller` (PascalCase)
- 範例: `UserController`, `ProductController`, `OrderController`
- 所有 Admin Controller 應繼承 `Ladmin\Controllers\AdminController`
- 使用命令建立: `php artisan admin:make UserController --model=App\\User`

### Model 命名
- **位置**: `app/Models/{ModelName}.php`
- 命名格式: 單數 PascalCase
- 範例: `User`, `Product`, `Order`
- 與資料表名稱對應 (Model: `User` → Table: `users`)

### 資料表和欄位
- **資料表**: 複數小寫 snake_case
- **欄位**: 小寫 snake_case
- 範例: `users` 表, `first_name`, `last_name`, `created_at`

---

## 2. 目錄結構

```
app/Admin/
├── Controllers/                    # Admin 控制器
│   ├── HomeController.php          # 後台首頁
│   ├── UserController.php
│   ├── ProductController.php
│   └── OrderController.php
├── bootstrap.php                   # laravel-admin 啟動檔案
└── routes.php                      # Admin 路由配置

app/Models/
├── User.php
├── Product.php
└── Order.php
```

### 核心檔案說明

- **app/Admin/Controllers/**: 存放所有 Admin 控制器
  - 每個控制器對應一個 Model 的 CRUD 操作
  - 包含 `grid()`、`form()`、`detail()` 三個方法

- **app/Admin/routes.php**: 定義 Admin 路由
  - 使用 `$router->resource()` 註冊資源路由
  - 範例: `$router->resource('users', UserController::class);`

- **app/Admin/bootstrap.php**: laravel-admin 啟動檔案
  - 用於全域配置和初始化

### 檔案組織原則

1. 一個 Controller 對應一個 Model
2. Controller 中的三個方法分別處理列表、詳情、表單邏輯
3. 複雜的業務邏輯應提取到 Model 或 Service 層
4. 保持 Controllers 目錄結構扁平

---

## 3. 自定義頁面

### 基本結構

自定義頁面使用 `Ladmin\Layout\Content` 類來實現內容區的布局。

```php
use Ladmin\Layout\Content;

public function customPage(Content $content)
{
    // 設定頁面標題
    $content->title('頁面標題');
    
    // 設定頁面描述
    $content->description('頁面描述');
    
    // 添加麵包屑導航
    $content->breadcrumb(
        ['text' => '首頁', 'url' => '/admin'],
        ['text' => '自定義頁面']
    );
    
    // 添加頁面內容
    $content->body('Hello World');
    
    return $content;
}
```

### 渲染視圖

使用 `view()` 方法直接渲染 Blade 視圖：

```php
public function show($id, Content $content)
{
    $data = Model::find($id);
    
    return $content->title('詳情')
        ->description('簡介')
        ->view('admin.show', $data->toArray());
}
```

對應的視圖檔案 `resources/views/admin/show.blade.php`：

```blade
<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title">詳情</h3>
    </div>
    <div class="box-body">
        <dl class="dl-horizontal">
            <dt>名稱</dt>
            <dd>{{ $name }}</dd>
        </dl>
    </div>
</div>
```

### 布局系統

laravel-admin 使用 Bootstrap 的 12 欄柵格系統進行布局。

**單行內容**：
```php
$content->row('Hello World');
```

**多列布局**：
```php
$content->row(function(Row $row) {
    $row->column(4, 'Left');      // 4 欄寬
    $row->column(4, 'Center');    // 4 欄寬
    $row->column(4, 'Right');     // 4 欄寬
});
```

**不等寬布局**：
```php
$content->row(function(Row $row) {
    $row->column(3, 'Sidebar');
    $row->column(9, 'Main Content');
});
```

**嵌套布局**：
```php
$content->row(function (Row $row) {
    $row->column(4, 'Left');
    
    $row->column(8, function (Column $column) {
        $column->row('Top');
        $column->row('Middle');
        $column->row('Bottom');
    });
});
```

### 路由配置

在 `app/Admin/routes.php` 中添加自定義頁面路由：

```php
$router->get('dashboard', 'DashboardController@index');
$router->get('reports', 'ReportController@index');
```

### 視圖檔案位置

- 自定義頁面視圖放在 `resources/views/admin/` 目錄
- 使用 AdminLTE2 框架的樣式和元件
- 參考 AdminLTE2 文檔編寫視圖

---

## 4. 資料表格 (Grid)

使用 `Ladmin\Grid` 類生成基於數據模型的表格。

### 基本使用

```php
use App\Models\User;
use Ladmin\Grid;

$grid = new Grid(new User);

// 添加列
$grid->column('id', 'ID')->sortable();
$grid->column('name', '名稱');
$grid->column('email', '郵箱');
$grid->column('created_at', '建立時間');

// 設定每頁顯示行數
$grid->paginate(15);

return $grid;
```

### 常用方法

**修改顯示輸出**：
```php
$grid->column('status')->display(function($status) {
    return $status ? '啟用' : '禁用';
});

// 顯示關聯數據
$grid->column('user.name')->display(function($name) {
    return $name;
});

// 添加不存在的字段
$grid->column('full_name')->display(function() {
    return $this->first_name . ' ' . $this->last_name;
});
```

**禁用功能**：
```php
$grid->disableCreateButton();    // 禁用建立按鈕
$grid->disablePagination();      // 禁用分頁
$grid->disableFilter();          // 禁用篩選器
$grid->disableExport();          // 禁用匯出
$grid->disableRowSelector();     // 禁用行選擇
$grid->disableActions();         // 禁用行操作列
$grid->disableColumnSelector();  // 禁用欄位選擇器
```

**添加查詢條件**：
```php
$grid->model()->where('status', 1);
$grid->model()->orderBy('id', 'desc');
```

**篩選器**：
```php
$grid->filter(function ($filter) {
    $filter->between('created_at', '建立時間')->datetime();
    $filter->like('name', '名稱');
});
```

### 關聯模型

**一對一**：
```php
$grid->column('profile.age');
$grid->column('profile.gender');
```

**一對多**：
```php
$grid->column('comments', '評論數')->display(function ($comments) {
    return count($comments);
});
```

**多對多**：
```php
$grid->column('roles')->display(function ($roles) {
    return array_map(function ($role) {
        return "<span class='label label-success'>{$role['name']}</span>";
    }, $roles);
});
```

---

## 5. 資料表單 (Form)

使用 `Ladmin\Form` 類生成基於數據模型的表單。

### 基本使用

```php
use App\Models\User;
use Ladmin\Form;

$form = new Form(new User);

// 顯示字段
$form->display('id', 'ID');

// 文本輸入
$form->text('name', '名稱');

// 郵箱輸入
$form->email('email', '郵箱');

// 密碼輸入
$form->password('password', '密碼');

// 下拉選擇
$form->select('role', '角色')->options([
    1 => '管理員',
    2 => '編輯',
    3 => '用戶'
]);

// 文本域
$form->textarea('description', '描述');

// 數字輸入
$form->number('age', '年齡');

// 開關
$form->switch('status', '狀態');

// 日期時間選擇
$form->dateTime('created_at', '建立時間');

return $form;
```

### 表單工具

```php
$form->tools(function (Form\Tools $tools) {
    $tools->disableList();      // 禁用列表按鈕
    $tools->disableDelete();    // 禁用刪除按鈕
    $tools->disableView();      // 禁用查看按鈕
    $tools->add('<a class="btn btn-sm btn-danger">自訂按鈕</a>');
});
```

### 表單腳部

```php
$form->footer(function ($footer) {
    $footer->disableReset();           // 禁用重置按鈕
    $footer->disableSubmit();          // 禁用提交按鈕
    $footer->disableViewCheck();       // 禁用查看 checkbox
    $footer->disableEditingCheck();    // 禁用繼續編輯 checkbox
    $footer->disableCreatingCheck();   // 禁用繼續建立 checkbox
});
```

### 其他方法

```php
$form->ignore(['column1', 'column2']);  // 忽略不需要保存的字段
$form->setWidth(10, 2);                 // 設定表單寬度
$form->isCreating();                    // 判斷是否為建立頁面
$form->isEditing();                     // 判斷是否為編輯頁面
$form->confirm('確定提交嗎？');         // 提交確認
```

---

## 6. 資料詳細 (Show)

使用 `Ladmin\Show` 類顯示數據詳情。

### 基本使用

```php
use App\Models\User;
use Ladmin\Show;

protected function detail($id)
{
    $show = new Show(User::findOrFail($id));
    
    $show->field('id', 'ID');
    $show->field('name', '名稱');
    $show->field('email', '郵箱');
    $show->field('created_at', '建立時間');
    
    return $show;
}
```

### 快速方式

```php
// 顯示所有字段
$content->body(Admin::show(User::findOrFail($id)));

// 顯示指定字段
$content->body(Admin::show(User::findOrFail($id), ['id', 'name', 'email']));

// 指定字段標籤
$content->body(Admin::show(User::findOrFail($id), [
    'id'    => 'ID',
    'name'  => '名稱',
    'email' => '郵箱'
]));
```

### 常用方法

```php
// 防止 XSS 攻擊，不轉義 HTML
$show->avatar()->unescape()->as(function ($avatar) {
    return "<img src='{$avatar}' />";
});

// 修改面板樣式
$show->panel()
    ->style('danger')
    ->title('用戶基本信息');

// 面板工具設置
$show->panel()
    ->tools(function ($tools) {
        $tools->disableEdit();
        $tools->disableList();
        $tools->disableDelete();
    });
```

---

## 7. 通知 (Notifications)

### Toastr 通知

在頁面右上角顯示浮動提示：

```php
admin_toastr('操作成功', 'success');
admin_toastr('提示信息', 'info');
admin_toastr('警告信息', 'warning');
admin_toastr('錯誤信息', 'error');

// 自訂選項
admin_toastr('消息', 'success', ['timeOut' => 5000]);
```

### Alert 消息

在頁面頂部顯示警告框：

```php
use Ladmin\Layout\Content;

public function index(Content $content)
{
    return $content
        ->withSuccess('標題', '成功消息')
        ->withInfo('標題', '信息消息')
        ->withWarning('標題', '警告消息')
        ->withError('標題', '錯誤消息');
}

// 或使用函數
admin_success('標題', '成功消息');
admin_info('標題', '信息消息');
admin_warning('標題', '警告消息');
admin_error('標題', '錯誤消息');
```

---

## 8. 引入外部 JS/CSS

### 在 bootstrap.php 中引入

```php
use Ladmin\Admin;

// 引入 CSS
Admin::css('/your/css/path/style.css');

// 引入 JavaScript
Admin::js('/your/javascript/path/js.js');

// 引入外部資源
Admin::js('https://cdn.bootcss.com/vue/2.6.10/vue.min.js');

// 設定 favicon
Admin::favicon('/your/favicon/path');
```

### 在頁面中插入代碼

```php
use Ladmin\Admin;

// 插入 JS 腳本
Admin::script('console.log("hello world");');

// 插入 CSS 樣式
Admin::style('.form-control {margin-top: 10px;}');

// 插入 HTML 代碼
Admin::html('<template>...</template>');
```

### 壓縮資源

安裝壓縮庫：
```bash
composer require matthiasmullie/minify --dev
```

執行壓縮命令：
```bash
php artisan admin:minify
```

清理壓縮文件：
```bash
php artisan admin:minify --clear
```

在 `config/admin.php` 中啟用壓縮：
```php
'minify_assets' => true,
```

---

## 9. 用戶角色權限 (RBAC)

### 權限管理

在後台管理頁面 `http://localhost/admin/auth/permissions` 建立權限。

**權限配置**：
- 權限標識 (slug): `create-post`
- 權限名稱: `建立文章`
- HTTP 方法: `GET`
- HTTP 路徑: `/posts` 或 `/posts*` (支持通配符)

### 頁面權限控制

```php
use Ladmin\Auth\Permission;

class PostController extends Controller
{
    public function create()
    {
        // 檢查權限
        Permission::check('create-post');
    }
}
```

### 表格中的權限控制

```php
$grid->actions(function ($actions) {
    // 沒有 delete-image 權限的角色不顯示刪除按鈕
    if (!Admin::user()->can('delete-image')) {
        $actions->disableDelete();
    }
});

// 只有具有 view-title-column 權限的用戶才能顯示 title 列
if (Admin::user()->can('view-title-column')) {
    $grid->column('title');
}
```

### 相關方法

```php
// 獲取當前用戶
Admin::user();

// 獲取用戶 ID
Admin::user()->id;

// 獲取用戶角色
Admin::user()->roles;

// 獲取用戶權限
Admin::user()->permissions;

// 檢查角色
Admin::user()->isRole('developer');

// 檢查權限
Admin::user()->can('create-post');
Admin::user()->cannot('delete-post');

// 檢查是否超級管理員
Admin::user()->isAdministrator();

// 檢查是否屬於某些角色
Admin::user()->inRoles(['editor', 'developer']);
```

### 權限中間件

在路由中使用權限中間件：

```php
// 允許 administrator、editor 角色訪問
Route::group([
    'middleware' => 'admin.permission:allow,administrator,editor',
], function ($router) {
    $router->resource('users', UserController::class);
});

// 禁止 developer、operator 角色訪問
Route::group([
    'middleware' => 'admin.permission:deny,developer,operator',
], function ($router) {
    $router->resource('users', UserController::class);
});

// 有特定權限的用戶才能訪問
Route::group([
    'middleware' => 'admin.permission:check,edit-post,create-post,delete-post',
], function ($router) {
    $router->resource('posts', PostController::class);
});
```

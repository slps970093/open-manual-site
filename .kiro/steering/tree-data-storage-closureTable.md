---
inclusion: always
---

# ClosureTable 樹狀結構開發指南

基於 [franzose/ClosureTable](https://github.com/franzose/ClosureTable) 套件的樹狀數據結構實現指南。

## 1. 安裝與配置

### 安裝套件

```bash
composer require franzose/closure-table
```

### 發佈配置文件

```bash
php artisan vendor:publish --provider="Franzose\ClosureTable\ClosureTableServiceProvider"
```

### 配置文件位置

- `config/closure_table.php` - 主配置文件
- 默認表名前綴: `closure_table_` (可自訂)

---

## 2. 模型設置

### 基本模型結構

```php
<?php

namespace App\Models;

use Franzose\ClosureTable\Models\Entity;

class Category extends Entity
{
    protected $table = 'categories';
    
    // 定義閉包表名稱
    protected $closureTable = 'category_closure';
    
    // 可填充字段
    protected $fillable = ['name', 'description', 'slug'];
    
    // 隱藏字段
    protected $hidden = [];
}
```

### 遷移文件

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 主表
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // 閉包表 (自動管理樹狀關係)
        Schema::create('category_closure', function (Blueprint $table) {
            $table->unsignedBigInteger('ancestor');
            $table->unsignedBigInteger('descendant');
            $table->unsignedInteger('depth');
            
            $table->primary(['ancestor', 'descendant']);
            $table->foreign('ancestor')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('descendant')->references('id')->on('categories')->onDelete('cascade');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('category_closure');
        Schema::dropIfExists('categories');
    }
};
```

---

## 3. 基本操作

### 創建根節點

```php
// 方式 1: 直接創建
$root = Category::create([
    'name' => '根分類',
    'slug' => 'root-category',
    'description' => '這是根分類'
]);

// 方式 2: 使用 make 和 save
$root = new Category([
    'name' => '根分類',
    'slug' => 'root-category'
]);
$root->save();
```

### 創建子節點

```php
// 方式 1: 使用 children() 關係
$parent = Category::find(1);
$child = $parent->children()->create([
    'name' => '子分類',
    'slug' => 'child-category'
]);

// 方式 2: 使用 makeChild()
$child = new Category([
    'name' => '子分類',
    'slug' => 'child-category'
]);
$parent->makeChild($child);

// 方式 3: 使用 appendNode()
$child = Category::create([
    'name' => '子分類',
    'slug' => 'child-category'
]);
$parent->appendNode($child);
```

### 查詢樹狀結構

```php
// 獲取所有根節點
$roots = Category::whereIsRoot()->get();

// 獲取所有葉子節點 (沒有子節點)
$leaves = Category::whereIsLeaf()->get();

// 獲取特定節點的所有子節點
$parent = Category::find(1);
$children = $parent->children()->get();

// 獲取所有後代 (包括子節點、孫節點等)
$descendants = $parent->descendants()->get();

// 獲取所有祖先 (包括父節點、祖父節點等)
$ancestors = $parent->ancestors()->get();

// 獲取直接父節點
$parentNode = $parent->parent()->first();

// 獲取所有兄弟節點
$siblings = $parent->siblings()->get();

// 獲取節點深度
$depth = $parent->getDepth();

// 判斷是否為根節點
if ($parent->isRoot()) {
    // ...
}

// 判斷是否為葉子節點
if ($parent->isLeaf()) {
    // ...
}

// 判斷是否為祖先
if ($parent->isAncestorOf($child)) {
    // ...
}

// 判斷是否為後代
if ($child->isDescendantOf($parent)) {
    // ...
}
```

### 修改樹狀結構

```php
// 移動節點到新的父節點
$child = Category::find(5);
$newParent = Category::find(3);
$child->setParent($newParent);

// 將節點變成根節點
$node = Category::find(5);
$node->makeRoot();

// 刪除節點及其所有後代
$node = Category::find(5);
$node->delete();

// 刪除節點但保留其子節點 (提升子節點)
$node = Category::find(5);
$node->deleteWithChildren();
```

---

## 4. 高級查詢

### 獲取樹狀層級結構

```php
// 獲取完整樹狀結構 (包括所有層級)
$tree = Category::whereIsRoot()->with('descendants')->get();

// 獲取特定深度的節點
$level2 = Category::whereDepth(2)->get();

// 獲取指定範圍內的節點
$range = Category::whereBetweenDepth(1, 3)->get();
```

### 分頁查詢

```php
// 獲取根節點並分頁
$roots = Category::whereIsRoot()->paginate(15);

// 獲取特定節點的子節點並分頁
$parent = Category::find(1);
$children = $parent->children()->paginate(15);
```

### 排序

```php
// 按名稱排序
$categories = Category::orderBy('name')->get();

// 按深度排序
$categories = Category::orderBy('depth')->get();

// 自訂排序
$categories = Category::orderByDesc('created_at')->get();
```

---

## 5. 常見模式

### 遞迴構建樹狀 HTML

```php
<?php

namespace App\Services;

use App\Models\Category;

class CategoryTreeBuilder
{
    public static function buildTree($nodes = null, $parentId = null)
    {
        if ($nodes === null) {
            $nodes = Category::all();
        }
        
        $html = '<ul>';
        
        foreach ($nodes as $node) {
            if ($node->parent_id == $parentId) {
                $html .= '<li>';
                $html .= '<a href="/categories/' . $node->id . '">' . $node->name . '</a>';
                
                $children = $nodes->where('parent_id', $node->id);
                if ($children->count()) {
                    $html .= self::buildTree($nodes, $node->id);
                }
                
                $html .= '</li>';
            }
        }
        
        $html .= '</ul>';
        return $html;
    }
    
    // 使用 ClosureTable 的更高效方式
    public static function buildTreeEfficient()
    {
        $roots = Category::whereIsRoot()->with('descendants')->get();
        
        return view('categories.tree', ['roots' => $roots]);
    }
}
```

### 批量操作

```php
// 刪除整個分支
$root = Category::find(1);
$root->delete(); // 刪除該節點及所有後代

// 移動整個分支
$branch = Category::find(5);
$newParent = Category::find(3);
$branch->setParent($newParent);

// 複製分支結構
public function duplicateBranch($nodeId, $newParentId)
{
    $node = Category::find($nodeId);
    $newParent = Category::find($newParentId);
    
    $copy = $node->replicate();
    $copy->setParent($newParent);
    $copy->save();
    
    foreach ($node->children as $child) {
        $this->duplicateBranch($child->id, $copy->id);
    }
}
```

### 性能優化

```php
// 使用 eager loading 避免 N+1 查詢
$categories = Category::with('parent', 'children', 'descendants')->get();

// 只查詢需要的字段
$categories = Category::select('id', 'name', 'parent_id')
    ->with('children:id,name,parent_id')
    ->get();

// 使用查詢緩存
$categories = Category::remember(60)->get();
```

---

## 6. 故障排除

### 常見問題

**Q: 刪除節點時出現外鍵約束錯誤**
A: 確保在遷移中設置了 `onDelete('cascade')`

**Q: 樹狀結構不正確**
A: 檢查 `parent_id` 是否正確設置，使用 `$node->setParent()` 而不是直接修改

**Q: 查詢性能緩慢**
A: 使用 eager loading 和 select 限制字段，避免 N+1 查詢

### 調試

```php
// 查看節點的所有關係
$node = Category::find(1);
dd($node->ancestors, $node->descendants, $node->children);

// 檢查閉包表
DB::table('category_closure')->where('ancestor', 1)->get();
```

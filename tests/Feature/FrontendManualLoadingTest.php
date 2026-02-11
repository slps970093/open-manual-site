<?php

namespace Tests\Feature;

use App\Models\Manual;
use App\Models\ManualMenu;
use App\Models\ManualPageInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendManualLoadingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test loading a public manual by URL slug.
     * Validates: Requirements 1.1, 1.4
     */
    public function test_can_load_public_manual_by_url_slug(): void
    {
        $manual = Manual::create([
            'url_slug' => 'test-manual',
            'name' => ['en' => 'Test Manual', 'zh-TW' => '測試手冊'],
            'description' => ['en' => 'Test Description', 'zh-TW' => '測試描述'],
            'is_public' => true,
        ]);

        $response = $this->get(route('frontend.manual', ['slug' => 'test-manual']));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.manual');
        $response->assertViewHas('manual');
        $this->assertEquals($manual->id, $response->viewData('manual')->id);
    }

    /**
     * Test that non-existent manual returns 404.
     * Validates: Requirements 1.2
     */
    public function test_non_existent_manual_returns_404(): void
    {
        $response = $this->get(route('frontend.manual', ['slug' => 'non-existent']));

        $response->assertStatus(404);
    }

    /**
     * Test that private manual returns 404.
     * Validates: Requirements 1.2
     */
    public function test_private_manual_returns_404(): void
    {
        Manual::create([
            'url_slug' => 'private-manual',
            'name' => ['en' => 'Private Manual'],
            'description' => ['en' => 'Private Description'],
            'is_public' => false,
        ]);

        $response = $this->get(route('frontend.manual', ['slug' => 'private-manual']));

        $response->assertStatus(404);
    }

    /**
     * Test menu tree retrieval with root items.
     * Validates: Requirements 2.1, 2.5, 2.6
     */
    public function test_menu_tree_retrieval_with_root_items(): void
    {
        $manual = Manual::create([
            'url_slug' => 'menu-test',
            'name' => ['en' => 'Menu Test'],
            'description' => ['en' => 'Menu Test Description'],
            'is_public' => true,
        ]);

        // Create root menu items
        $root1 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Root 1', 'zh-TW' => '根菜單 1'],
            'click_action' => 'expand',
            'position' => 1,
        ]);

        $root2 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Root 2', 'zh-TW' => '根菜單 2'],
            'click_action' => 'expand',
            'position' => 2,
        ]);

        $response = $this->get(route('frontend.manual', ['slug' => 'menu-test']));

        $response->assertStatus(200);
        $response->assertViewHas('menus');

        $menus = $response->viewData('menus');
        $this->assertCount(2, $menus);

        // Check that both root menus are present (order may vary)
        $menuIds = $menus->pluck('id')->toArray();
        $this->assertContains($root1->id, $menuIds);
        $this->assertContains($root2->id, $menuIds);
    }

    /**
     * Test menu tree with descendants (nested items).
     * Validates: Requirements 2.1, 2.5, 2.6
     */
    public function test_menu_tree_with_descendants(): void
    {
        $manual = Manual::create([
            'url_slug' => 'nested-menu-test',
            'name' => ['en' => 'Nested Menu Test'],
            'description' => ['en' => 'Nested Menu Test Description'],
            'is_public' => true,
        ]);

        // Create root menu
        $root = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Root', 'zh-TW' => '根菜單'],
            'click_action' => 'expand',
            'position' => 1,
        ]);

        // Create child menu items
        $child1 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Child 1', 'zh-TW' => '子菜單 1'],
            'click_action' => 'expand',
            'parent_id' => $root->id,
            'position' => 1,
        ]);

        $child2 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Child 2', 'zh-TW' => '子菜單 2'],
            'click_action' => 'expand',
            'parent_id' => $root->id,
            'position' => 2,
        ]);

        // Create grandchild
        $grandchild = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Grandchild', 'zh-TW' => '孫菜單'],
            'click_action' => 'expand',
            'parent_id' => $child1->id,
            'position' => 1,
        ]);

        $response = $this->get(route('frontend.manual', ['slug' => 'nested-menu-test']));

        $response->assertStatus(200);
        $response->assertViewHas('menus');

        $menus = $response->viewData('menus');
        $this->assertCount(1, $menus);
        $this->assertEquals($root->id, $menus[0]->id);

        // Verify descendants are loaded
        $descendants = $menus[0]->descendants;
        $this->assertNotNull($descendants);
        $descendantIds = $descendants->pluck('id')->toArray();
        $this->assertContains($child1->id, $descendantIds);
        $this->assertContains($child2->id, $descendantIds);
        $this->assertContains($grandchild->id, $descendantIds);
    }

    /**
     * Test menu tree with page info relationships.
     * Validates: Requirements 2.1, 2.5
     */
    public function test_menu_tree_with_page_info_relationships(): void
    {
        $manual = Manual::create([
            'url_slug' => 'page-info-test',
            'name' => ['en' => 'Page Info Test'],
            'description' => ['en' => 'Page Info Test Description'],
            'is_public' => true,
        ]);

        // Create page info
        $pageInfo = ManualPageInfo::create([
            'manual_id' => $manual->id,
            'title' => ['en' => 'Test Page', 'zh-TW' => '測試頁面'],
            'keyword' => ['en' => 'test', 'zh-TW' => '測試'],
        ]);

        // Create menu item with page info
        $menu = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Test Menu', 'zh-TW' => '測試菜單'],
            'click_action' => 'page',
            'manual_page_info_id' => $pageInfo->id,
            'position' => 1,
        ]);

        $response = $this->get(route('frontend.manual', ['slug' => 'page-info-test']));

        $response->assertStatus(200);
        $response->assertViewHas('menus');

        $menus = $response->viewData('menus');
        $this->assertCount(1, $menus);
        $this->assertNotNull($menus[0]->pageInfo);
        $this->assertEquals($pageInfo->id, $menus[0]->pageInfo->id);
    }

    /**
     * Test language filtering in menu tree.
     * Validates: Requirements 2.1, 2.5
     */
    public function test_menu_tree_language_filtering(): void
    {
        $manual = Manual::create([
            'url_slug' => 'lang-test',
            'name' => ['en' => 'Language Test', 'zh-TW' => '語言測試'],
            'description' => ['en' => 'Language Test Description', 'zh-TW' => '語言測試描述'],
            'is_public' => true,
        ]);

        // Create menu items with multilingual names
        $menu = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'English Menu', 'zh-TW' => '繁體中文菜單'],
            'click_action' => 'expand',
            'position' => 1,
        ]);

        // Test with English locale
        app()->setLocale('en');
        $response = $this->get(route('frontend.manual', ['slug' => 'lang-test']));
        $response->assertStatus(200);
        $menus = $response->viewData('menus');
        $this->assertEquals('English Menu', $menus[0]->getTranslation('name', 'en'));

        // Test with Traditional Chinese locale
        app()->setLocale('zh-TW');
        $response = $this->get(route('frontend.manual', ['slug' => 'lang-test']));
        $response->assertStatus(200);
        $menus = $response->viewData('menus');
        $this->assertEquals('繁體中文菜單', $menus[0]->getTranslation('name', 'zh-TW'));
    }

    /**
     * Test menu tree with different click_action types.
     * Validates: Requirements 2.1, 2.5
     */
    public function test_menu_tree_with_different_click_actions(): void
    {
        $manual = Manual::create([
            'url_slug' => 'click-action-test',
            'name' => ['en' => 'Click Action Test'],
            'description' => ['en' => 'Click Action Test Description'],
            'is_public' => true,
        ]);

        // Create page info for page type menu
        $pageInfo = ManualPageInfo::create([
            'manual_id' => $manual->id,
            'title' => ['en' => 'Test Page'],
            'keyword' => ['en' => 'test'],
        ]);

        // Create menu items with different click_actions
        $expandMenu = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Expand Menu'],
            'click_action' => 'expand',
            'position' => 1,
        ]);

        $pageMenu = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Page Menu'],
            'click_action' => 'page',
            'manual_page_info_id' => $pageInfo->id,
            'position' => 2,
        ]);

        $externalMenu = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'External Menu'],
            'click_action' => 'external',
            'url' => 'https://example.com',
            'position' => 3,
        ]);

        $response = $this->get(route('frontend.manual', ['slug' => 'click-action-test']));

        $response->assertStatus(200);
        $menus = $response->viewData('menus');
        $this->assertCount(3, $menus);

        // Check that all three click actions are present (order may vary)
        $clickActions = $menus->pluck('click_action')->toArray();
        $this->assertContains('expand', $clickActions);
        $this->assertContains('page', $clickActions);
        $this->assertContains('external', $clickActions);
    }

    /**
     * Test menu tree ordering by position.
     * Validates: Requirements 2.1, 2.5
     */
    public function test_menu_tree_ordering_by_position(): void
    {
        $manual = Manual::create([
            'url_slug' => 'position-test',
            'name' => ['en' => 'Position Test'],
            'description' => ['en' => 'Position Test Description'],
            'is_public' => true,
        ]);

        // Create menu items with different positions
        $menu3 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Third Menu'],
            'click_action' => 'expand',
            'position' => 3,
        ]);

        $menu1 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'First Menu'],
            'click_action' => 'expand',
            'position' => 1,
        ]);

        $menu2 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Second Menu'],
            'click_action' => 'expand',
            'position' => 2,
        ]);

        $response = $this->get(route('frontend.manual', ['slug' => 'position-test']));

        $response->assertStatus(200);
        $menus = $response->viewData('menus');
        $this->assertCount(3, $menus);

        // Verify ordering by position
        $this->assertEquals($menu1->id, $menus[0]->id);
        $this->assertEquals($menu2->id, $menus[1]->id);
        $this->assertEquals($menu3->id, $menus[2]->id);
    }

    /**
     * Test empty menu tree.
     * Validates: Requirements 2.1
     */
    public function test_empty_menu_tree(): void
    {
        $manual = Manual::create([
            'url_slug' => 'empty-menu-test',
            'name' => ['en' => 'Empty Menu Test'],
            'description' => ['en' => 'Empty Menu Test Description'],
            'is_public' => true,
        ]);

        $response = $this->get(route('frontend.manual', ['slug' => 'empty-menu-test']));

        $response->assertStatus(200);
        $response->assertViewHas('menus');

        $menus = $response->viewData('menus');
        $this->assertCount(0, $menus);
    }

    /**
     * Test currentLang is passed to view.
     * Validates: Requirements 2.1, 2.5
     */
    public function test_current_lang_passed_to_view(): void
    {
        $manual = Manual::create([
            'url_slug' => 'current-lang-test',
            'name' => ['en' => 'Current Lang Test'],
            'description' => ['en' => 'Current Lang Test Description'],
            'is_public' => true,
        ]);

        app()->setLocale('zh-TW');
        $response = $this->get(route('frontend.manual', ['slug' => 'current-lang-test']));

        $response->assertStatus(200);
        $response->assertViewHas('currentLang');
        $this->assertEquals('zh-TW', $response->viewData('currentLang'));
    }
}

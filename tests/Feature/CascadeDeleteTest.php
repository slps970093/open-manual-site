<?php

namespace Tests\Feature;

use App\Models\Manual;
use App\Models\ManualMenu;
use App\Models\ManualPageInfo;
use App\Models\ManualPageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CascadeDeleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test cascade delete when manual is deleted - deletes all menus.
     */
    public function test_deleting_manual_cascades_to_menus()
    {
        $manual = Manual::create([
            'url_slug' => 'cascade-test',
            'name' => ['en' => 'Cascade Test'],
            'is_public' => true,
        ]);

        $menu1 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Menu 1'],
            'click_action' => 'expand',
        ]);

        $menu2 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Menu 2'],
            'click_action' => 'expand',
        ]);

        $manualId = $manual->id;
        $menu1Id = $menu1->id;
        $menu2Id = $menu2->id;

        // Delete the manual
        $manual->delete();

        // Verify menus are soft deleted
        $this->assertSoftDeleted('manual_menu', ['id' => $menu1Id]);
        $this->assertSoftDeleted('manual_menu', ['id' => $menu2Id]);
    }

    /**
     * Test cascade delete when manual is deleted - deletes all page infos.
     */
    public function test_deleting_manual_cascades_to_page_infos()
    {
        $manual = Manual::create([
            'url_slug' => 'cascade-test',
            'name' => ['en' => 'Cascade Test'],
            'is_public' => true,
        ]);

        $pageInfo1 = ManualPageInfo::create([
            'manual_id' => $manual->id,
            'title' => ['en' => 'Page 1'],
        ]);

        $pageInfo2 = ManualPageInfo::create([
            'manual_id' => $manual->id,
            'title' => ['en' => 'Page 2'],
        ]);

        $manualId = $manual->id;
        $pageInfo1Id = $pageInfo1->id;
        $pageInfo2Id = $pageInfo2->id;

        // Delete the manual
        $manual->delete();

        // Verify page infos are soft deleted
        $this->assertSoftDeleted('manual_page_info', ['id' => $pageInfo1Id]);
        $this->assertSoftDeleted('manual_page_info', ['id' => $pageInfo2Id]);
    }

    /**
     * Test cascade delete when manual is deleted - deletes all page contents.
     */
    public function test_deleting_manual_cascades_to_page_contents()
    {
        $manual = Manual::create([
            'url_slug' => 'cascade-test',
            'name' => ['en' => 'Cascade Test'],
            'is_public' => true,
        ]);

        $pageInfo = ManualPageInfo::create([
            'manual_id' => $manual->id,
            'title' => ['en' => 'Test Page'],
        ]);

        $pageContent1 = ManualPageContent::create([
            'manual_page_info_id' => $pageInfo->id,
            'lang' => 'en',
            'content' => '<p>English content</p>',
        ]);

        $pageContent2 = ManualPageContent::create([
            'manual_page_info_id' => $pageInfo->id,
            'lang' => 'zh-TW',
            'content' => '<p>繁體中文內容</p>',
        ]);

        $pageContent1Id = $pageContent1->id;
        $pageContent2Id = $pageContent2->id;

        // Delete the manual
        $manual->delete();

        // Verify page contents are soft deleted
        $this->assertSoftDeleted('manual_page_content', ['id' => $pageContent1Id]);
        $this->assertSoftDeleted('manual_page_content', ['id' => $pageContent2Id]);
    }

    /**
     * Test cascade delete when page info is deleted - deletes all page contents.
     */
    public function test_deleting_page_info_cascades_to_page_contents()
    {
        $manual = Manual::create([
            'url_slug' => 'cascade-test',
            'name' => ['en' => 'Cascade Test'],
            'is_public' => true,
        ]);

        $pageInfo = ManualPageInfo::create([
            'manual_id' => $manual->id,
            'title' => ['en' => 'Test Page'],
        ]);

        $pageContent1 = ManualPageContent::create([
            'manual_page_info_id' => $pageInfo->id,
            'lang' => 'en',
            'content' => '<p>English content</p>',
        ]);

        $pageContent2 = ManualPageContent::create([
            'manual_page_info_id' => $pageInfo->id,
            'lang' => 'zh-TW',
            'content' => '<p>繁體中文內容</p>',
        ]);

        $pageContent1Id = $pageContent1->id;
        $pageContent2Id = $pageContent2->id;

        // Delete the page info
        $pageInfo->delete();

        // Verify page contents are soft deleted
        $this->assertSoftDeleted('manual_page_content', ['id' => $pageContent1Id]);
        $this->assertSoftDeleted('manual_page_content', ['id' => $pageContent2Id]);
    }

    /**
     * Test soft delete on manual.
     */
    public function test_manual_soft_delete()
    {
        $manual = Manual::create([
            'url_slug' => 'soft-delete-test',
            'name' => ['en' => 'Soft Delete Test'],
            'is_public' => true,
        ]);

        $manualId = $manual->id;

        $manual->delete();

        // Verify manual is soft deleted
        $this->assertSoftDeleted('manual', ['id' => $manualId]);

        // Verify manual is not retrieved by default
        $this->assertNull(Manual::find($manualId));

        // Verify manual can be retrieved with withTrashed
        $this->assertNotNull(Manual::withTrashed()->find($manualId));
    }

    /**
     * Test soft delete on menu.
     */
    public function test_menu_soft_delete()
    {
        $manual = Manual::create([
            'url_slug' => 'soft-delete-test',
            'name' => ['en' => 'Soft Delete Test'],
            'is_public' => true,
        ]);

        $menu = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Test Menu'],
            'click_action' => 'expand',
        ]);

        $menuId = $menu->id;

        $menu->delete();

        // Verify menu is soft deleted
        $this->assertSoftDeleted('manual_menu', ['id' => $menuId]);

        // Verify menu is not retrieved by default
        $this->assertNull(ManualMenu::find($menuId));

        // Verify menu can be retrieved with withTrashed
        $this->assertNotNull(ManualMenu::withTrashed()->find($menuId));
    }

    /**
     * Test soft delete on page info.
     */
    public function test_page_info_soft_delete()
    {
        $manual = Manual::create([
            'url_slug' => 'soft-delete-test',
            'name' => ['en' => 'Soft Delete Test'],
            'is_public' => true,
        ]);

        $pageInfo = ManualPageInfo::create([
            'manual_id' => $manual->id,
            'title' => ['en' => 'Test Page'],
        ]);

        $pageInfoId = $pageInfo->id;

        $pageInfo->delete();

        // Verify page info is soft deleted
        $this->assertSoftDeleted('manual_page_info', ['id' => $pageInfoId]);

        // Verify page info is not retrieved by default
        $this->assertNull(ManualPageInfo::find($pageInfoId));

        // Verify page info can be retrieved with withTrashed
        $this->assertNotNull(ManualPageInfo::withTrashed()->find($pageInfoId));
    }

    /**
     * Test soft delete on page content.
     */
    public function test_page_content_soft_delete()
    {
        $manual = Manual::create([
            'url_slug' => 'soft-delete-test',
            'name' => ['en' => 'Soft Delete Test'],
            'is_public' => true,
        ]);

        $pageInfo = ManualPageInfo::create([
            'manual_id' => $manual->id,
            'title' => ['en' => 'Test Page'],
        ]);

        $pageContent = ManualPageContent::create([
            'manual_page_info_id' => $pageInfo->id,
            'lang' => 'en',
            'content' => '<p>Test content</p>',
        ]);

        $pageContentId = $pageContent->id;

        $pageContent->delete();

        // Verify page content is soft deleted
        $this->assertSoftDeleted('manual_page_content', ['id' => $pageContentId]);

        // Verify page content is not retrieved by default
        $this->assertNull(ManualPageContent::find($pageContentId));

        // Verify page content can be retrieved with withTrashed
        $this->assertNotNull(ManualPageContent::withTrashed()->find($pageContentId));
    }

    /**
     * Test complete cascade delete scenario.
     */
    public function test_complete_cascade_delete_scenario()
    {
        // Create a complete manual structure
        $manual = Manual::create([
            'url_slug' => 'complete-cascade-test',
            'name' => ['en' => 'Complete Cascade Test'],
            'is_public' => true,
        ]);

        // Create menus
        $menu1 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Menu 1'],
            'click_action' => 'expand',
        ]);

        // Create page info
        $pageInfo = ManualPageInfo::create([
            'manual_id' => $manual->id,
            'title' => ['en' => 'Test Page'],
        ]);

        // Create page contents
        $pageContent1 = ManualPageContent::create([
            'manual_page_info_id' => $pageInfo->id,
            'lang' => 'en',
            'content' => '<p>English content</p>',
        ]);

        $pageContent2 = ManualPageContent::create([
            'manual_page_info_id' => $pageInfo->id,
            'lang' => 'zh-TW',
            'content' => '<p>繁體中文內容</p>',
        ]);

        // Store IDs for verification
        $manualId = $manual->id;
        $menu1Id = $menu1->id;
        $pageInfoId = $pageInfo->id;
        $pageContent1Id = $pageContent1->id;
        $pageContent2Id = $pageContent2->id;

        // Delete the manual
        $manual->delete();

        // Verify all related records are soft deleted
        $this->assertSoftDeleted('manual', ['id' => $manualId]);
        $this->assertSoftDeleted('manual_menu', ['id' => $menu1Id]);
        $this->assertSoftDeleted('manual_page_info', ['id' => $pageInfoId]);
        $this->assertSoftDeleted('manual_page_content', ['id' => $pageContent1Id]);
        $this->assertSoftDeleted('manual_page_content', ['id' => $pageContent2Id]);
    }

    /**
     * Test restore functionality after soft delete.
     */
    public function test_restore_after_soft_delete()
    {
        $manual = Manual::create([
            'url_slug' => 'restore-test',
            'name' => ['en' => 'Restore Test'],
            'is_public' => true,
        ]);

        $manualId = $manual->id;

        // Delete the manual
        $manual->delete();

        // Verify manual is soft deleted
        $this->assertSoftDeleted('manual', ['id' => $manualId]);

        // Restore the manual
        Manual::withTrashed()->find($manualId)->restore();

        // Verify manual is restored
        $this->assertDatabaseHas('manual', [
            'id' => $manualId,
            'deleted_at' => null,
        ]);

        // Verify manual can be retrieved normally
        $this->assertNotNull(Manual::find($manualId));
    }
}

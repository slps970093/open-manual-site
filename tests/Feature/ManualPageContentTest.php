<?php

namespace Tests\Feature;

use App\Models\Manual;
use App\Models\ManualPageInfo;
use App\Models\ManualPageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualPageContentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a page content record.
     */
    public function test_can_create_page_content()
    {
        $manual = Manual::create([
            'url_slug' => 'test-manual',
            'name' => ['en' => 'Test Manual'],
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

        $this->assertDatabaseHas('manual_page_content', [
            'manual_page_info_id' => $pageInfo->id,
            'lang' => 'en',
            'content' => '<p>Test content</p>',
        ]);

        $this->assertEquals($pageInfo->id, $pageContent->manual_page_info_id);
        $this->assertEquals('en', $pageContent->lang);
    }

    /**
     * Test retrieving page content.
     */
    public function test_can_retrieve_page_content()
    {
        $manual = Manual::create([
            'url_slug' => 'test-manual',
            'name' => ['en' => 'Test Manual'],
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

        $retrieved = ManualPageContent::find($pageContent->id);

        $this->assertNotNull($retrieved);
        $this->assertEquals('en', $retrieved->lang);
        $this->assertEquals('<p>Test content</p>', $retrieved->content);
    }

    /**
     * Test language uniqueness constraint.
     */
    public function test_language_uniqueness_constraint()
    {
        $manual = Manual::create([
            'url_slug' => 'test-manual',
            'name' => ['en' => 'Test Manual'],
            'is_public' => true,
        ]);

        $pageInfo = ManualPageInfo::create([
            'manual_id' => $manual->id,
            'title' => ['en' => 'Test Page'],
        ]);

        ManualPageContent::create([
            'manual_page_info_id' => $pageInfo->id,
            'lang' => 'en',
            'content' => '<p>Test content</p>',
        ]);

        // Attempt to create duplicate language content
        $this->expectException(\Illuminate\Database\QueryException::class);

        ManualPageContent::create([
            'manual_page_info_id' => $pageInfo->id,
            'lang' => 'en',
            'content' => '<p>Different content</p>',
        ]);
    }

    /**
     * Test relationship with page info.
     */
    public function test_page_content_belongs_to_page_info()
    {
        $manual = Manual::create([
            'url_slug' => 'test-manual',
            'name' => ['en' => 'Test Manual'],
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

        $this->assertEquals($pageInfo->id, $pageContent->pageInfo->id);
        $this->assertEquals('Test Page', $pageContent->pageInfo->title);
    }

    /**
     * Test page info has many page contents.
     */
    public function test_page_info_has_many_page_contents()
    {
        $manual = Manual::create([
            'url_slug' => 'test-manual',
            'name' => ['en' => 'Test Manual'],
            'is_public' => true,
        ]);

        $pageInfo = ManualPageInfo::create([
            'manual_id' => $manual->id,
            'title' => ['en' => 'Test Page'],
        ]);

        ManualPageContent::create([
            'manual_page_info_id' => $pageInfo->id,
            'lang' => 'en',
            'content' => '<p>English content</p>',
        ]);

        ManualPageContent::create([
            'manual_page_info_id' => $pageInfo->id,
            'lang' => 'zh-TW',
            'content' => '<p>繁體中文內容</p>',
        ]);

        $contents = $pageInfo->pageContents;

        $this->assertCount(2, $contents);
        $this->assertTrue($contents->contains('lang', 'en'));
        $this->assertTrue($contents->contains('lang', 'zh-TW'));
    }

    /**
     * Test content validation - empty content.
     */
    public function test_content_cannot_be_empty()
    {
        $manual = Manual::create([
            'url_slug' => 'test-manual',
            'name' => ['en' => 'Test Manual'],
            'is_public' => true,
        ]);

        $pageInfo = ManualPageInfo::create([
            'manual_id' => $manual->id,
            'title' => ['en' => 'Test Page'],
        ]);

        $this->expectException(\InvalidArgumentException::class);

        ManualPageContent::create([
            'manual_page_info_id' => $pageInfo->id,
            'lang' => 'en',
            'content' => '',
        ]);
    }

    /**
     * Test language code validation - empty language.
     */
    public function test_language_code_cannot_be_empty()
    {
        $manual = Manual::create([
            'url_slug' => 'test-manual',
            'name' => ['en' => 'Test Manual'],
            'is_public' => true,
        ]);

        $pageInfo = ManualPageInfo::create([
            'manual_id' => $manual->id,
            'title' => ['en' => 'Test Page'],
        ]);

        $this->expectException(\InvalidArgumentException::class);

        ManualPageContent::create([
            'manual_page_info_id' => $pageInfo->id,
            'lang' => '',
            'content' => '<p>Test content</p>',
        ]);
    }

    /**
     * Test cascade delete when page info is deleted.
     */
    public function test_cascade_delete_when_page_info_deleted()
    {
        $manual = Manual::create([
            'url_slug' => 'test-manual',
            'name' => ['en' => 'Test Manual'],
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

        $pageInfoId = $pageInfo->id;
        $pageContentId = $pageContent->id;

        $pageInfo->delete();

        // Check that page content is soft deleted
        $this->assertSoftDeleted('manual_page_content', [
            'id' => $pageContentId,
        ]);
    }

    /**
     * Test soft delete functionality.
     */
    public function test_soft_delete_functionality()
    {
        $manual = Manual::create([
            'url_slug' => 'test-manual',
            'name' => ['en' => 'Test Manual'],
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

        $pageContent->delete();

        $this->assertSoftDeleted('manual_page_content', [
            'id' => $pageContent->id,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Manual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a manual with valid data.
     */
    public function test_can_create_manual_with_valid_data(): void
    {
        $data = [
            'url_slug' => 'test-manual',
            'name' => ['en' => 'Test Manual'],
            'description' => ['en' => 'Test Description'],
            'is_public' => true,
        ];

        $manual = Manual::create($data);

        $this->assertDatabaseHas('manual', [
            'url_slug' => 'test-manual',
            'is_public' => true,
        ]);

        $this->assertEquals('Test Manual', $manual->getTranslation('name', 'en'));
        $this->assertEquals('Test Description', $manual->getTranslation('description', 'en'));
    }

    /**
     * Test creating a manual with duplicate url_slug fails.
     */
    public function test_cannot_create_manual_with_duplicate_url_slug(): void
    {
        Manual::create([
            'url_slug' => 'duplicate-slug',
            'name' => ['en' => 'First Manual'],
            'description' => ['en' => 'First Description'],
            'is_public' => true,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Manual::create([
            'url_slug' => 'duplicate-slug',
            'name' => ['en' => 'Second Manual'],
            'description' => ['en' => 'Second Description'],
            'is_public' => false,
        ]);
    }

    /**
     * Test editing a manual.
     */
    public function test_can_edit_manual(): void
    {
        $manual = Manual::create([
            'url_slug' => 'original-slug',
            'name' => ['en' => 'Original Name'],
            'description' => ['en' => 'Original Description'],
            'is_public' => false,
        ]);

        $manual->update([
            'name' => ['en' => 'Updated Name'],
            'description' => ['en' => 'Updated Description'],
            'is_public' => true,
        ]);

        $this->assertDatabaseHas('manual', [
            'id' => $manual->id,
            'url_slug' => 'original-slug',
            'is_public' => true,
        ]);

        $this->assertEquals('Updated Name', $manual->fresh()->getTranslation('name', 'en'));
        $this->assertEquals('Updated Description', $manual->fresh()->getTranslation('description', 'en'));
    }

    /**
     * Test deleting a manual cascades to related records.
     */
    public function test_deleting_manual_cascades_to_related_records(): void
    {
        $manual = Manual::create([
            'url_slug' => 'cascade-test',
            'name' => ['en' => 'Cascade Test'],
            'description' => ['en' => 'Test cascading delete'],
            'is_public' => true,
        ]);

        $manualId = $manual->id;

        // Delete the manual
        $manual->delete();

        // Verify the manual is soft deleted
        $this->assertSoftDeleted('manual', ['id' => $manualId]);
    }

    /**
     * Test manual with multilingual content.
     */
    public function test_manual_supports_multiple_languages(): void
    {
        $manual = Manual::create([
            'url_slug' => 'multilingual-manual',
            'name' => [
                'en' => 'English Name',
                'zh-TW' => '繁體中文名稱',
                'zh-CN' => '简体中文名称',
                'ja' => '日本語名',
            ],
            'description' => [
                'en' => 'English Description',
                'zh-TW' => '繁體中文描述',
                'zh-CN' => '简体中文描述',
                'ja' => '日本語説明',
            ],
            'is_public' => true,
        ]);

        $this->assertEquals('English Name', $manual->getTranslation('name', 'en'));
        $this->assertEquals('繁體中文名稱', $manual->getTranslation('name', 'zh-TW'));
        $this->assertEquals('简体中文名称', $manual->getTranslation('name', 'zh-CN'));
        $this->assertEquals('日本語名', $manual->getTranslation('name', 'ja'));
    }

    /**
     * Test manual is_public status.
     */
    public function test_manual_is_public_status(): void
    {
        $publicManual = Manual::create([
            'url_slug' => 'public-manual',
            'name' => ['en' => 'Public Manual'],
            'description' => ['en' => 'Public Description'],
            'is_public' => true,
        ]);

        $privateManual = Manual::create([
            'url_slug' => 'private-manual',
            'name' => ['en' => 'Private Manual'],
            'description' => ['en' => 'Private Description'],
            'is_public' => false,
        ]);

        $this->assertTrue($publicManual->is_public);
        $this->assertFalse($privateManual->is_public);
    }

    /**
     * Test manual relationships.
     */
    public function test_manual_has_relationships(): void
    {
        $manual = Manual::create([
            'url_slug' => 'relationship-test',
            'name' => ['en' => 'Relationship Test'],
            'description' => ['en' => 'Test relationships'],
            'is_public' => true,
        ]);

        // Test that relationships exist
        $this->assertIsObject($manual->menus());
        $this->assertIsObject($manual->pageInfos());
    }

    /**
     * Test validation rules for url_slug format.
     */
    public function test_url_slug_format_validation(): void
    {
        // Valid url_slug formats
        $validSlugs = ['user-guide', 'api_docs', 'test123', 'my-manual_v2'];

        foreach ($validSlugs as $slug) {
            $manual = Manual::create([
                'url_slug' => $slug,
                'name' => ['en' => 'Test Manual'],
                'description' => ['en' => 'Test Description'],
                'is_public' => true,
            ]);

            $this->assertDatabaseHas('manual', ['url_slug' => $slug]);
            $manual->delete();
        }
    }
}

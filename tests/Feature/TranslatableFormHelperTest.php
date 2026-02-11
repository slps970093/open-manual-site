<?php

namespace Tests\Feature;

use App\Admin\Helpers\TranslatableFormHelper;
use App\Models\Manual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslatableFormHelperTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting supported languages from configuration.
     */
    public function test_get_supported_languages(): void
    {
        $languages = TranslatableFormHelper::getSupportedLanguages();

        $this->assertIsArray($languages);
        $this->assertArrayHasKey('en', $languages);
        $this->assertArrayHasKey('zh-TW', $languages);
        $this->assertArrayHasKey('zh-CN', $languages);
        $this->assertArrayHasKey('ja', $languages);
    }

    /**
     * Test getting required languages from configuration.
     */
    public function test_get_required_languages(): void
    {
        $required = TranslatableFormHelper::getRequiredLanguages();

        $this->assertIsArray($required);
        $this->assertContains('en', $required);
    }

    /**
     * Test checking if a language is required.
     */
    public function test_is_language_required(): void
    {
        $this->assertTrue(TranslatableFormHelper::isLanguageRequired('en'));
        $this->assertFalse(TranslatableFormHelper::isLanguageRequired('zh-TW'));
        $this->assertFalse(TranslatableFormHelper::isLanguageRequired('zh-CN'));
        $this->assertFalse(TranslatableFormHelper::isLanguageRequired('ja'));
    }

    /**
     * Test validating required languages with valid data.
     */
    public function test_validate_required_languages_with_valid_data(): void
    {
        $data = [
            'en' => 'English content',
            'zh-TW' => '',
            'zh-CN' => '',
            'ja' => '',
        ];

        $this->assertTrue(TranslatableFormHelper::validateRequiredLanguages($data));
    }

    /**
     * Test validating required languages with invalid data.
     */
    public function test_validate_required_languages_with_invalid_data(): void
    {
        $data = [
            'en' => '',
            'zh-TW' => 'Chinese content',
            'zh-CN' => '',
            'ja' => '',
        ];

        $this->assertFalse(TranslatableFormHelper::validateRequiredLanguages($data));
    }

    /**
     * Test validating required languages with multiple required languages.
     */
    public function test_validate_required_languages_with_multiple_required(): void
    {
        $data = [
            'en' => 'English content',
            'zh-TW' => 'Chinese content',
            'zh-CN' => '',
            'ja' => '',
        ];

        $this->assertTrue(TranslatableFormHelper::validateRequiredLanguages($data));
    }

    /**
     * Test validating required languages with custom required languages.
     */
    public function test_validate_required_languages_with_custom_required(): void
    {
        $data = [
            'en' => '',
            'zh-TW' => 'Chinese content',
            'zh-CN' => '',
            'ja' => '',
        ];

        $customRequired = ['zh-TW'];
        $this->assertTrue(TranslatableFormHelper::validateRequiredLanguages($data, $customRequired));
    }

    /**
     * Test getting language name.
     */
    public function test_get_language_name(): void
    {
        $this->assertEquals('English', TranslatableFormHelper::getLanguageName('en'));
        $this->assertEquals('繁體中文', TranslatableFormHelper::getLanguageName('zh-TW'));
        $this->assertEquals('簡體中文', TranslatableFormHelper::getLanguageName('zh-CN'));
        $this->assertEquals('日本語', TranslatableFormHelper::getLanguageName('ja'));
    }

    /**
     * Test getting language name for non-existent language.
     */
    public function test_get_language_name_for_non_existent_language(): void
    {
        $this->assertNull(TranslatableFormHelper::getLanguageName('fr'));
    }

    /**
     * Test getting all language names.
     */
    public function test_get_language_names(): void
    {
        $names = TranslatableFormHelper::getLanguageNames();

        $this->assertIsArray($names);
        $this->assertArrayHasKey('en', $names);
        $this->assertArrayHasKey('zh-TW', $names);
        $this->assertArrayHasKey('zh-CN', $names);
        $this->assertArrayHasKey('ja', $names);
    }

    /**
     * Test that helper works with translatable models.
     */
    public function test_helper_works_with_translatable_models(): void
    {
        $manual = Manual::create([
            'url_slug' => 'test-manual',
            'name' => [
                'en' => 'English Name',
                'zh-TW' => '繁體中文名稱',
            ],
            'description' => [
                'en' => 'English Description',
                'zh-TW' => '繁體中文描述',
            ],
            'is_public' => true,
        ]);

        // Verify the model has translatable content
        $this->assertEquals('English Name', $manual->getTranslation('name', 'en'));
        $this->assertEquals('繁體中文名稱', $manual->getTranslation('name', 'zh-TW'));

        // Verify helper can validate the data
        $nameData = $manual->getTranslations('name');
        $this->assertTrue(TranslatableFormHelper::validateRequiredLanguages($nameData));
    }

    /**
     * Test validating empty required languages.
     */
    public function test_validate_required_languages_with_empty_array(): void
    {
        $data = [];

        $this->assertFalse(TranslatableFormHelper::validateRequiredLanguages($data));
    }

    /**
     * Test validating required languages with null values.
     */
    public function test_validate_required_languages_with_null_values(): void
    {
        $data = [
            'en' => null,
            'zh-TW' => null,
            'zh-CN' => null,
            'ja' => null,
        ];

        $this->assertFalse(TranslatableFormHelper::validateRequiredLanguages($data));
    }

    /**
     * Test validating required languages with whitespace only.
     */
    public function test_validate_required_languages_with_whitespace_only(): void
    {
        $data = [
            'en' => '   ',
            'zh-TW' => '',
            'zh-CN' => '',
            'ja' => '',
        ];

        // Whitespace is considered as content
        $this->assertTrue(TranslatableFormHelper::validateRequiredLanguages($data));
    }
}

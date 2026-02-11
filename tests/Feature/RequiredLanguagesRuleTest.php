<?php

namespace Tests\Feature;

use App\Rules\RequiredLanguages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequiredLanguagesRuleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test rule passes with valid required language content.
     */
    public function test_rule_passes_with_valid_required_language_content(): void
    {
        $rule = new RequiredLanguages();

        $data = [
            'en' => 'English content',
            'zh-TW' => '',
            'zh-CN' => '',
            'ja' => '',
        ];

        $this->assertTrue($rule->passes('name', $data));
    }

    /**
     * Test rule fails with no required language content.
     */
    public function test_rule_fails_with_no_required_language_content(): void
    {
        $rule = new RequiredLanguages();

        $data = [
            'en' => '',
            'zh-TW' => 'Chinese content',
            'zh-CN' => '',
            'ja' => '',
        ];

        $this->assertFalse($rule->passes('name', $data));
    }

    /**
     * Test rule fails with non-array value.
     */
    public function test_rule_fails_with_non_array_value(): void
    {
        $rule = new RequiredLanguages();

        $this->assertFalse($rule->passes('name', 'string value'));
        $this->assertFalse($rule->passes('name', 123));
        $this->assertFalse($rule->passes('name', null));
    }

    /**
     * Test rule with custom required languages.
     */
    public function test_rule_with_custom_required_languages(): void
    {
        $rule = new RequiredLanguages(['zh-TW']);

        $data = [
            'en' => '',
            'zh-TW' => 'Chinese content',
            'zh-CN' => '',
            'ja' => '',
        ];

        $this->assertTrue($rule->passes('name', $data));
    }

    /**
     * Test rule with custom required languages fails.
     */
    public function test_rule_with_custom_required_languages_fails(): void
    {
        $rule = new RequiredLanguages(['zh-TW']);

        $data = [
            'en' => 'English content',
            'zh-TW' => '',
            'zh-CN' => '',
            'ja' => '',
        ];

        $this->assertFalse($rule->passes('name', $data));
    }

    /**
     * Test rule message.
     */
    public function test_rule_message(): void
    {
        $rule = new RequiredLanguages(['en', 'zh-TW']);

        $message = $rule->message();

        $this->assertStringContainsString('At least one of the required languages', $message);
        $this->assertStringContainsString('en', $message);
        $this->assertStringContainsString('zh-TW', $message);
    }

    /**
     * Test rule with empty array.
     */
    public function test_rule_with_empty_array(): void
    {
        $rule = new RequiredLanguages();

        $data = [];

        $this->assertFalse($rule->passes('name', $data));
    }

    /**
     * Test rule with null values in array.
     */
    public function test_rule_with_null_values_in_array(): void
    {
        $rule = new RequiredLanguages();

        $data = [
            'en' => null,
            'zh-TW' => null,
            'zh-CN' => null,
            'ja' => null,
        ];

        $this->assertFalse($rule->passes('name', $data));
    }

    /**
     * Test rule with zero as content.
     */
    public function test_rule_with_zero_as_content(): void
    {
        $rule = new RequiredLanguages();

        $data = [
            'en' => 0,
            'zh-TW' => '',
            'zh-CN' => '',
            'ja' => '',
        ];

        // Zero is falsy, so it should fail
        $this->assertFalse($rule->passes('name', $data));
    }

    /**
     * Test rule with false as content.
     */
    public function test_rule_with_false_as_content(): void
    {
        $rule = new RequiredLanguages();

        $data = [
            'en' => false,
            'zh-TW' => '',
            'zh-CN' => '',
            'ja' => '',
        ];

        // False is falsy, so it should fail
        $this->assertFalse($rule->passes('name', $data));
    }

    /**
     * Test rule with whitespace as content.
     */
    public function test_rule_with_whitespace_as_content(): void
    {
        $rule = new RequiredLanguages();

        $data = [
            'en' => '   ',
            'zh-TW' => '',
            'zh-CN' => '',
            'ja' => '',
        ];

        // Whitespace is truthy, so it should pass
        $this->assertTrue($rule->passes('name', $data));
    }

    /**
     * Test rule with multiple required languages all having content.
     */
    public function test_rule_with_multiple_required_languages_all_having_content(): void
    {
        $rule = new RequiredLanguages(['en', 'zh-TW']);

        $data = [
            'en' => 'English content',
            'zh-TW' => 'Chinese content',
            'zh-CN' => '',
            'ja' => '',
        ];

        $this->assertTrue($rule->passes('name', $data));
    }

    /**
     * Test rule with multiple required languages only one having content.
     */
    public function test_rule_with_multiple_required_languages_only_one_having_content(): void
    {
        $rule = new RequiredLanguages(['en', 'zh-TW']);

        $data = [
            'en' => 'English content',
            'zh-TW' => '',
            'zh-CN' => '',
            'ja' => '',
        ];

        // At least one required language has content, so it should pass
        $this->assertTrue($rule->passes('name', $data));
    }
}

<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Validation rule to ensure at least one required language has content.
 *
 * Usage:
 * 'name' => [new RequiredLanguages()]
 *
 * This rule checks that for translatable fields, at least one of the
 * required languages (configured in config/manual.php) has content.
 */
class RequiredLanguages implements Rule
{
    /**
     * The required languages.
     *
     * @var array
     */
    protected $requiredLanguages;

    /**
     * Create a new rule instance.
     *
     * @param array|null $requiredLanguages
     */
    public function __construct($requiredLanguages = null)
    {
        $this->requiredLanguages = $requiredLanguages ?? config('manual.required_languages', []);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // If value is not an array, it's invalid
        if (!is_array($value)) {
            return false;
        }

        // Check if at least one required language has content
        foreach ($this->requiredLanguages as $langCode) {
            if (!empty($value[$langCode] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $languages = implode(', ', $this->requiredLanguages);
        return "At least one of the required languages ({$languages}) must have content.";
    }
}

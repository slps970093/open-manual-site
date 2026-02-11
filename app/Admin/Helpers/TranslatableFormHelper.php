<?php

namespace App\Admin\Helpers;

use Ladmin\Form;

/**
 * Helper class for managing translatable form fields in Laravel-Admin.
 *
 * This helper simplifies the process of adding multilingual form fields
 * by automatically handling language configuration and validation rules.
 */
class TranslatableFormHelper
{
    /**
     * Add translatable text fields to a form.
     *
     * @param Form $form
     * @param string $column
     * @param string $label
     * @param array $options
     * @return void
     */
    public static function addTranslatableText(Form $form, $column, $label, $options = [])
    {
        $supportedLanguages = config('manual.supported_languages', []);
        $requiredLanguages = config('manual.required_languages', []);

        foreach ($supportedLanguages as $langCode => $langName) {
            $isRequired = in_array($langCode, $requiredLanguages);
            $rules = $isRequired ? 'required' : 'nullable';

            $fieldLabel = $label . " ({$langName})";
            $fieldName = "{$column}.{$langCode}";

            $field = $form->text($fieldName, $fieldLabel)
                ->rules($rules);

            // Apply additional options if provided
            if (isset($options['help'])) {
                $help = $isRequired
                    ? $options['help'] . ' (Required)'
                    : $options['help'] . ' (Optional)';
                $field->help($help);
            } else {
                $field->help($isRequired ? 'Required' : 'Optional');
            }

            // Apply any other options
            foreach ($options as $key => $value) {
                if ($key !== 'help' && method_exists($field, $key)) {
                    $field->$key($value);
                }
            }
        }
    }

    /**
     * Add translatable textarea fields to a form.
     *
     * @param Form $form
     * @param string $column
     * @param string $label
     * @param array $options
     * @return void
     */
    public static function addTranslatableTextarea(Form $form, $column, $label, $options = [])
    {
        $supportedLanguages = config('manual.supported_languages', []);
        $requiredLanguages = config('manual.required_languages', []);

        foreach ($supportedLanguages as $langCode => $langName) {
            $isRequired = in_array($langCode, $requiredLanguages);
            $rules = $isRequired ? 'required' : 'nullable';

            $fieldLabel = $label . " ({$langName})";
            $fieldName = "{$column}.{$langCode}";

            $field = $form->textarea($fieldName, $fieldLabel)
                ->rules($rules);

            // Apply additional options if provided
            if (isset($options['help'])) {
                $help = $isRequired
                    ? $options['help'] . ' (Required)'
                    : $options['help'] . ' (Optional)';
                $field->help($help);
            } else {
                $field->help($isRequired ? 'Required' : 'Optional');
            }

            // Apply any other options
            foreach ($options as $key => $value) {
                if ($key !== 'help' && method_exists($field, $key)) {
                    $field->$key($value);
                }
            }
        }
    }

    /**
     * Get supported languages configuration.
     *
     * @return array
     */
    public static function getSupportedLanguages()
    {
        return config('manual.supported_languages', []);
    }

    /**
     * Get required languages configuration.
     *
     * @return array
     */
    public static function getRequiredLanguages()
    {
        return config('manual.required_languages', []);
    }

    /**
     * Check if a language is required.
     *
     * @param string $langCode
     * @return bool
     */
    public static function isLanguageRequired($langCode)
    {
        return in_array($langCode, self::getRequiredLanguages());
    }

    /**
     * Validate that at least one required language has content.
     *
     * @param array $translatableData
     * @param array $requiredLanguages
     * @return bool
     */
    public static function validateRequiredLanguages($translatableData, $requiredLanguages = null)
    {
        if ($requiredLanguages === null) {
            $requiredLanguages = self::getRequiredLanguages();
        }

        foreach ($requiredLanguages as $langCode) {
            if (!empty($translatableData[$langCode] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get language display name.
     *
     * @param string $langCode
     * @return string|null
     */
    public static function getLanguageName($langCode)
    {
        $languages = self::getSupportedLanguages();
        return $languages[$langCode] ?? null;
    }

    /**
     * Get all language names.
     *
     * @return array
     */
    public static function getLanguageNames()
    {
        return self::getSupportedLanguages();
    }
}

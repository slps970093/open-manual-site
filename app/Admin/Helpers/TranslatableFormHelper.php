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
     * Render a single tab group where each TAB = one language,
     * and each tab contains ALL the provided fields.
     *
     * $fields format:
     * [
     *   ['column' => 'name',        'label' => 'Name',        'type' => 'text',     'values' => ['en'=>'...']],
     *   ['column' => 'description', 'label' => 'Description', 'type' => 'textarea', 'values' => ['en'=>'...']],
     * ]
     *
     * @param Form   $form
     * @param array  $fields
     * @param string $groupLabel  Label shown above the whole tab group
     * @return void
     */
    public static function addMultiFieldTranslatableTabs(Form $form, array $fields, string $groupLabel = '')
    {
        $form->html(self::renderMultiFieldTabGroup($fields, $groupLabel));
    }

    /**
     * Build the HTML for a language-per-tab group containing multiple fields.
     *
     * @param array  $fields
     * @param string $groupLabel
     * @return string
     */
    public static function renderMultiFieldTabGroup(array $fields, string $groupLabel = ''): string
    {
        $supportedLanguages = config('manual.supported_languages', []);
        $requiredLanguages  = config('manual.required_languages', []);
        $uid = 'trans-group-' . uniqid();

        $html  = '<div class="form-group">';

        if ($groupLabel !== '') {
            $html .= '<label class="col-md-2 control-label">' . e($groupLabel) . '</label>';
            $html .= '<div class="col-md-8">';
        } else {
            $html .= '<div class="col-md-8 col-md-offset-2">';
        }

        $html .= '<div class="nav-tabs-custom" style="margin-bottom:0">';
        $html .= '<ul class="nav nav-tabs">';

        $first = true;
        foreach ($supportedLanguages as $langCode => $langName) {
            $isRequired  = in_array($langCode, $requiredLanguages);
            $activeClass = $first ? 'active' : '';
            $badge       = $isRequired
                ? ' <span class="label label-danger" style="font-size:10px">Required</span>'
                : '';

            $html .= '<li class="' . $activeClass . '">';
            $html .= '<a href="#' . $uid . '-' . $langCode . '" data-toggle="tab">';
            $html .= e($langName) . $badge;
            $html .= '</a></li>';
            $first = false;
        }

        $html .= '</ul>';
        $html .= '<div class="tab-content" style="padding:15px">';

        $first = true;
        foreach ($supportedLanguages as $langCode => $langName) {
            $isRequired  = in_array($langCode, $requiredLanguages);
            $activeClass = $first ? 'active' : '';

            $html .= '<div class="tab-pane ' . $activeClass . '" id="' . $uid . '-' . $langCode . '">';

            foreach ($fields as $field) {
                $column    = $field['column'];
                $label     = $field['label'] ?? $column;
                $type      = $field['type'] ?? 'text';
                $values    = $field['values'] ?? [];
                $help      = $field['help'] ?? null;
                $value     = $values[$langCode] ?? '';
                $nameAttr  = $column . '[' . $langCode . ']';
                $reqAttr   = ($isRequired && ($field['required'] ?? true)) ? ' required' : '';

                $html .= '<div class="form-group" style="margin-bottom:12px">';
                $html .= '<label>' . e($label) . '</label>';

                if ($type === 'textarea') {
                    $html .= '<textarea name="' . e($nameAttr) . '" class="form-control" rows="4"' . $reqAttr . '>';
                    $html .= e($value);
                    $html .= '</textarea>';
                } else {
                    $html .= '<input type="text" name="' . e($nameAttr) . '" class="form-control" value="' . e($value) . '"' . $reqAttr . '>';
                }

                if ($help) {
                    $html .= '<p class="help-block" style="margin-bottom:0">' . e($help) . '</p>';
                }

                $html .= '</div>';
            }

            $html .= '</div>'; // .tab-pane
            $first = false;
        }

        $html .= '</div>'; // .tab-content
        $html .= '</div>'; // .nav-tabs-custom
        $html .= '</div>'; // .col
        $html .= '</div>'; // .form-group

        return $html;
    }

    /**
     * Add a translatable text field as a TAB group via $form->html().
     * All language inputs are rendered inside one tab group and submitted together.
     *
     * @param Form   $form
     * @param string $column
     * @param string $label
     * @param array  $currentValues  Associative array ['langCode' => 'value']
     * @param array  $options        ['help' => string]
     * @return void
     */
    public static function addTranslatableTextTab(Form $form, $column, $label, $currentValues = [], $options = [])
    {
        $form->html(self::renderTabGroup($column, $label, 'text', $currentValues, $options));
    }

    /**
     * Add a translatable textarea field as a TAB group via $form->html().
     *
     * @param Form   $form
     * @param string $column
     * @param string $label
     * @param array  $currentValues
     * @param array  $options
     * @return void
     */
    public static function addTranslatableTextareaTab(Form $form, $column, $label, $currentValues = [], $options = [])
    {
        $form->html(self::renderTabGroup($column, $label, 'textarea', $currentValues, $options));
    }

    /**
     * Render a Bootstrap tab group for a single translatable field (one field, tabs per language).
     *
     * @param string $column
     * @param string $label
     * @param string $inputType  'text' | 'textarea'
     * @param array  $currentValues
     * @param array  $options
     * @return string
     */
    public static function renderTabGroup($column, $label, $inputType = 'text', $currentValues = [], $options = [])
    {
        $supportedLanguages = config('manual.supported_languages', []);
        $requiredLanguages  = config('manual.required_languages', []);
        $helpText           = $options['help'] ?? null;

        $uid = 'trans-' . $column . '-' . uniqid();

        $html  = '<div class="form-group">';
        $html .= '<label class="col-md-2 control-label">' . e($label) . '</label>';
        $html .= '<div class="col-md-8">';
        $html .= '<div class="nav-tabs-custom" style="margin-bottom:0">';
        $html .= '<ul class="nav nav-tabs">';

        $first = true;
        foreach ($supportedLanguages as $langCode => $langName) {
            $isRequired  = in_array($langCode, $requiredLanguages);
            $activeClass = $first ? 'active' : '';
            $badge       = $isRequired
                ? ' <span class="label label-danger" style="font-size:10px">Required</span>'
                : '';

            $html .= '<li class="' . $activeClass . '">';
            $html .= '<a href="#' . $uid . '-' . $langCode . '" data-toggle="tab">';
            $html .= e($langName) . $badge;
            $html .= '</a></li>';
            $first = false;
        }

        $html .= '</ul>';
        $html .= '<div class="tab-content" style="padding:10px">';

        $first = true;
        foreach ($supportedLanguages as $langCode => $langName) {
            $isRequired  = in_array($langCode, $requiredLanguages);
            $activeClass = $first ? 'active' : '';
            $value       = $currentValues[$langCode] ?? '';
            $nameAttr    = $column . '[' . $langCode . ']';
            $reqAttr     = $isRequired ? ' required' : '';

            $html .= '<div class="tab-pane ' . $activeClass . '" id="' . $uid . '-' . $langCode . '">';

            if ($inputType === 'textarea') {
                $html .= '<textarea name="' . e($nameAttr) . '" class="form-control" rows="4"' . $reqAttr . '>';
                $html .= e($value);
                $html .= '</textarea>';
            } else {
                $html .= '<input type="text" name="' . e($nameAttr) . '" class="form-control" value="' . e($value) . '"' . $reqAttr . '>';
            }

            $html .= '</div>';
            $first = false;
        }

        $html .= '</div>'; // .tab-content
        $html .= '</div>'; // .nav-tabs-custom

        if ($helpText) {
            $html .= '<p class="help-block">' . e($helpText) . '</p>';
        }

        $html .= '</div>'; // .col-md-8
        $html .= '</div>'; // .form-group

        return $html;
    }

    /**
     * Add translatable text fields to a form (legacy vertical layout).
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

            if (isset($options['help'])) {
                $help = $isRequired
                    ? $options['help'] . ' (Required)'
                    : $options['help'] . ' (Optional)';
                $field->help($help);
            } else {
                $field->help($isRequired ? 'Required' : 'Optional');
            }

            foreach ($options as $key => $value) {
                if ($key !== 'help' && method_exists($field, $key)) {
                    $field->$key($value);
                }
            }
        }
    }

    /**
     * Add translatable textarea fields to a form (legacy vertical layout).
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

            if (isset($options['help'])) {
                $help = $isRequired
                    ? $options['help'] . ' (Required)'
                    : $options['help'] . ' (Optional)';
                $field->help($help);
            } else {
                $field->help($isRequired ? 'Required' : 'Optional');
            }

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

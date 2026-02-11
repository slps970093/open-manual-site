<?php

namespace App\Admin\Forms\Fields;

use Ladmin\Form\Field;

/**
 * Custom Laravel-Admin form field for translatable (multilingual) input.
 *
 * This field automatically generates input fields for all supported languages
 * and marks required languages as mandatory.
 *
 * Usage:
 * $form->translatable('name', 'Name')
 *      ->rules('required')
 *      ->help('Enter the name in all supported languages');
 */
class Translatable extends Field
{
    /**
     * The field type.
     *
     * @var string
     */
    protected $view = 'admin.forms.translatable';

    /**
     * The input type.
     *
     * @var string
     */
    protected $inputType = 'text';

    /**
     * Supported languages configuration.
     *
     * @var array
     */
    protected $supportedLanguages = [];

    /**
     * Required languages configuration.
     *
     * @var array
     */
    protected $requiredLanguages = [];

    /**
     * Initialize the field.
     */
    public function __construct($column, $label = '')
    {
        parent::__construct($column, $label);

        // Load configuration
        $this->supportedLanguages = config('manual.supported_languages', []);
        $this->requiredLanguages = config('manual.required_languages', []);

        // Set default value
        $this->value = [];
    }

    /**
     * Set the input type (text, textarea, etc.).
     *
     * @param string $type
     * @return $this
     */
    public function setInputType($type)
    {
        $this->inputType = $type;
        return $this;
    }

    /**
     * Get the supported languages.
     *
     * @return array
     */
    public function getSupportedLanguages()
    {
        return $this->supportedLanguages;
    }

    /**
     * Get the required languages.
     *
     * @return array
     */
    public function getRequiredLanguages()
    {
        return $this->requiredLanguages;
    }

    /**
     * Check if a language is required.
     *
     * @param string $langCode
     * @return bool
     */
    public function isLanguageRequired($langCode)
    {
        return in_array($langCode, $this->requiredLanguages);
    }

    /**
     * Prepare for rendering.
     *
     * @param mixed $value
     * @return void
     */
    public function prepare($value)
    {
        // Get the current value from the model
        if ($this->form->model() && method_exists($this->form->model(), 'getTranslations')) {
            $translations = $this->form->model()->getTranslations($this->column);
            $this->value = $translations ?: [];
        } else {
            $this->value = $value ?: [];
        }

        // Ensure all languages have a key in the value array
        foreach ($this->supportedLanguages as $langCode => $langName) {
            if (!isset($this->value[$langCode])) {
                $this->value[$langCode] = '';
            }
        }
    }

    /**
     * Render the field.
     *
     * @return string
     */
    public function render()
    {
        $this->prepare($this->value);

        $html = '<div class="form-group">';
        $html .= '<label class="control-label">' . $this->label . '</label>';
        $html .= '<div class="nav-tabs-custom">';
        $html .= '<ul class="nav nav-tabs">';

        // Render tabs
        $first = true;
        foreach ($this->supportedLanguages as $langCode => $langName) {
            $active = $first ? 'active' : '';
            $isRequired = $this->isLanguageRequired($langCode);
            $requiredBadge = $isRequired ? ' <span class="label label-danger">Required</span>' : '';

            $html .= '<li class="' . $active . '">';
            $html .= '<a href="#' . $this->column . '-' . $langCode . '" data-toggle="tab">';
            $html .= $langName . $requiredBadge;
            $html .= '</a>';
            $html .= '</li>';

            $first = false;
        }

        $html .= '</ul>';
        $html .= '<div class="tab-content">';

        // Render content tabs
        $first = true;
        foreach ($this->supportedLanguages as $langCode => $langName) {
            $active = $first ? 'active' : '';
            $isRequired = $this->isLanguageRequired($langCode);
            $requiredAttr = $isRequired ? 'required' : '';
            $value = $this->value[$langCode] ?? '';

            $html .= '<div class="tab-pane ' . $active . '" id="' . $this->column . '-' . $langCode . '">';

            if ($this->inputType === 'textarea') {
                $html .= '<textarea ';
                $html .= 'name="' . $this->column . '[' . $langCode . ']" ';
                $html .= 'class="form-control" ';
                $html .= 'rows="5" ';
                $html .= $requiredAttr . '>';
                $html .= htmlspecialchars($value);
                $html .= '</textarea>';
            } else {
                $html .= '<input ';
                $html .= 'type="' . $this->inputType . '" ';
                $html .= 'name="' . $this->column . '[' . $langCode . ']" ';
                $html .= 'class="form-control" ';
                $html .= 'value="' . htmlspecialchars($value) . '" ';
                $html .= $requiredAttr . '>';
            }

            $html .= '</div>';
            $first = false;
        }

        $html .= '</div>';
        $html .= '</div>';

        // Add help text if provided
        if ($this->help) {
            $html .= '<p class="help-block">' . $this->help . '</p>';
        }

        $html .= '</div>';

        return $html;
    }
}

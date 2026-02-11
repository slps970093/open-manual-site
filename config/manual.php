<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | This array defines all languages supported by the manual system.
    | The key is the language code (e.g., 'en', 'zh-TW') and the value
    | is the display name of the language.
    |
    */
    'supported_languages' => [
        'zh-TW' => '繁體中文',
        'en' => 'English',
        'zh-CN' => '簡體中文',
        'ja' => '日本語',
        'ko' => '한국어',
        'es' => 'Español',
        'fr' => 'Français',
        'de' => 'Deutsch',
        'ru' => 'Русский',
        'ar' => 'العربية',
    ],

    /*
    |--------------------------------------------------------------------------
    | Required Languages
    |--------------------------------------------------------------------------
    |
    | This array defines which languages are required when creating or
    | editing translatable content. At least one of these languages must
    | have content for the record to be valid.
    |
    */
    'required_languages' => ['en'],

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | The default language to use when displaying content. This should be
    | one of the keys from the 'supported_languages' array.
    |
    */
    'default_language' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Image Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for manual images. This can be a relative path or a
    | full CDN URL. Images uploaded through the file manager will use
    | this base URL.
    |
    | Examples:
    | - Relative path: '/storage/manual-images/'
    | - CDN URL: 'https://cdn.example.com/manual-images/'
    |
    */
    'image_base_url' => env('MANUAL_IMAGE_BASE_URL', '/storage/manual-images/'),
];

@props(['id' => 'languageSelector', 'size' => 'sm'])

<div class="d-flex align-items-center gap-2">
    <span class="text-muted small">語言:</span>
    <select class="form-select form-select-{{ $size }} language-selector" id="{{ $id }}" style="width: auto;" aria-label="選擇語言">
        <option value="zh-TW" {{ app()->getLocale() === 'zh-TW' ? 'selected' : '' }}>繁體中文</option>
        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>English</option>
        <option value="zh-CN" {{ app()->getLocale() === 'zh-CN' ? 'selected' : '' }}>简体中文</option>
        <option value="ja" {{ app()->getLocale() === 'ja' ? 'selected' : '' }}>日本語</option>
        <option value="ko" {{ app()->getLocale() === 'ko' ? 'selected' : '' }}>한국어</option>
        <option value="es" {{ app()->getLocale() === 'es' ? 'selected' : '' }}>Español</option>
        <option value="fr" {{ app()->getLocale() === 'fr' ? 'selected' : '' }}>Français</option>
        <option value="de" {{ app()->getLocale() === 'de' ? 'selected' : '' }}>Deutsch</option>
        <option value="ru" {{ app()->getLocale() === 'ru' ? 'selected' : '' }}>Русский</option>
        <option value="ar" {{ app()->getLocale() === 'ar' ? 'selected' : '' }}>العربية</option>
    </select>
</div>

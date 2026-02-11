<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author leeqvip <https://github.com/leeqvip>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Ladmin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Ladmin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

Ladmin\Form::forget(['map', 'editor']);
// Register custom translatable form field for multilingual support
Ladmin\Form::extend('translatable', \App\Admin\Forms\Fields\Translatable::class);

// Add HugerTE CSS and JS
Ladmin\Admin::css('//cdn.jsdelivr.net/npm/hugerte@1.0.9/skins/ui/oxide/skin.min.css');
Ladmin\Admin::js('//cdn.jsdelivr.net/npm/hugerte@1.0.9/hugerte.min.js');

// Add delete confirmation dialogs
Ladmin\Admin::script(<<<'JS'
$(document).ready(function() {
    // Add confirmation to delete buttons
    $(document).on('click', '.grid-row-delete', function(e) {
        var confirmMessage = $(this).data('confirm') || 'Are you sure you want to delete this record?';
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
    });

    // Add confirmation to form delete button
    $(document).on('click', '.form-tools .btn-danger', function(e) {
        var confirmMessage = $(this).data('confirm') || 'Are you sure you want to delete this record?';
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
    });
});
JS
);


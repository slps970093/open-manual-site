<?php

return [
    // Manual Menu
    'manual_menu' => [
        'title' => 'Manual Menu',
        'manual' => 'Manual',
        'path' => 'Path',
        'name' => 'Name',
        'click_action' => 'Click Action',
        'url' => 'URL',
        'page_info' => 'Page Info',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'parent_menu' => 'Parent Menu',
        'root_no_parent' => 'Root (No Parent)',

        // Click Actions
        'expand' => 'Expand',
        'page' => 'Page',
        'external' => 'External',
        'expand_desc' => 'Expand (Show children only)',
        'page_desc' => 'Page (Navigate to page)',
        'external_desc' => 'External (Navigate to URL)',

        // Help Text
        'help_manual' => 'Select the manual this menu item belongs to',
        'help_parent' => 'Select parent menu item or leave as Root for top-level menu',
        'help_name' => 'Required',
        'help_name_optional' => 'Optional',
        'help_click_action' => 'Select the action when this menu item is clicked',
        'help_url' => 'Required when click action is "External"',
        'help_page_info' => 'Required when click action is "Page"',

        // Filters
        'filter_manual' => 'Manual',
        'filter_click_action' => 'Click Action',

        // Messages
        'error_url_required' => 'URL is required when click action is "External"',
        'error_page_info_required' => 'Page Info is required when click action is "Page"',
        'delete_success' => 'Menu item deleted successfully',
        'delete_error' => 'Failed to delete menu item',
    ],

    // Manual
    'manual' => [
        'title' => 'Manual',
        'url_slug' => 'URL Slug',
        'name' => 'Name',
        'description' => 'Description',
        'is_public' => 'Public',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    // Manual Page Info
    'page_info' => [
        'title' => 'Page Info',
        'manual' => 'Manual',
        'page_title' => 'Title',
        'keyword' => 'Keyword',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'help_manual' => 'Select the manual this page belongs to',
        'help_title' => 'Required',
        'help_title_optional' => 'Optional',
        'help_keyword' => 'Optional keywords for SEO',
        'error_title_required' => 'Title is required in at least one language',
    ],

    // Manual Page Content
    'page_content' => [
        'title' => 'Page Content',
        'page_info' => 'Page Info',
        'language' => 'Language',
        'content' => 'Content',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    // Success Messages
    'success' => [
        'create' => 'Record created successfully',
        'update' => 'Record updated successfully',
        'delete' => 'Record deleted successfully',
        'manual_create' => 'Manual created successfully',
        'manual_update' => 'Manual updated successfully',
        'manual_delete' => 'Manual and all related records deleted successfully',
        'menu_create' => 'Menu item created successfully',
        'menu_update' => 'Menu item updated successfully',
        'menu_delete' => 'Menu item deleted successfully',
        'page_info_create' => 'Page info created successfully',
        'page_info_update' => 'Page info updated successfully',
        'page_info_delete' => 'Page info and all related content deleted successfully',
        'page_content_create' => 'Page content created successfully',
        'page_content_update' => 'Page content updated successfully',
        'page_content_delete' => 'Page content deleted successfully',
    ],

    // Error Messages
    'error' => [
        'create' => 'Failed to create record',
        'update' => 'Failed to update record',
        'delete' => 'Failed to delete record',
        'not_found' => 'Record not found',
        'unauthorized' => 'You do not have permission to perform this action',
        'tree_operation_failed' => 'Menu structure operation failed, please try again',
        'invalid_parent' => 'Invalid parent menu item',
        'circular_reference' => 'Cannot move menu item to its own descendant',
    ],

    // Validation Messages
    'validation' => [
        'url_slug_unique' => 'This URL slug is already in use',
        'url_slug_format' => 'URL slug can only contain letters, numbers, hyphens, and underscores',
        'name_required' => 'Name is required in at least one language',
        'title_required' => 'Title is required in at least one language',
        'content_required' => 'Content is required',
        'url_required' => 'URL is required when click action is "External"',
        'page_info_required' => 'Page info is required when click action is "Page"',
        'language_not_supported' => 'Language code is not supported',
        'duplicate_language_content' => 'Content for this language already exists',
        'required_language_missing' => 'Content is required for the following languages: :languages',
    ],

    // Confirmation Messages
    'confirm' => [
        'delete' => 'Are you sure you want to delete this record?',
        'delete_manual' => 'Are you sure you want to delete this manual? All related menus, pages, and content will be deleted.',
        'delete_menu' => 'Are you sure you want to delete this menu item?',
        'delete_page_info' => 'Are you sure you want to delete this page? All related content will be deleted.',
        'delete_page_content' => 'Are you sure you want to delete this page content?',
    ],

    // Menu Items
    'menu' => [
        'manual_management' => 'Manual Management',
        'manuals' => 'Manuals',
        'menu_items' => 'Menu Items',
        'page_info' => 'Page Info',
        'page_content' => 'Page Content',
    ],

    // Frontend
    'search_manuals' => 'Search manuals...',
    'no_search_results' => 'No manuals found matching your search.',
    'all_rights_reserved' => 'All rights reserved.',
    'home' => 'Home',
    'table_of_contents' => 'Table of Contents',
    'no_headings' => 'No headings found',
    'no_content_language' => 'Content not available in this language',

    // Common
    'none' => 'None',
    'yes' => 'Yes',
    'no' => 'No',
];

<?php

return [
    // Manual Menu
    'manual_menu' => [
        'title' => '手冊選單',
        'manual' => '所屬手冊',
        'path' => '完整路徑',
        'name' => '名稱',
        'click_action' => '點擊動作',
        'url' => 'URL',
        'page_info' => '頁面資訊',
        'created_at' => '建立時間',
        'updated_at' => '更新時間',
        'parent_menu' => '父選單',
        'root_no_parent' => '根節點（無父節點）',

        // Click Actions
        'expand' => '展開',
        'page' => '頁面',
        'external' => '外部連結',
        'expand_desc' => '展開（僅顯示子項目）',
        'page_desc' => '頁面（導向頁面）',
        'external_desc' => '外部連結（導向 URL）',

        // Help Text
        'help_manual' => '選擇此選單項目所屬的手冊',
        'help_parent' => '選擇父選單項目，或保持為根節點以建立頂層選單',
        'help_name' => '必填',
        'help_name_optional' => '選填',
        'help_click_action' => '選擇點擊此選單項目時的動作',
        'help_url' => '當點擊動作為「外部連結」時必填',
        'help_page_info' => '當點擊動作為「頁面」時必填',

        // Filters
        'filter_manual' => '手冊',
        'filter_click_action' => '點擊動作',

        // Messages
        'error_url_required' => '當點擊動作為「外部連結」時，URL 為必填',
        'error_page_info_required' => '當點擊動作為「頁面」時，頁面資訊為必填',
        'delete_success' => '選單項目已成功刪除',
        'delete_error' => '刪除選單項目失敗',
    ],

    // Manual
    'manual' => [
        'title' => '手冊',
        'url_slug' => 'URL 別名',
        'name' => '名稱',
        'description' => '描述',
        'is_public' => '公開',
        'created_at' => '建立時間',
        'updated_at' => '更新時間',
    ],

    // Manual Page Info
    'page_info' => [
        'title' => '頁面資訊',
        'manual' => '所屬手冊',
        'page_title' => '標題',
        'keyword' => '關鍵字',
        'created_at' => '建立時間',
        'updated_at' => '更新時間',
        'help_manual' => '選擇此頁面所屬的手冊',
        'help_title' => '必填',
        'help_title_optional' => '選填',
        'help_keyword' => '選填的 SEO 關鍵字',
        'error_title_required' => '至少需要一種語言的標題',
    ],

    // Manual Page Content
    'page_content' => [
        'title' => '頁面內容',
        'page_info' => '頁面資訊',
        'language' => '語言',
        'content' => '內容',
        'created_at' => '建立時間',
        'updated_at' => '更新時間',
    ],

    // Success Messages
    'success' => [
        'create' => '記錄已成功建立',
        'update' => '記錄已成功更新',
        'delete' => '記錄已成功刪除',
        'manual_create' => '手冊已成功建立',
        'manual_update' => '手冊已成功更新',
        'manual_delete' => '手冊及所有相關記錄已成功刪除',
        'menu_create' => '選單項目已成功建立',
        'menu_update' => '選單項目已成功更新',
        'menu_delete' => '選單項目已成功刪除',
        'page_info_create' => '頁面資訊已成功建立',
        'page_info_update' => '頁面資訊已成功更新',
        'page_info_delete' => '頁面資訊及所有相關內容已成功刪除',
        'page_content_create' => '頁面內容已成功建立',
        'page_content_update' => '頁面內容已成功更新',
        'page_content_delete' => '頁面內容已成功刪除',
    ],

    // Error Messages
    'error' => [
        'create' => '建立記錄失敗',
        'update' => '更新記錄失敗',
        'delete' => '刪除記錄失敗',
        'not_found' => '記錄未找到',
        'unauthorized' => '您沒有權限執行此操作',
        'tree_operation_failed' => '選單結構操作失敗，請重試',
        'invalid_parent' => '無效的父選單項目',
        'circular_reference' => '無法將選單項目移動到其自身的後代',
    ],

    // Validation Messages
    'validation' => [
        'url_slug_unique' => '此 URL 別名已被使用',
        'url_slug_format' => 'URL 別名只能包含字母、數字、連字符和下劃線',
        'name_required' => '至少需要一種語言的名稱',
        'title_required' => '至少需要一種語言的標題',
        'content_required' => '內容為必填',
        'url_required' => '當點擊動作為「外部連結」時，URL 為必填',
        'page_info_required' => '當點擊動作為「頁面」時，頁面資訊為必填',
        'language_not_supported' => '不支持的語言代碼',
        'duplicate_language_content' => '此語言的內容已存在',
        'required_language_missing' => '以下語言的內容為必填：:languages',
    ],

    // Confirmation Messages
    'confirm' => [
        'delete' => '確定要刪除此記錄嗎？',
        'delete_manual' => '確定要刪除此手冊嗎？所有相關的選單、頁面和內容都將被刪除。',
        'delete_menu' => '確定要刪除此選單項目嗎？',
        'delete_page_info' => '確定要刪除此頁面嗎？所有相關的內容都將被刪除。',
        'delete_page_content' => '確定要刪除此頁面內容嗎？',
    ],

    // Menu Items
    'menu' => [
        'manual_management' => '手冊管理',
        'manuals' => '手冊',
        'menu_items' => '選單項目',
        'page_info' => '頁面資訊',
        'page_content' => '頁面內容',
    ],

    // Frontend
    'table_of_contents' => '目錄',
    'no_headings' => '未找到標題',
    'no_content_language' => '此語言的內容不可用',

    // Common
    'none' => '無',
    'yes' => '是',
    'no' => '否',
];

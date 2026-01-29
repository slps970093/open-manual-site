# 實現計劃：手冊管理後台系統

## 概述

本實現計劃將手冊管理後台系統的設計轉化為一系列可執行的開發任務。系統將使用 Laravel-Admin 作為管理界面框架，ClosureTable 進行階層式菜單管理，spatie/laravel-translatable 進行多語言支持，CKEditor 進行富文本編輯，laravel-filemanager 進行文件管理。

## 任務

- [ ] 1. 設置項目結構和配置
  - 安裝必要的 Composer 套件（ClosureTable、spatie/laravel-translatable、CKEditor、laravel-filemanager）
  - 創建 config/manual.php 配置文件
  - 發佈 laravel-filemanager 配置文件
  - _需求：7.1, 7.2_

- [ ] 2. 創建 Manual 模型和遷移
  - 創建 Manual 模型並應用 HasTranslations trait
  - 創建 manual 表遷移（包含 url_slug、name、description、is_public 字段）
  - 實現 Manual 模型的驗證規則（url_slug 唯一性和格式驗證）
  - _需求：1.1, 1.2, 1.3, 5.1, 5.2_

  - [ ]* 2.1 編寫 Manual 模型的屬性測試
    - 測試手冊創建和檢索
    - 測試多語言字段存儲
    - _需求：1.3_

- [ ] 3. 創建 ManualMenu 模型和 ClosureTable 結構
  - 創建 ManualMenu 模型並應用 HasTranslations trait 和 ClosureTable trait
  - 創建 manual_menu 表遷移
  - 創建 manual_menu_closure 表遷移
  - 實現菜單項驗證規則（click_action 和 url/manual_page_info_id 的驗證）
  - _需求：2.1, 2.2, 2.3, 2.8, 2.9, 2.10, 5.3, 5.4, 5.5, 5.6_

  - [ ]* 3.1 編寫 ManualMenu 模型的屬性測試
    - 測試樹狀結構創建和操作
    - 測試菜單項驗證規則
    - _需求：2.1, 2.3, 2.4, 2.8, 2.9, 2.10_

- [ ] 4. 創建 ManualPageInfo 模型和遷移
  - 創建 ManualPageInfo 模型並應用 HasTranslations trait
  - 創建 manual_page_info 表遷移
  - 實現頁面信息驗證規則（title 必填驗證）
  - _需求：3.1, 3.2, 5.5_

  - [ ]* 4.1 編寫 ManualPageInfo 模型的屬性測試
    - 測試頁面信息創建和檢索
    - 測試多語言字段存儲
    - _需求：3.1, 3.2_

- [ ] 5. 創建 ManualPageContent 模型和遷移
  - 創建 ManualPageContent 模型
  - 創建 manual_page_content 表遷移（包含複合唯一索引）
  - 實現頁面內容驗證規則（content 非空、語言代碼有效性）
  - _需求：4.1, 5.8, 5.9_

  - [ ]* 5.1 編寫 ManualPageContent 模型的屬性測試
    - 測試頁面內容創建和檢索
    - 測試語言唯一性約束
    - _需求：4.1, 4.7_

- [ ] 6. 創建 Manual 控制器和 Laravel-Admin 界面
  - 創建 ManualController 並繼承 AdminController
  - 實現 grid() 方法顯示手冊列表（包含 is_public 狀態）
  - 實現 form() 方法用於創建和編輯手冊
  - 實現多語言字段表單（name、description）
  - 實現 is_public 開關字段
  - _需求：1.1, 1.2, 1.3, 1.5, 1.7, 6.1, 6.3, 7.1, 7.2_

  - [ ]* 6.1 編寫 Manual 控制器的單元測試
    - 測試列表、創建、編輯、刪除操作
    - 測試驗證錯誤處理
    - _需求：1.1, 1.3, 1.5_

- [ ] 7. 創建 ManualMenu 控制器和 Laravel-Admin 界面
  - 創建 ManualMenuController 並繼承 AdminController
  - 實現 grid() 方法顯示菜單樹狀結構
  - 實現 form() 方法用於創建和編輯菜單項
  - 實現多語言 name 字段
  - 實現 click_action 選擇（external、page、expand）
  - 實現條件性 url 和 manual_page_info_id 字段
  - _需求：2.1, 2.2, 2.3, 2.6, 2.7, 6.1, 7.2_

  - [ ]* 7.1 編寫 ManualMenu 控制器的單元測試
    - 測試菜單項 CRUD 操作
    - 測試樹狀結構操作
    - 測試驗證規則
    - _需求：2.1, 2.3, 2.6_

- [ ] 8. 創建 ManualPageInfo 控制器和 Laravel-Admin 界面
  - 創建 ManualPageInfoController 並繼承 AdminController
  - 實現 grid() 方法顯示頁面信息列表
  - 實現 form() 方法用於創建和編輯頁面信息
  - 實現多語言 title 和 keyword 字段
  - _需求：3.1, 3.2, 3.3, 3.5, 6.1, 7.2_

  - [ ]* 8.1 編寫 ManualPageInfo 控制器的單元測試
    - 測試頁面信息 CRUD 操作
    - 測試多語言字段
    - _需求：3.1, 3.2, 3.3_

- [ ] 9. 創建 ManualPageContent 控制器和 Laravel-Admin 界面
  - 創建 ManualPageContentController 並繼承 AdminController
  - 實現 grid() 方法顯示頁面內容列表（按語言分組）
  - 實現 form() 方法用於創建和編輯頁面內容
  - 集成 CKEditor 富文本編輯器
  - 集成 laravel-filemanager 文件管理器
  - 實現圖片路徑靈活性（使用 config/manual.php 中的 image_base_url）
  - _需求：4.1, 4.2, 4.3, 4.4, 4.5, 4.9, 6.1, 7.2_

  - [ ]* 9.1 編寫 ManualPageContent 控制器的單元測試
    - 測試頁面內容 CRUD 操作
    - 測試語言唯一性約束
    - 測試圖片路徑處理
    - _需求：4.1, 4.5, 4.7_

- [ ] 10. 實現多語言表單支持
  - 創建自定義 Laravel-Admin 表單字段用於多語言輸入
  - 實現 supported_languages 配置讀取
  - 實現 required_languages 驗證
  - 在表單中標記必填語言
  - _需求：6.1, 6.4, 7.1, 7.3, 7.4_

  - [ ]* 10.1 編寫多語言表單字段的屬性測試
    - 測試語言字段生成
    - 測試必填語言驗證
    - _需求：7.3, 7.4_

- [ ] 11. 實現級聯刪除和數據完整性
  - 在 Manual 模型中實現級聯刪除邏輯
  - 在 ManualPageInfo 模型中實現級聯刪除邏輯
  - 添加軟刪除支持
  - _需求：1.6, 3.4_

  - [ ]* 11.1 編寫級聯刪除的屬性測試
    - 測試手冊刪除時的級聯效果
    - 測試頁面信息刪除時的級聯效果
    - _需求：1.6, 3.4_

- [ ] 12. 實現菜單樹狀操作
  - 實現菜單項移動功能
  - 實現菜單項刪除功能（處理後代項）
  - 實現菜單樹狀結構查詢優化
  - _需求：2.4, 2.5_

  - [ ]* 12.1 編寫菜單樹狀操作的屬性測試
    - 測試菜單項移動
    - 測試菜單項刪除
    - 測試樹狀結構一致性
    - _需求：2.4, 2.5_

- [ ] 13. 實現操作反饋和錯誤處理
  - 實現成功消息顯示
  - 實現驗證錯誤消息
  - 實現業務邏輯錯誤消息
  - 實現刪除確認對話框
  - _需求：7.3, 7.4_

  - [ ]* 13.1 編寫操作反饋的單元測試
    - 測試成功消息
    - 測試錯誤消息
    - _需求：7.3_

- [ ] 14. 實現路由和菜單導航
  - 在 app/Admin/routes.php 中註冊所有資源路由
  - 在 Laravel-Admin 菜單中添加手冊管理入口
  - 實現麵包屑導航
  - _需求：7.1, 7.2, 7.7_

- [ ] 15. 檢查點 - 確保所有測試通過
  - 運行所有單元測試
  - 運行所有屬性測試
  - 驗證沒有遺漏的功能
  - 確保代碼質量

- [ ] 16. 集成測試和端到端驗證
  - [ ]* 16.1 編寫集成測試
    - 測試完整的手冊創建流程
    - 測試菜單和頁面的關聯
    - 測試多語言內容的完整流程
    - _需求：1.1, 2.1, 3.1, 4.1_

  - [ ]* 16.2 編寫端到端測試
    - 測試通過 Laravel-Admin 界面的完整操作流程
    - _需求：所有需求_

- [ ] 17. 最終檢查點 - 確保所有測試通過
  - 運行完整的測試套件
  - 驗證所有正確性屬性
  - 確保系統穩定性

## 注意事項

- 所有標記為 `*` 的任務是可選的測試任務，可以根據項目需求跳過
- 核心實現任務（不標記 `*`）必須完成
- 每個任務應該在完成後運行相應的測試
- 遵循 Laravel 和 Laravel-Admin 的最佳實踐
- 確保代碼的可讀性和可維護性


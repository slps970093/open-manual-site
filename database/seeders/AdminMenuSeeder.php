<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Ladmin\Auth\Database\Menu;

class AdminMenuSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Manual Management menu item
        Menu::create([
            'parent_id' => 0,
            'order' => 1,
            'title' => __('admin/manual.menu.manual_management'),
            'icon' => 'fa-book',
            'uri' => '',
            'permission' => null,
        ]);

        // Get the parent menu ID
        $parentMenu = Menu::where('title', __('admin/manual.menu.manual_management'))->first();

        if ($parentMenu) {
            // Create Manual submenu
            Menu::create([
                'parent_id' => $parentMenu->id,
                'order' => 1,
                'title' => __('admin/manual.menu.manuals'),
                'icon' => 'fa-list',
                'uri' => 'manuals',
                'permission' => null,
            ]);

            // Create Manual Menu submenu
            Menu::create([
                'parent_id' => $parentMenu->id,
                'order' => 2,
                'title' => __('admin/manual.menu.menu_items'),
                'icon' => 'fa-sitemap',
                'uri' => 'manual-menus',
                'permission' => null,
            ]);

            // Create Manual Page Info submenu
            Menu::create([
                'parent_id' => $parentMenu->id,
                'order' => 3,
                'title' => __('admin/manual.menu.page_info'),
                'icon' => 'fa-file-text',
                'uri' => 'manual-page-infos',
                'permission' => null,
            ]);

            // Create Manual Page Content submenu (will be added in task 9)
            // Menu::create([
            //     'parent_id' => $parentMenu->id,
            //     'order' => 4,
            //     'title' => __('admin/manual.menu.page_content'),
            //     'icon' => 'fa-file-o',
            //     'uri' => 'manual-page-contents',
            //     'permission' => null,
            // ]);
        }
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\ManualMenu;
use App\Models\Manual;
use App\Models\ManualPageInfo;
use App\Admin\Helpers\TranslatableFormHelper;
use Ladmin\Controllers\AdminController;
use Ladmin\Form;
use Ladmin\Grid;
use Ladmin\Show;
use Ladmin\Layout\Content;

class ManualMenuController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Manual Menu';

    /**
     * Index page with breadcrumb.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title(__('admin/manual.manual_menu.title'))
            ->description(__('admin/manual.manual_menu.title'))
            ->breadcrumb(
                ['text' => __('admin.home'), 'url' => admin_url('/')],
                ['text' => __('admin/manual.manual_menu.title')]
            )
            ->body($this->grid());
    }

    /**
     * Create page with breadcrumb.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->title(__('admin.create'))
            ->description(__('admin/manual.manual_menu.title'))
            ->breadcrumb(
                ['text' => __('admin.home'), 'url' => admin_url('/')],
                ['text' => __('admin/manual.manual_menu.title'), 'url' => admin_url('manual-menus')],
                ['text' => __('admin.create')]
            )
            ->body($this->form());
    }

    /**
     * Create page with manual_id parameter.
     *
     * @param Content $content
     * @return Content
     */
    public function createWithManual(Content $content)
    {
        $manualId = request('manual_id');
        if (!$manualId) {
            return redirect(admin_url('manual-menus/create'));
        }

        return $content
            ->title(__('admin.create'))
            ->description(__('admin/manual.manual_menu.title'))
            ->breadcrumb(
                ['text' => __('admin.home'), 'url' => admin_url('/')],
                ['text' => __('admin/manual.manual_menu.title'), 'url' => admin_url('manual-menus')],
                ['text' => __('admin.create')]
            )
            ->body($this->form());
    }

    /**
     * Edit page with breadcrumb.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title(__('admin.edit'))
            ->description(__('admin/manual.manual_menu.title'))
            ->breadcrumb(
                ['text' => __('admin.home'), 'url' => admin_url('/')],
                ['text' => __('admin/manual.manual_menu.title'), 'url' => admin_url('manual-menus')],
                ['text' => __('admin.edit')]
            )
            ->body($this->form(ManualMenu::findOrFail($id))->edit($id));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ManualMenu());

        // Optimize queries with eager loading (avoid ancestors eager loading due to ClosureTable compatibility)
        $grid->model()->with(['manual', 'pageInfo']);

        $grid->column('id', __('ID'))->sortable();

        $grid->column('manual.name', __('admin/manual.manual_menu.manual'))->display(function () {
            return $this->manual ? $this->manual->getTranslation('name', config('manual.default_language')) : '-';
        });

        $grid->column('path', __('admin/manual.manual_menu.path'))->display(function () {
            // Build full path from root to current node
            // For performance, just show the current node name with depth indicator
            $depth = $this->getNodeDepth();
            $displayName = $this->getTranslation('name', config('manual.default_language'));
            $indent = str_repeat('  ', $depth);
            return $indent . $displayName;
        });

        $grid->column('name', __('admin/manual.manual_menu.name'))->display(function ($name) {
            // Display the name in the default language with tree indentation
            $depth = $this->getNodeDepth();
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth);
            $displayName = $this->getTranslation('name', config('manual.default_language'));
            return $indent . ($depth > 0 ? '└─ ' : '') . $displayName;
        });

        $grid->column('click_action', __('admin/manual.manual_menu.click_action'))->display(function ($action) {
            $labels = [
                'external' => '<span class="label label-info">' . __('admin/manual.manual_menu.external') . '</span>',
                'page' => '<span class="label label-success">' . __('admin/manual.manual_menu.page') . '</span>',
                'expand' => '<span class="label label-default">' . __('admin/manual.manual_menu.expand') . '</span>',
            ];
            return $labels[$action] ?? $action;
        });

        $grid->column('url', __('admin/manual.manual_menu.url'))->display(function ($url) {
            return $url ?: '-';
        });

        $grid->column('pageInfo.title', __('admin/manual.manual_menu.page_info'))->display(function () {
            if ($this->pageInfo) {
                return $this->pageInfo->getTranslation('title', config('manual.default_language'));
            }
            return '-';
        });

        $grid->column('created_at', __('admin/manual.manual_menu.created_at'))->sortable();

        // Order by manual_id, then parent_id (nulls first for root nodes), then position, then id
        $grid->model()->orderBy('manual_id')
            ->orderByRaw('CASE WHEN parent_id IS NULL OR parent_id = 0 THEN 0 ELSE 1 END')
            ->orderBy('parent_id')
            ->orderBy('position')
            ->orderBy('id');

        // Disable pagination to show full tree structure
        $grid->disablePagination();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            $filter->equal('manual_id', __('admin/manual.manual_menu.filter_manual'))->select(
                Manual::all()->pluck('name', 'id')->map(function ($name, $id) {
                    $manual = Manual::find($id);
                    return $manual ? $manual->getTranslation('name', config('manual.default_language')) : $name;
                })
            );

            $filter->equal('click_action', __('admin/manual.manual_menu.filter_click_action'))->select([
                'external' => __('admin/manual.manual_menu.external'),
                'page' => __('admin/manual.manual_menu.page'),
                'expand' => __('admin/manual.manual_menu.expand'),
            ]);
        });

        // Add delete confirmation
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(ManualMenu::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('manual.name', __('admin/manual.manual_menu.manual'))->as(function () {
            return $this->manual ? $this->manual->getTranslation('name', config('manual.default_language')) : '-';
        });
        $show->field('name', __('admin/manual.manual_menu.name'));
        $show->field('click_action', __('admin/manual.manual_menu.click_action'));
        $show->field('url', __('admin/manual.manual_menu.url'));
        $show->field('pageInfo.title', __('admin/manual.manual_menu.page_info'))->as(function () {
            return $this->pageInfo ? $this->pageInfo->getTranslation('title', config('manual.default_language')) : '-';
        });
        $show->field('created_at', __('admin/manual.manual_menu.created_at'));
        $show->field('updated_at', __('admin/manual.manual_menu.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($model = null)
    {
        $model = $model ?? new ManualMenu();
        $form = new Form($model);

        // Get manual_id from query parameter if creating
        $manualId = request('manual_id');

        // Manual selection
        $manualSelect = $form->select('manual_id', __('admin/manual.manual_menu.manual'))
            ->options(
                Manual::all()->pluck('name', 'id')->map(function ($name, $id) {
                    $manual = Manual::find($id);
                    return $manual ? $manual->getTranslation('name', config('manual.default_language')) : $name;
                })
            );

        // If creating with manual_id parameter, set default
        if ($form->isCreating() && $manualId) {
            $manualSelect->default($manualId);
        }

        $manualSelect->rules('required')
            ->help(__('admin/manual.manual_menu.help_manual'));

        // Parent menu selection (optional)
        $form->select('parent_id', __('admin/manual.manual_menu.parent_menu'))
            ->options(function () {
                $manualId = request('manual_id') ?: (request()->route('manual_menu') ? ManualMenu::find(request()->route('manual_menu'))->manual_id : null);

                // Only show menus from the same manual
                if ($manualId) {
                    $menus = ManualMenu::where('manual_id', $manualId)->get();
                } else {
                    $menus = ManualMenu::all();
                }

                $options = [0 => __('admin/manual.manual_menu.root_no_parent')];
                foreach ($menus as $menu) {
                    $depth = $menu->getNodeDepth();
                    $indent = str_repeat('&nbsp;&nbsp;', $depth);
                    $displayName = $menu->getTranslation('name', config('manual.default_language'));
                    $options[$menu->id] = $indent . ($depth > 0 ? '└─ ' : '') . $displayName;
                }
                return $options;
            })
            ->help(__('admin/manual.manual_menu.help_parent'));

        // Translatable name — TAB layout
        TranslatableFormHelper::addMultiFieldTranslatableTabs($form, [
            [
                'column' => 'name',
                'label'  => __('admin/manual.manual_menu.name'),
                'type'   => 'text',
                'values' => $model->exists ? ($model->getTranslations('name') ?: []) : [],
                'help'   => __('admin/manual.manual_menu.help_name'),
            ],
        ]);

        // Click action selection
        $form->select('click_action', __('admin/manual.manual_menu.click_action'))
            ->options([
                'expand' => __('admin/manual.manual_menu.expand_desc'),
                'page' => __('admin/manual.manual_menu.page_desc'),
                'external' => __('admin/manual.manual_menu.external_desc'),
            ])
            ->default('expand')
            ->rules('required')
            ->help(__('admin/manual.manual_menu.help_click_action'));

        // URL field (conditional on click_action)
        $form->text('url', __('admin/manual.manual_menu.url'))
            ->rules('nullable|url')
            ->help(__('admin/manual.manual_menu.help_url'));

        // Page info selection (conditional on click_action)
        $form->select('manual_page_info_id', __('admin/manual.manual_menu.page_info'))
            ->options(function () {
                $manualId = request('manual_id') ?: (request()->route('manual_menu') ? ManualMenu::find(request()->route('manual_menu'))->manual_id : null);

                // Only show page infos from the same manual that have content
                if ($manualId) {
                    $pageInfos = ManualPageInfo::where('manual_id', $manualId)
                        ->whereHas('pageContents') // Only show pages with content
                        ->get();
                } else {
                    $pageInfos = ManualPageInfo::whereHas('pageContents')->get();
                }

                $options = ['' => __('admin/manual.none')];
                foreach ($pageInfos as $pageInfo) {
                    $displayTitle = $pageInfo->getTranslation('title', config('manual.default_language'));
                    $contentCount = $pageInfo->pageContents()->count();
                    $options[$pageInfo->id] = $displayTitle . " ({$contentCount} " . __('admin/manual.languages') . ")";
                }
                return $options;
            })
            ->help(__('admin/manual.manual_menu.help_page_info'));

        // Add JavaScript to handle conditional fields and dynamic page info loading
        $form->html('<script>
            $(document).ready(function() {
                function updateFields() {
                    var clickAction = $("select[name=\'click_action\']").val();
                    var urlField = $(".url");
                    var pageInfoField = $(".manual_page_info_id");

                    // Hide all conditional fields first
                    urlField.closest(".form-group").hide();
                    pageInfoField.closest(".form-group").hide();

                    // Show relevant fields based on click_action
                    if (clickAction === "external") {
                        urlField.closest(".form-group").show();
                    } else if (clickAction === "page") {
                        pageInfoField.closest(".form-group").show();
                    }
                }

                function updatePageInfoOptions() {
                    var manualId = $("select[name=\'manual_id\']").val();

                    if (!manualId) {
                        return;
                    }

                    // Fetch page infos for the selected manual
                    $.ajax({
                        url: "' . admin_url('manual-page-infos') . '",
                        type: "GET",
                        data: { manual_id: manualId },
                        headers: {
                            "Accept": "application/json"
                        },
                        dataType: "json",
                        success: function(data) {
                            var pageInfoSelect = $("select[name=\'manual_page_info_id\']");
                            var currentValue = pageInfoSelect.val();

                            pageInfoSelect.empty();
                            pageInfoSelect.append($("<option></option>").attr("value", "").text("' . __('admin/manual.none') . '"));

                            if (data.data) {
                                $.each(data.data, function(key, value) {
                                    var title = value.title;
                                    pageInfoSelect.append($("<option></option>").attr("value", value.id).text(title));
                                });
                            }

                            // Restore previous value if it still exists
                            if (currentValue && pageInfoSelect.find("option[value=\'" + currentValue + "\']").length) {
                                pageInfoSelect.val(currentValue);
                            }
                        }
                    });
                }

                // Initial update
                updateFields();

                // Update on change
                $("select[name=\'click_action\']").change(updateFields);
                $("select[name=\'manual_id\']").change(updatePageInfoOptions);
            });
        </script>');

        // Custom validation and parent handling
        $form->saving(function (Form $form) {
            $clickAction = request('click_action');
            $url = request('url');
            $pageInfoId = request('manual_page_info_id');
            $parentId = request('parent_id');

            // Handle translatable name (submitted via TAB html fields)
            $nameData = request('name', []);
            $nameValues = is_array($nameData) ? array_filter($nameData) : [];
            if (!empty($nameValues)) {
                $form->model()->name = $nameValues;
            }

            // Validate based on click_action
            if ($clickAction === 'external' && empty($url)) {
                admin_error(__('admin/manual.error.create'), __('admin/manual.validation.url_required'));
                return back()->withInput();
            }

            if ($clickAction === 'page' && empty($pageInfoId)) {
                admin_error(__('admin/manual.error.create'), __('admin/manual.validation.page_info_required'));
                return back()->withInput();
            }

            // Validate that selected page has content
            if ($clickAction === 'page' && $pageInfoId) {
                $pageInfo = ManualPageInfo::find($pageInfoId);
                if (!$pageInfo || !$pageInfo->pageContents()->exists()) {
                    admin_error(__('admin/manual.error.create'), __('admin/manual.validation.page_must_have_content'));
                    return back()->withInput();
                }
            }

            // Clear fields based on click_action
            if ($clickAction === 'external') {
                $form->manual_page_info_id = null;
            } elseif ($clickAction === 'page') {
                $form->url = '';
            } elseif ($clickAction === 'expand') {
                $form->url = '';
                $form->manual_page_info_id = null;
            }

            // Handle parent_id for tree operations
            if ($parentId && $parentId !== '0') {
                $form->parent_id = $parentId;
            } else {
                $form->parent_id = null;
            }
        });

        // Handle parent change after save (move operation)
        $form->saved(function (Form $form) {
            $menu = $form->model();
            $parentId = request('parent_id');

            try {
                // If parent_id is provided and different from current parent, move the node
                if ($parentId && $parentId !== '0' && $menu->parent_id != $parentId) {
                    $newParent = ManualMenu::find($parentId);
                    if ($newParent) {
                        // Check for circular reference
                        if ($newParent->isDescendantOf($menu)) {
                            admin_error(__('admin/manual.error.tree_operation_failed'), __('admin/manual.error.circular_reference'));
                            return back()->withInput();
                        }
                        $menu->setParent($newParent);
                    }
                } elseif ((!$parentId || $parentId === '0') && $menu->parent_id !== null) {
                    // Make it a root node if parent_id is null or 0
                    $menu->makeRoot();
                }

                // Show success message
                if ($form->isCreating()) {
                    admin_toastr(__('admin/manual.success.menu_create'), 'success');
                } else {
                    admin_toastr(__('admin/manual.success.menu_update'), 'success');
                }
            } catch (\Exception $e) {
                admin_error(__('admin/manual.error.tree_operation_failed'), $e->getMessage());
            }
        });

        return $form;
    }

    /**
     * Delete a menu item and handle descendants.
     *
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $menu = ManualMenu::findOrFail($id);
            $menu->delete();
            admin_toastr(__('admin/manual.success.menu_delete'), 'success');
        } catch (\Exception $e) {
            admin_toastr(__('admin/manual.error.delete'), 'error');
        }

        return redirect(admin_url('manual-menu'));
    }
}

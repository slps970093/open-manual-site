<?php

namespace App\Admin\Controllers;

use App\Models\Manual;
use App\Admin\Helpers\TranslatableFormHelper;
use Ladmin\Controllers\AdminController;
use Ladmin\Form;
use Ladmin\Grid;
use Ladmin\Show;
use Ladmin\Layout\Content;

class ManualController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Manual';

    /**
     * Index page with breadcrumb.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title(__('admin/manual.manual.title'))
            ->description(__('admin/manual.manual.title'))
            ->breadcrumb(
                ['text' => __('admin.home'), 'url' => admin_url('/')],
                ['text' => __('admin/manual.manual.title')]
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
            ->description(__('admin/manual.manual.title'))
            ->breadcrumb(
                ['text' => __('admin.home'), 'url' => admin_url('/')],
                ['text' => __('admin/manual.manual.title'), 'url' => admin_url('manuals')],
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
            ->description(__('admin/manual.manual.title'))
            ->breadcrumb(
                ['text' => __('admin.home'), 'url' => admin_url('/')],
                ['text' => __('admin/manual.manual.title'), 'url' => admin_url('manuals')],
                ['text' => __('admin.edit')]
            )
            ->body($this->form(Manual::findOrFail($id))->edit($id));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Manual());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('url_slug', __('admin/manual.manual.url_slug'))->sortable();
        $grid->column('name', __('admin/manual.manual.name'))->display(function ($name) {
            // Display the name in the default language
            return $this->getTranslation('name', config('manual.default_language'));
        });
        $grid->column('description', __('admin/manual.manual.description'))->display(function ($description) {
            // Display the description in the default language, truncated
            $desc = $this->getTranslation('description', config('manual.default_language'));
            return strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
        });

        // Add menu list link column
        $grid->column('menus_link', __('admin/manual.manual_menu.title'))->display(function () {
            return '<a href="' . admin_url('manual-menus?manual_id=' . $this->id) . '" class="btn btn-xs btn-info">
                <i class="fa fa-sitemap"></i> ' . __('admin.view') . '
            </a>';
        });

        // Add page info list link column
        $grid->column('page_infos_link', __('admin/manual.page_info.title'))->display(function () {
            return '<a href="' . admin_url('manual-page-infos?manual_id=' . $this->id) . '" class="btn btn-xs btn-warning">
                <i class="fa fa-file-text"></i> ' . __('admin.view') . '
            </a>';
        });

        $grid->column('is_public', __('admin/manual.manual.is_public'))->display(function ($isPublic) {
            return $isPublic ? '<span class="label label-success">' . __('admin/manual.yes') . '</span>' : '<span class="label label-danger">' . __('admin/manual.no') . '</span>';
        });
        $grid->column('created_at', __('admin/manual.manual.created_at'))->sortable();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('url_slug', __('admin/manual.manual.url_slug'));
            $filter->equal('is_public', __('admin/manual.manual.is_public'))->radio([
                '' => 'All',
                1 => __('admin/manual.yes'),
                0 => __('admin/manual.no'),
            ]);
        });

        return $grid;
    }

    /**
     * Edit a manual (custom route handler).
     *
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editManual($id)
    {
        return redirect(admin_url('manuals/' . $id . '/edit'));
    }

    /**
     * Show page with breadcrumb and tree structure.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        $manual = Manual::findOrFail($id);

        // Use ClosureTable's built-in methods to get root menus
        $menus = $manual->menus()
            ->whereNull('parent_id')
            ->with('pageInfo')
            ->get();

        return $content
            ->title(__('admin.show'))
            ->description(__('admin/manual.manual.title'))
            ->breadcrumb(
                ['text' => __('admin.home'), 'url' => admin_url('/')],
                ['text' => __('admin/manual.manual.title'), 'url' => admin_url('manuals')],
                ['text' => __('admin.show')]
            )
            ->view('admin.manual.show', [
                'manual' => $manual,
                'menus' => $menus,
                'pageInfos' => $manual->pageInfos()->with('pageContents')->get(),
            ]);
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Manual::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('url_slug', __('URL Slug'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('is_public', __('Public'))->as(function ($value) {
            return $value ? 'Yes' : 'No';
        });
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @param \App\Models\Manual|null $model
     * @return Form
     */
    protected function form($model = null)
    {
        $model = $model ?? new Manual();
        $form = new Form($model);

        $form->text('url_slug', __('admin/manual.manual.url_slug'))
            ->rules('required|regex:/^[a-zA-Z0-9_-]+$/')
            ->help(__('admin/manual.manual_menu.help_url'));

        // Translatable fields — one tab group, each tab = one language
        TranslatableFormHelper::addMultiFieldTranslatableTabs($form, [
            [
                'column' => 'name',
                'label'  => __('admin/manual.manual.name'),
                'type'   => 'text',
                'values' => $model->exists ? ($model->getTranslations('name') ?: []) : [],
                'help'   => __('admin/manual.manual_menu.help_name'),
            ],
            [
                'column'   => 'description',
                'label'    => __('admin/manual.manual.description'),
                'type'     => 'textarea',
                'values'   => $model->exists ? ($model->getTranslations('description') ?: []) : [],
                'help'     => __('admin/manual.manual_menu.help_name_optional'),
                'required' => false,
            ],
        ], __('admin/manual.manual.name') . ' / ' . __('admin/manual.manual.description'));

        $form->switch('is_public', __('admin/manual.manual.is_public'))
            ->default(false)
            ->help('Make this manual visible to external users');

        // Add custom validation
        $form->saving(function (Form $form) {
            // Validate url_slug uniqueness manually (exclude self on update)
            $slug = request('url_slug');
            $currentId = $form->model()->id; // null when creating, has value when updating

            $uniqueRule = \Illuminate\Validation\Rule::unique('manual', 'url_slug');
            if ($currentId) {
                $uniqueRule->ignore($currentId);
            }

            $validator = \Illuminate\Support\Facades\Validator::make(
                ['url_slug' => $slug],
                ['url_slug' => $uniqueRule]
            );
            if ($validator->fails()) {
                admin_error('Error', "The URL Slug \"{$slug}\" has already been taken.");
                return back()->withInput();
            }

            // Read translatable fields from request (submitted via TAB html fields)
            $nameData = request('name', []);
            $nameValues = is_array($nameData) ? array_filter($nameData) : [];

            if (empty($nameValues)) {
                admin_error(__('admin/manual.error.create'), __('admin/manual.validation.name_required'));
                return back()->withInput();
            }

            $form->model()->name = $nameValues;

            $descData = request('description', []);
            $descValues = is_array($descData) ? array_filter($descData) : [];
            if (!empty($descValues)) {
                $form->model()->description = $descValues;
            }
        });

        // Add success message on save
        $form->saved(function (Form $form) {
            if ($form->isCreating()) {
                admin_toastr(__('admin/manual.success.manual_create'), 'success');
            } else {
                admin_toastr(__('admin/manual.success.manual_update'), 'success');
            }
        });

        return $form;
    }

    /**
     * Delete a manual and handle cascade deletion.
     *
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $manual = Manual::findOrFail($id);
            $manual->delete();
            admin_toastr(__('admin/manual.success.manual_delete'), 'success');
        } catch (\Exception $e) {
            admin_toastr(__('admin/manual.error.delete'), 'error');
        }

        return redirect(admin_url('manual'));
    }
}

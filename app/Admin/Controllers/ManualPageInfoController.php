<?php

namespace App\Admin\Controllers;

use App\Models\ManualPageInfo;
use App\Models\Manual;
use App\Models\ManualPageContent;
use App\Admin\Helpers\TranslatableFormHelper;
use Ladmin\Controllers\AdminController;
use Ladmin\Form;
use Ladmin\Grid;
use Ladmin\Show;
use Ladmin\Layout\Content;

class ManualPageInfoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Manual Page Info';

    /**
     * Index page with breadcrumb.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        // Handle AJAX request for page infos by manual_id (only if it's an actual AJAX request)
        if (request()->header('Accept') === 'application/json' && request('manual_id')) {
            $manualId = request('manual_id');

            $pageInfos = ManualPageInfo::where('manual_id', $manualId)
                ->whereHas('pageContents') // Only return pages with content
                ->get(['id', 'title'])
                ->map(function ($pageInfo) {
                    return [
                        'id' => $pageInfo->id,
                        'title' => $pageInfo->getTranslation('title', config('manual.default_language')),
                    ];
                });

            return response()->json(['data' => $pageInfos]);
        }

        return $content
            ->title(__('admin/manual.page_info.title'))
            ->description(__('admin/manual.page_info.title'))
            ->breadcrumb(
                ['text' => __('admin.home'), 'url' => admin_url('/')],
                ['text' => __('admin/manual.page_info.title')]
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
            ->description(__('admin/manual.page_info.title'))
            ->breadcrumb(
                ['text' => __('admin.home'), 'url' => admin_url('/')],
                ['text' => __('admin/manual.page_info.title'), 'url' => admin_url('manual-page-infos')],
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
            ->description(__('admin/manual.page_info.title'))
            ->breadcrumb(
                ['text' => __('admin.home'), 'url' => admin_url('/')],
                ['text' => __('admin/manual.page_info.title'), 'url' => admin_url('manual-page-infos')],
                ['text' => __('admin.edit')]
            )
            ->body($this->form()->edit($id));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ManualPageInfo());

        $grid->column('id', __('ID'))->sortable();

        $grid->column('manual.name', __('admin/manual.page_info.manual'))->display(function () {
            return $this->manual ? $this->manual->getTranslation('name', config('manual.default_language')) : '-';
        });

        $grid->column('title', __('admin/manual.page_info.page_title'))->display(function ($title) {
            return $this->getTranslation('title', config('manual.default_language'));
        });

        $grid->column('keyword', __('admin/manual.page_info.keyword'))->display(function ($keyword) {
            $kw = $this->getTranslation('keyword', config('manual.default_language'));
            return $kw ?: '-';
        });

        $grid->column('languages', __('Languages'))->display(function () {
            $languages = $this->pageContents()->pluck('lang')->unique()->toArray();
            if (empty($languages)) {
                return '<span class="label label-danger">' . __('admin/manual.validation.content_required') . '</span>';
            }
            return implode(', ', $languages);
        });

        $grid->column('created_at', __('admin/manual.page_info.created_at'))->sortable();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            $filter->equal('manual_id', __('admin/manual.page_info.manual'))->select(
                Manual::all()->pluck('name', 'id')->map(function ($name, $id) {
                    $manual = Manual::find($id);
                    return $manual ? $manual->getTranslation('name', config('manual.default_language')) : $name;
                })
            );
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
        $show = new Show(ManualPageInfo::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('manual.name', __('admin/manual.page_info.manual'))->as(function () {
            return $this->manual ? $this->manual->getTranslation('name', config('manual.default_language')) : '-';
        });
        $show->field('title', __('admin/manual.page_info.page_title'));
        $show->field('keyword', __('admin/manual.page_info.keyword'));
        $show->field('created_at', __('admin/manual.page_info.created_at'));
        $show->field('updated_at', __('admin/manual.page_info.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ManualPageInfo());

        // Manual selection
        $form->select('manual_id', __('admin/manual.page_info.manual'))
            ->options(
                Manual::all()->pluck('name', 'id')->map(function ($name, $id) {
                    $manual = Manual::find($id);
                    return $manual ? $manual->getTranslation('name', config('manual.default_language')) : $name;
                })
            )
            ->rules('required')
            ->help(__('admin/manual.page_info.help_manual'));

        // Add translatable title fields using helper
        TranslatableFormHelper::addTranslatableText($form, 'title', __('admin/manual.page_info.page_title'), [
            'help' => __('admin/manual.page_info.help_title'),
        ]);

        // Add translatable keyword fields using helper
        TranslatableFormHelper::addTranslatableText($form, 'keyword', __('admin/manual.page_info.keyword'), [
            'help' => __('admin/manual.page_info.help_keyword'),
        ]);

        // Add page content editor tabs
        $form->html($this->renderPageContentTabs());

        // Custom validation and data transformation
        $form->saving(function (Form $form) {
            $titleData = request('title', []);
            $titleValues = is_array($titleData) ? array_filter($titleData) : [];

            if (empty($titleValues)) {
                admin_error(__('admin/manual.error.create'), __('admin/manual.validation.title_required'));
                return false;
            }

            // Transform the translatable data into JSON format for the model
            $form->model()->title = $titleValues;

            // Also handle keyword if provided
            $keywordData = request('keyword', []);
            $keywordValues = is_array($keywordData) ? array_filter($keywordData) : [];
            if (!empty($keywordValues)) {
                $form->model()->keyword = $keywordValues;
            }
        });

        // Handle page content saving after form is saved
        $form->saved(function (Form $form) {
            try {
                $this->savePageContents($form->model());

                // Show success message
                if ($form->isCreating()) {
                    admin_toastr(__('admin/manual.success.page_info_create'), 'success');
                } else {
                    admin_toastr(__('admin/manual.success.page_info_update'), 'success');
                }
            } catch (\Exception $e) {
                admin_error(__('admin/manual.error.update'), $e->getMessage());
            }
        });

        return $form;
    }

    /**
     * Delete a page info and handle cascade deletion.
     *
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $pageInfo = ManualPageInfo::findOrFail($id);
            $pageInfo->delete();
            admin_toastr(__('admin/manual.success.page_info_delete'), 'success');
        } catch (\Exception $e) {
            admin_toastr(__('admin/manual.error.delete'), 'error');
        }

        return redirect(admin_url('manual-page-info'));
    }

    /**
     * Render page content tabs for multi-language editing.
     *
     * @return string
     */
    protected function renderPageContentTabs()
    {
        $supportedLanguages = config('manual.supported_languages', []);
        $pageInfoId = request()->route('manual_page_info');
        $pageInfo = null;

        if ($pageInfoId) {
            $pageInfo = ManualPageInfo::find($pageInfoId);
        }

        $html = '<div class="form-group">';
        $html .= '<label class="control-label">' . __('admin/manual.page_content.content') . '</label>';
        $html .= '<div class="nav-tabs-custom">';
        $html .= '<ul class="nav nav-tabs">';

        $first = true;
        foreach ($supportedLanguages as $langCode => $langName) {
            $active = $first ? 'active' : '';
            $html .= '<li class="' . $active . '"><a href="#content-' . $langCode . '" data-toggle="tab">' . $langName . '</a></li>';
            $first = false;
        }

        $html .= '</ul>';
        $html .= '<div class="tab-content">';

        $first = true;
        foreach ($supportedLanguages as $langCode => $langName) {
            $active = $first ? 'active' : '';
            $html .= '<div class="tab-pane ' . $active . '" id="content-' . $langCode . '">';

            if ($pageInfo) {
                $pageContent = $pageInfo->pageContents()->where('lang', $langCode)->first();
                $content = $pageContent ? $pageContent->content : '';
                $contentId = $pageContent ? $pageContent->id : null;

                $html .= '<input type="hidden" name="page_content_ids[' . $langCode . ']" value="' . ($contentId ?: '') . '">';
                $html .= '<textarea id="page_content_' . $langCode . '" name="page_contents[' . $langCode . ']" class="hugerte-editor">' . htmlspecialchars($content) . '</textarea>';
            } else {
                $html .= '<textarea id="page_content_' . $langCode . '" name="page_contents[' . $langCode . ']" class="hugerte-editor"></textarea>';
            }

            $html .= '</div>';
            $first = false;
        }

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        // Add HugerTE initialization script with file manager integration
        $html .= '<script>
            $(document).ready(function() {
                // Initialize HugerTE for all textareas with hugerte-editor class
                hugerte.init({
                    selector: ".hugerte-editor",
                    height: 400,
                    plugins: "image link table lists code",
                    toolbar: "undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | code",
                    file_picker_callback: function(callback, value, meta) {
                        if (meta.filetype === "image") {
                            window.open("/laravel-filemanager?type=Images", "fm", "width=900,height=600");
                            window.SetUrl = function(items) {
                                var file_path = items.map(function(x) { return x.url; }).join(",");
                                callback(file_path, { alt: "" });
                            };
                        } else if (meta.filetype === "file") {
                            window.open("/laravel-filemanager?type=Files", "fm", "width=900,height=600");
                            window.SetUrl = function(items) {
                                var file_path = items.map(function(x) { return x.url; }).join(",");
                                callback(file_path);
                            };
                        }
                    },
                    images_upload_url: "/laravel-filemanager/upload?type=Images&_token=' . csrf_token() . '",
                    images_upload_handler: function(blobInfo, progress) {
                        return new Promise(function(resolve, reject) {
                            var xhr, formData;
                            xhr = new XMLHttpRequest();
                            xhr.withCredentials = false;
                            xhr.open("POST", "/laravel-filemanager/upload?type=Images&_token=' . csrf_token() . '");
                            xhr.upload.onprogress = function(e) {
                                progress(e.loaded / e.total * 100);
                            };
                            xhr.onload = function() {
                                var json;
                                if (xhr.status === 403) {
                                    reject("HTTP Error: " + xhr.status);
                                    return;
                                }
                                if (xhr.status < 200 || xhr.status >= 300) {
                                    reject("HTTP Error: " + xhr.status);
                                    return;
                                }
                                json = JSON.parse(xhr.responseText);
                                if (!json || typeof json.location !== "string") {
                                    reject("Invalid JSON: " + xhr.responseText);
                                    return;
                                }
                                resolve(json.location);
                            };
                            xhr.onerror = function() {
                                reject("Image upload failed due to a XHR Transport error. Status: " + xhr.status);
                            };
                            formData = new FormData();
                            formData.append("upload", blobInfo.blob(), blobInfo.filename());
                            xhr.send(formData);
                        });
                    }
                });
            });
        </script>';

        return $html;
    }

    /**
     * Save page contents for all languages.
     *
     * @param ManualPageInfo $pageInfo
     * @return void
     */
    protected function savePageContents(ManualPageInfo $pageInfo)
    {
        $pageContents = request()->input('page_contents', []);
        $pageContentIds = request()->input('page_content_ids', []);

        foreach ($pageContents as $lang => $content) {
            if (!empty($content)) {
                // Process image paths for CDN support
                $content = $this->processImagePaths($content);

                $pageContentId = $pageContentIds[$lang] ?? null;

                if ($pageContentId) {
                    // Update existing
                    $pageContent = ManualPageContent::find($pageContentId);
                    if ($pageContent) {
                        $pageContent->update([
                            'content' => $content,
                        ]);
                    }
                } else {
                    // Create new
                    ManualPageContent::create([
                        'manual_page_info_id' => $pageInfo->id,
                        'lang' => $lang,
                        'content' => $content,
                    ]);
                }
            }
        }
    }

    /**
     * Process image paths for CDN support.
     *
     * @param string $content
     * @return string
     */
    protected function processImagePaths($content)
    {
        // This method can be extended to support CDN URL replacement
        // For now, it just returns the content as-is
        // In the future, you can implement logic like:
        // $content = str_replace('/storage/laravel-filemanager/', config('manual.image_base_url'), $content);

        return $content;
    }
}

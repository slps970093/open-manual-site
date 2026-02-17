<?php

namespace App\Http\Controllers;

use App\Models\Manual;
use App\Models\ManualMenu;
use App\Models\ManualPageInfo;
use App\Models\ManualPageContent;
use App\Services\PageContentService;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    /**
     * Display the homepage with all public manuals.
     *
     * Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 4.1, 4.2
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get language from request parameter or use default
        $requestedLang = $request->query('lang');

        // Get supported languages from config
        $supportedLanguages = array_keys(config('manual.supported_languages', []));

        // Validate and set the language
        if ($requestedLang && in_array($requestedLang, $supportedLanguages)) {
            app()->setLocale($requestedLang);
        }

        $currentLang = app()->getLocale();

        // Get all public manuals with eager loading to avoid N+1 queries
        // Filter by language - only show manuals that have translations in the current language
        $manuals = Manual::where('is_public', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($manual) use ($currentLang) {
                // Only include manuals that have a translation in the current language
                return $manual->hasTranslation('name', $currentLang);
            })
            ->values();

        return view('frontend.index', compact('manuals'));
    }

    /**
     * Display a specific manual with its menu tree.
     *
     * Validates: Requirements 1.1, 1.3, 1.4, 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 4.1
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function manual($slug)
    {
        // Get language from request parameter or use default
        $requestedLang = request()->query('lang');

        // Get supported languages from config
        $supportedLanguages = array_keys(config('manual.supported_languages', []));

        // Validate and set the language
        if ($requestedLang && in_array($requestedLang, $supportedLanguages)) {
            app()->setLocale($requestedLang);
        }

        // Get the manual by URL slug and verify it's public
        // Returns 404 if manual doesn't exist or is not public
        $manual = Manual::where('url_slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        // Get current language
        $currentLang = app()->getLocale();

        // Get root menu items with eager loading to avoid N+1 queries
        $menus = ManualMenu::where('manual_id', $manual->id)
            ->whereNull('parent_id')
            ->with('pageInfo')
            ->orderBy('position', 'asc')
            ->get();

        // Load all descendants with their pageInfo relationships
        // This ensures all menu items have their pageInfo loaded
        foreach ($menus as $menu) {
            $this->loadMenuDescendants($menu);
        }

        // Get current page ID from session or request (if coming from a page view)
        $currentPageId = session('current_page_id', null);

        // Build breadcrumbs for the manual detail page
        // On the manual detail page, breadcrumbs show: Home > Manual Name
        $breadcrumbs = [
            [
                'name' => $manual->getTranslation('name', $currentLang),
                'url' => null
            ]
        ];

        return view('frontend.manual', compact('manual', 'menus', 'currentLang', 'currentPageId', 'breadcrumbs'));
    }

    /**
     * Recursively load all descendants with their pageInfo relationships
     */
    private function loadMenuDescendants(ManualMenu $menu)
    {
        // Load direct children with pageInfo
        $children = $menu->children()->with('pageInfo')->orderBy('position')->get();
        $menu->setRelation('children', $children);

        // Recursively load descendants for each child
        foreach ($children as $child) {
            $this->loadMenuDescendants($child);
        }
    }

    /**
     * Display a specific page content.
     *
     * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 4.1, 4.2, 4.3, 6.1, 6.2, 6.4, 6.6
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function page($slug, $id)
    {
        // Get language from request parameter or use default
        $requestedLang = request()->query('lang');

        // Get supported languages from config
        $supportedLanguages = array_keys(config('manual.supported_languages', []));

        // Validate and set the language
        if ($requestedLang && in_array($requestedLang, $supportedLanguages)) {
            app()->setLocale($requestedLang);
        }

        // Get the manual by URL slug and verify it's public
        $manual = Manual::where('url_slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        // Get the page info
        $pageInfo = ManualPageInfo::findOrFail($id);

        // Verify the page belongs to this manual
        if ($pageInfo->manual_id !== $manual->id) {
            abort(404, 'Page not found in this manual');
        }

        // Get the current language
        $currentLang = app()->getLocale();

        // Get page content in the current language
        $content = $pageInfo->pageContents()
            ->where('lang', $currentLang)
            ->first();

        // If content not found in current language, try to find any available language
        // Validates: Requirements 5.3, 11.3 (fallback behavior for missing translations)
        if (!$content) {
            $content = $pageInfo->pageContents()->first();

            // If still no content found, return 404 with detailed error
            if (!$content) {
                $availableLangs = $pageInfo->pageContents()->pluck('lang')->implode(', ');
                \Log::warning("No content available for page {$id} in manual {$slug}. Available languages: {$availableLangs}");
                abort(404, "No content available for this page. Please ensure the page has content in at least one language.");
            }
        }

        // Process page content: clean HTML, convert Markdown, add responsive classes
        // Validates: Requirements 6.2, 6.4, 6.6
        $processedContent = $content->replicate();
        $processedContent->content = PageContentService::process($content->content, 'html');

        // Get root menu items for sidebar navigation
        $menus = $manual->menus()
            ->whereNull('parent_id')
            ->with('pageInfo')
            ->orderBy('position')
            ->get();

        // Load all descendants with their pageInfo relationships
        foreach ($menus as $menu) {
            $this->loadMenuDescendants($menu);
        }

        // Set the current page ID in session for menu highlighting
        session(['current_page_id' => $id]);

        // Build breadcrumb trail from menu hierarchy
        // Validates: Properties 6, 7, 8
        $breadcrumbs = $this->buildBreadcrumbs($pageInfo, $currentLang);

        return view('frontend.page', compact('pageInfo', 'manual', 'menus', 'processedContent', 'breadcrumbs', 'currentLang', 'id'));
    }

    /**
     * Search for pages in the current manual or across all manuals.
     *
     * Validates: Requirements 5.1, 5.2, 5.3, 5.4, 5.5, 5.6
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $manualSlug = $request->input('manual', null);

        // Validate query
        if (strlen($query) < 2) {
            return response()->json([
                'results' => [],
                'message' => 'Search query must be at least 2 characters'
            ]);
        }

        $currentLang = app()->getLocale();

        // Build the search query
        $results = ManualPageContent::query()
            ->where('lang', $currentLang)
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(content) LIKE ?', ['%' . strtolower($query) . '%']);
            });

        // Filter by manual if specified
        if ($manualSlug) {
            $manual = Manual::where('url_slug', $manualSlug)
                ->where('is_public', true)
                ->firstOrFail();

            $results = $results->whereHas('pageInfo', function ($q) use ($manual) {
                $q->where('manual_id', $manual->id);
            });
        } else {
            // Only search in public manuals
            $results = $results->whereHas('pageInfo.manual', function ($q) {
                $q->where('is_public', true);
            });
        }

        $results = $results->with('pageInfo.manual')
            ->limit(20)
            ->get();

        // Format results for response
        $formattedResults = $results->map(function ($content) use ($query) {
            $pageInfo = $content->pageInfo;
            $manual = $pageInfo->manual;

            // Extract snippet around the match
            $snippet = $this->extractSnippet($content->content, $query, 150);

            return [
                'id' => $pageInfo->id,
                'title' => $pageInfo->getTranslation('title', app()->getLocale()),
                'manual' => $manual->getTranslation('name', app()->getLocale()),
                'manual_slug' => $manual->url_slug,
                'snippet' => $snippet,
                'url' => route('frontend.page', ['slug' => $manual->url_slug, 'id' => $pageInfo->id]),
            ];
        });

        // Return JSON for AJAX requests
        if ($request->wantsJson()) {
            return response()->json([
                'results' => $formattedResults,
                'total' => count($formattedResults)
            ]);
        }

        // Return view for regular requests
        return view('frontend.search', [
            'query' => $query,
            'results' => $formattedResults,
            'manual_slug' => $manualSlug
        ]);
    }

    /**
     * Build breadcrumb trail for a page based on menu hierarchy.
     *
     * Validates: Properties 6, 7, 8
     *
     * @param ManualPageInfo $pageInfo
     * @param string $currentLang
     * @return array
     */
    private function buildBreadcrumbs(ManualPageInfo $pageInfo, $currentLang)
    {
        $breadcrumbs = [];

        $manual = $pageInfo->manual;

        // Find the menu item that references this page
        $menuItem = $manual->menus()
            ->where('manual_page_info_id', $pageInfo->id)
            ->first();

        if ($menuItem) {
            // Get all ancestors of this menu item, ordered by depth (root first)
            $ancestors = $menuItem->ancestors()
                ->orderBy('depth', 'asc')
                ->get();

            // Add ancestors to breadcrumbs
            foreach ($ancestors as $ancestor) {
                $ancestorLink = null;

                // Only add link if ancestor has a page and click_action is 'page'
                if ($ancestor->click_action === 'page' && $ancestor->pageInfo) {
                    $ancestorLink = route('frontend.page', ['slug' => $manual->url_slug, 'id' => $ancestor->pageInfo->id]);
                }

                $breadcrumbs[] = [
                    'name' => $ancestor->getTranslation('name', $currentLang),
                    'url' => $ancestorLink
                ];
            }

            // Add the current menu item (no link for current item)
            $breadcrumbs[] = [
                'name' => $menuItem->getTranslation('name', $currentLang),
                'url' => null
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Extract a snippet from content around the search query.
     *
     * @param string $content
     * @param string $query
     * @param int $length
     * @return string
     */
    private function extractSnippet($content, $query, $length = 150)
    {
        $pos = stripos($content, $query);

        if ($pos === false) {
            return substr($content, 0, $length) . '...';
        }

        $start = max(0, $pos - $length / 2);
        $snippet = substr($content, $start, $length);

        if ($start > 0) {
            $snippet = '...' . $snippet;
        }

        if ($start + $length < strlen($content)) {
            $snippet = $snippet . '...';
        }

        return $snippet;
    }
}

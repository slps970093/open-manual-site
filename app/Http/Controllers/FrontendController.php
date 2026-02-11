<?php

namespace App\Http\Controllers;

use App\Models\Manual;
use App\Models\ManualPageInfo;
use App\Models\ManualPageContent;
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
     * Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function manual($slug)
    {
        // Get the manual by URL slug
        $manual = Manual::where('url_slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        // Get root menu items with their children (ClosureTable provides children() relationship)
        $menus = $manual->menus()
            ->whereNull('parent_id')
            ->with('pageInfo')
            ->orderBy('position')
            ->get();

        return view('frontend.manual', compact('manual', 'menus'));
    }

    /**
     * Display a specific page content.
     *
     * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function page($id)
    {
        // Get the page info
        $pageInfo = ManualPageInfo::findOrFail($id);
        $manual = $pageInfo->manual;

        // Check if manual is public
        if (!$manual->is_public) {
            abort(403, 'This manual is not public');
        }

        // Get the current language
        $currentLang = app()->getLocale();

        // Get page content in the current language
        $content = $pageInfo->pageContents()
            ->where('lang', $currentLang)
            ->first();

        // If content not found in current language, show error
        if (!$content) {
            abort(404, "Content not available in {$currentLang}");
        }

        // Get root menu items for sidebar navigation
        $menus = $manual->menus()
            ->whereNull('parent_id')
            ->with('pageInfo')
            ->orderBy('position')
            ->get();

        // Build breadcrumb trail from menu hierarchy
        $breadcrumbs = $this->buildBreadcrumbs($pageInfo);

        return view('frontend.page', compact('pageInfo', 'manual', 'menus', 'content', 'breadcrumbs'));
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
                'url' => route('frontend.page', ['id' => $pageInfo->id]),
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
     * @param ManualPageInfo $pageInfo
     * @return array
     */
    private function buildBreadcrumbs(ManualPageInfo $pageInfo)
    {
        $breadcrumbs = [
            [
                'name' => 'Home',
                'url' => route('frontend.index')
            ]
        ];

        $manual = $pageInfo->manual;
        $breadcrumbs[] = [
            'name' => $manual->getTranslation('name', app()->getLocale()),
            'url' => route('frontend.manual', ['slug' => $manual->url_slug])
        ];

        // Find the menu item that references this page
        $menuItem = $manual->menus()
            ->where('manual_page_info_id', $pageInfo->id)
            ->first();

        if ($menuItem) {
            // Get all ancestors of this menu item
            $ancestors = $menuItem->ancestors()
                ->orderBy('depth')
                ->get();

            foreach ($ancestors as $ancestor) {
                $breadcrumbs[] = [
                    'name' => $ancestor->getTranslation('name', app()->getLocale()),
                    'url' => $ancestor->click_action === 'page' && $ancestor->pageInfo
                        ? route('frontend.page', ['id' => $ancestor->pageInfo->id])
                        : null
                ];
            }

            // Add the current menu item
            $breadcrumbs[] = [
                'name' => $menuItem->getTranslation('name', app()->getLocale()),
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

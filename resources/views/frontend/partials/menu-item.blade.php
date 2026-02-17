@php
    // Check if item has children - use loaded children if available, otherwise query
    $hasChildren = false;
    if (isset($item->children) && $item->children instanceof \Illuminate\Database\Eloquent\Collection) {
        $hasChildren = $item->children->count() > 0;
    } else {
        $hasChildren = $item->children()->exists();
    }
    
    $currentLang = $currentLang ?? app()->getLocale();
    $currentPageId = $currentPageId ?? null;
    $manualSlug = $manualSlug ?? null;
    $itemName = $item->getTranslation('name', $currentLang);
    $itemLink = null;
    $isExternal = false;
    $isActive = false;
    $shouldExpand = false;

    // Determine the link based on click_action
    if ($item->click_action === 'page' && $item->pageInfo) {
        $itemLink = route('frontend.page', ['slug' => $manualSlug, 'id' => $item->pageInfo->id]);
        // Check if this is the active menu item
        if ($currentPageId && $item->manual_page_info_id === $currentPageId) {
            $isActive = true;
            $shouldExpand = true;
        }
    } elseif ($item->click_action === 'external' && $item->url) {
        $itemLink = $item->url;
        $isExternal = true;
    } elseif ($item->click_action === 'expand') {
        // Expand type: no link, just toggle
        $itemLink = null;
    }
    
    // Debug: Log menu item information
    if ($item->click_action === 'page' && !$item->pageInfo) {
        \Log::warning("Menu item {$item->id} has click_action='page' but pageInfo is null. manual_page_info_id={$item->manual_page_info_id}");
    }

    // Check if any descendant is active (for auto-expand)
    // This implements Property 5: Active Menu Item Highlighting
    if (!$isActive && $currentPageId && $hasChildren) {
        // Get children - use pre-loaded if available
        $children = isset($item->children) && $item->children instanceof \Illuminate\Database\Eloquent\Collection
            ? $item->children
            : $item->children()->get();
        
        // Check if any child or deeper descendant is active
        foreach ($children as $child) {
            if ($child->manual_page_info_id === $currentPageId) {
                $shouldExpand = true;
                break;
            }
        }
    }
@endphp

<li class="menu-tree-item" data-item-id="{{ $item->id }}" role="treeitem" aria-expanded="{{ $shouldExpand ? 'true' : 'false' }}">
    <div class="menu-tree-item-content @if($isActive) active @endif" @if($itemLink) data-link="{{ $itemLink }}" @endif data-click-action="{{ $item->click_action }}">
        @if($hasChildren)
            <button
                class="menu-tree-toggle @if($shouldExpand) expanded @endif"
                type="button"
                aria-label="Toggle submenu"
                aria-expanded="{{ $shouldExpand ? 'true' : 'false' }}"
            >
                <i class="fas fa-chevron-right"></i>
            </button>
        @else
            <button class="menu-tree-toggle" type="button" disabled aria-hidden="true"></button>
        @endif

        <span class="menu-tree-item-label">{{ $itemName }}</span>

        @if($isExternal)
            <span class="menu-tree-item-icon" title="External link">
                <i class="fas fa-external-link-alt"></i>
            </span>
        @endif
    </div>

    @if($hasChildren)
        <ul class="menu-tree-children @if($shouldExpand) expanded @endif" role="group">
            @php
                // Use pre-loaded children if available, otherwise query
                if (isset($item->children) && $item->children instanceof \Illuminate\Database\Eloquent\Collection) {
                    $children = $item->children->sortBy('position');
                } else {
                    $children = $item->children()->with('pageInfo')->orderBy('position')->get();
                }
            @endphp
            @foreach($children as $child)
                @include('frontend.partials.menu-item', ['item' => $child, 'level' => ($level ?? 0) + 1, 'currentLang' => $currentLang, 'currentPageId' => $currentPageId, 'manualSlug' => $manualSlug])
            @endforeach
        </ul>
    @endif
</li>

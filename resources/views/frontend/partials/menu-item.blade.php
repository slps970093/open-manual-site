@php
    $hasChildren = $item->children()->exists();
    $currentLang = $currentLang ?? app()->getLocale();
    $itemName = $item->getTranslation('name', $currentLang);
    $itemLink = null;
    $isExternal = false;

    // Determine the link based on click_action
    if ($item->click_action === 'page' && $item->pageInfo) {
        $itemLink = route('frontend.page', ['id' => $item->pageInfo->id]);
    } elseif ($item->click_action === 'external' && $item->url) {
        $itemLink = $item->url;
        $isExternal = true;
    }
@endphp

<li class="menu-tree-item" data-item-id="{{ $item->id }}" role="treeitem" aria-expanded="false">
    <div class="menu-tree-item-content" @if($itemLink) data-link="{{ $itemLink }}" @endif>
        @if($hasChildren)
            <button
                class="menu-tree-toggle"
                type="button"
                aria-label="Toggle submenu"
                aria-expanded="false"
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
        <ul class="menu-tree-children" role="group">
            @foreach($item->children()->orderBy('position')->get() as $child)
                @include('frontend.partials.menu-item', ['item' => $child, 'level' => ($level ?? 0) + 1, 'currentLang' => $currentLang])
            @endforeach
        </ul>
    @endif
</li>

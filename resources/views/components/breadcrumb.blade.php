@php
    /**
     * Breadcrumb Navigation Component
     *
     * Displays the user's current location in the menu tree hierarchy.
     * Shows the manual name (linked to manual homepage) and the complete path
     * from root to current item using > as separator.
     *
     * Validates: Requirements 4.1, 4.2, 4.4
     * Validates: Properties 6, 7, 8
     *
     * @param array $breadcrumbs - Array of breadcrumb items with 'name' and 'url' keys
     * @param string $manualSlug - The manual URL slug for linking back to manual
     * @param bool $simplified - Whether to show simplified version (mobile)
     */

    $breadcrumbs = $breadcrumbs ?? [];
    $manualSlug = $manualSlug ?? null;
    $simplified = $simplified ?? false;
@endphp

<nav aria-label="breadcrumb" class="breadcrumb-nav">
    <ol class="breadcrumb mb-0" style="font-size: 0.9rem;">
        {{-- Manual Home Link --}}
        <li class="breadcrumb-item">
            <a href="{{ route('frontend.manuals') }}" class="text-decoration-none" style="color: var(--primary-color);">
                <i class="fas fa-home"></i>
                <span class="d-none d-sm-inline ms-1">首頁</span>
            </a>
        </li>

        {{-- Breadcrumb Items --}}
        @forelse($breadcrumbs as $index => $item)
            @php
                $isLast = $index === count($breadcrumbs) - 1;
                $isSecondLast = $index === count($breadcrumbs) - 2;
                $shouldSkip = $simplified && !$isLast && !$isSecondLast;
                $showEllipsis = $simplified && $isSecondLast && count($breadcrumbs) > 2;
            @endphp

            @if($showEllipsis)
                <li class="breadcrumb-item"><span class="text-muted">...</span></li>
            @endif

            @if(!$shouldSkip)
                <li class="breadcrumb-item @if($isLast) active @endif">
                    @if($item['url'] && !$isLast)
                        <a href="{{ $item['url'] }}" class="text-decoration-none breadcrumb-link" style="color: var(--primary-color);">
                            {{ $item['name'] }}
                        </a>
                    @else
                        <span class="@if($isLast) text-dark fw-500 @else text-muted @endif">
                            {{ $item['name'] }}
                        </span>
                    @endif
                </li>
            @endif
        @empty
            {{-- No breadcrumbs provided --}}
        @endforelse
    </ol>
</nav>

<style>
    .breadcrumb-nav {
        padding: 0;
        margin: 0;
    }

    .breadcrumb {
        background-color: transparent;
        padding: 0;
        margin: 0;
    }

    .breadcrumb-item {
        font-size: 0.9rem;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: ">";
        padding: 0 0.5rem;
        color: var(--text-light);
    }

    .breadcrumb-link {
        transition: color 0.2s;
        cursor: pointer;
    }

    .breadcrumb-link:hover {
        color: var(--primary-color-dark, #1e40af) !important;
        text-decoration: underline !important;
    }

    .breadcrumb-link:focus {
        outline: 2px solid var(--primary-color);
        outline-offset: 2px;
        border-radius: 2px;
    }

    .breadcrumb-item.active {
        color: var(--text-dark);
    }

    @media (max-width: 576px) {
        .breadcrumb-item {
            font-size: 0.85rem;
        }
    }
</style>

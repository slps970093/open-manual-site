/**
 * Search Manager Module
 * Handles search functionality for manual cards
 */

export function initSearchManager(searchInputSelector, manualGridSelector, emptySearchStateSelector) {
    const searchInput = document.querySelector(searchInputSelector);
    const manualGrid = document.querySelector(manualGridSelector);
    const emptySearchState = document.querySelector(emptySearchStateSelector);

    if (!searchInput || !manualGrid) return;

    const manualCards = document.querySelectorAll('.manual-card-wrapper');

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let visibleCount = 0;

        manualCards.forEach(card => {
            const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
            const description = card.querySelector('.card-text')?.textContent.toLowerCase() || '';

            if (title.includes(searchTerm) || description.includes(searchTerm)) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Show/hide empty state
        if (visibleCount === 0 && searchTerm.length > 0) {
            manualGrid.style.display = 'none';
            if (emptySearchState) {
                emptySearchState.style.display = 'block';
            }
        } else {
            manualGrid.style.display = 'grid';
            if (emptySearchState) {
                emptySearchState.style.display = 'none';
            }
        }

        // Update URL search parameter
        if (searchTerm) {
            const url = new URL(window.location);
            url.searchParams.set('q', searchTerm);
            window.history.replaceState({}, '', url);
        } else {
            const url = new URL(window.location);
            url.searchParams.delete('q');
            window.history.replaceState({}, '', url);
        }
    });

    // Restore search query from URL
    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('q');
    if (searchQuery) {
        searchInput.value = searchQuery;
        searchInput.dispatchEvent(new Event('input'));
    }
}

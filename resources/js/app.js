import './bootstrap';
import * as bootstrap from 'bootstrap';
import { initSearchManager } from './search-manager';

// Initialize search manager for manual cards (index page only)
document.addEventListener('DOMContentLoaded', function() {
    initSearchManager(
        '#searchInput',
        '#manualGrid',
        '#emptySearchState'
    );
});

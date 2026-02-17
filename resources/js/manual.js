import './bootstrap';
import * as bootstrap from 'bootstrap';
import { initLanguageSwitcher } from './language-switcher';
import { initMenuManager } from './menu-manager';

// Initialize all modules for manual pages when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize language switcher
    initLanguageSwitcher();

    // Initialize menu manager for desktop menu
    initMenuManager(
        '#menuTree',
        '#menuSearchInput',
        '#mobileMenuBtn',
        '#mobileSidebar'
    );

    // Initialize menu manager for mobile menu
    initMenuManager(
        '#mobileMenuTree',
        '#mobileMenuSearchInput',
        null,
        null
    );
});

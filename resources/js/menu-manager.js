/**
 * Menu Manager Module
 * Handles menu tree interactions, search, and state management
 */

export function initMenuManager(menuTreeSelector, searchInputSelector, mobileMenuBtnSelector, mobileSidebarSelector) {
    const menuTree = document.querySelector(menuTreeSelector);
    const searchInput = document.querySelector(searchInputSelector);
    const mobileMenuBtn = document.querySelector(mobileMenuBtnSelector);
    const mobileSidebar = document.querySelector(mobileSidebarSelector);

    if (!menuTree) return;

    // Setup menu search
    if (searchInput) {
        setupSearch(searchInput, menuTree);
    }

    // Setup menu item click handlers
    setupMenuItems(menuTree);

    // Restore expand/collapse state from localStorage
    restoreExpandState(menuTree);

    // Ensure active menu item and its ancestors are expanded
    ensureActiveMenuItemExpanded(menuTree);

    // Setup mobile menu toggle
    if (mobileMenuBtn && mobileSidebar) {
        setupMobileMenu(mobileMenuBtn, mobileSidebar);
    }
}

function setupSearch(searchInput, menuTree) {
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterMenuItems(searchTerm, menuTree);
    });
}

function filterMenuItems(searchTerm, menuTree) {
    const items = menuTree.querySelectorAll('.menu-tree-item');
    let visibleCount = 0;

    items.forEach(item => {
        const label = item.querySelector('.menu-tree-item-label');
        const text = label ? label.textContent.toLowerCase() : '';
        const isMatch = text.includes(searchTerm);

        if (searchTerm === '') {
            item.style.display = '';
        } else if (isMatch) {
            item.style.display = '';
            visibleCount++;
            expandAncestors(item, menuTree);
        } else {
            item.style.display = 'none';
        }
    });

    if (visibleCount === 0 && searchTerm.length > 0) {
        menuTree.style.display = 'none';
    } else {
        menuTree.style.display = '';
    }
}

function expandAncestors(item, menuTree) {
    let parent = item.parentElement;
    while (parent && parent !== menuTree) {
        if (parent.classList.contains('menu-tree-children')) {
            parent.classList.add('expanded');
            const toggle = parent.previousElementSibling.querySelector('.menu-tree-toggle');
            if (toggle) {
                toggle.classList.add('expanded');
            }
        }
        parent = parent.parentElement;
    }
}

function setupMenuItems(menuTree) {
    menuTree.querySelectorAll('.menu-tree-item-content').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const toggle = this.querySelector('.menu-tree-toggle');
            const children = this.nextElementSibling;
            const menuItem = this.closest('.menu-tree-item');
            const itemId = menuItem ? menuItem.dataset.itemId : null;

            // If it's a toggle button, expand/collapse
            if (toggle && !toggle.disabled) {
                const isExpanded = toggle.classList.contains('expanded');
                toggle.classList.toggle('expanded');
                if (children && children.classList.contains('menu-tree-children')) {
                    children.classList.toggle('expanded');
                }
                // Save state to localStorage
                if (itemId) {
                    saveExpandState(itemId, !isExpanded);
                }
                // Update ARIA attribute
                if (menuItem) {
                    menuItem.setAttribute('aria-expanded', !isExpanded);
                }
            }

            // If it has a link, navigate
            const link = this.dataset.link;
            if (link) {
                window.location.href = link;
            }
        });
    });

    menuTree.querySelectorAll('.menu-tree-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isExpanded = this.classList.contains('expanded');
            this.classList.toggle('expanded');
            const children = this.closest('.menu-tree-item-content').nextElementSibling;
            if (children && children.classList.contains('menu-tree-children')) {
                children.classList.toggle('expanded');
            }
            // Save state to localStorage
            const menuItem = this.closest('.menu-tree-item');
            if (menuItem) {
                const itemId = menuItem.dataset.itemId;
                if (itemId) {
                    saveExpandState(itemId, !isExpanded);
                }
                // Update ARIA attribute
                menuItem.setAttribute('aria-expanded', !isExpanded);
            }
        });
    });
}

function saveExpandState(itemId, isExpanded) {
    const expandedItems = JSON.parse(localStorage.getItem('menuExpandedItems') || '{}');
    if (isExpanded) {
        expandedItems[itemId] = true;
    } else {
        delete expandedItems[itemId];
    }
    localStorage.setItem('menuExpandedItems', JSON.stringify(expandedItems));
}

function restoreExpandState(menuTree) {
    const expandedItems = JSON.parse(localStorage.getItem('menuExpandedItems') || '{}');

    menuTree.querySelectorAll('.menu-tree-item').forEach(item => {
        const itemId = item.dataset.itemId;
        if (itemId && expandedItems[itemId]) {
            const toggle = item.querySelector('.menu-tree-toggle');
            const children = item.querySelector('.menu-tree-children');

            if (toggle && !toggle.disabled) {
                toggle.classList.add('expanded');
                item.setAttribute('aria-expanded', 'true');
            }
            if (children) {
                children.classList.add('expanded');
            }
        }
    });
}

function ensureActiveMenuItemExpanded(menuTree) {
    const activeItem = menuTree.querySelector('.menu-tree-item-content.active');
    if (activeItem) {
        // Expand all ancestors of the active item
        let parent = activeItem.closest('.menu-tree-item').parentElement;
        while (parent && parent !== menuTree) {
            if (parent.classList.contains('menu-tree-children')) {
                parent.classList.add('expanded');
                const toggle = parent.previousElementSibling.querySelector('.menu-tree-toggle');
                if (toggle && !toggle.disabled) {
                    toggle.classList.add('expanded');
                }
            }
            parent = parent.parentElement;
        }
    }
}

function setupMobileMenu(mobileMenuBtn, mobileSidebar) {
    // Auto-open sidebar on mobile for detail pages
    const isMobile = window.innerWidth < 768;
    if (isMobile) {
        mobileSidebar.style.transform = 'translateX(0px)';
    }

    // Mobile menu toggle
    mobileMenuBtn.addEventListener('click', function() {
        const isOpen = mobileSidebar.style.transform === 'translateX(0px)';
        mobileSidebar.style.transform = isOpen ? 'translateX(-100%)' : 'translateX(0px)';
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        const isOpen = mobileSidebar.style.transform === 'translateX(0px)';
        if (isOpen && !mobileSidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
            mobileSidebar.style.transform = 'translateX(-100%)';
        }
    });
}

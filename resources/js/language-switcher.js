/**
 * Language Switcher Module
 * Handles language selection and page reload with language parameter
 */

export function initLanguageSwitcher() {
    const languageSelectors = document.querySelectorAll('.language-selector');

    languageSelectors.forEach(selector => {
        // Restore language preference from localStorage
        const savedLanguage = localStorage.getItem('preferredLanguage');
        if (savedLanguage) {
            selector.value = savedLanguage;
        }

        // Handle language change
        selector.addEventListener('change', function() {
            const selectedLanguage = this.value;
            localStorage.setItem('preferredLanguage', selectedLanguage);

            // Reload page with language parameter
            const url = new URL(window.location);
            url.searchParams.set('lang', selectedLanguage);
            window.location.href = url.toString();
        });
    });
}

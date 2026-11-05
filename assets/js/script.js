const themeStorageKey = 'school-dental-theme';
let currentTheme = 'light';

try {
    currentTheme = localStorage.getItem(themeStorageKey)
        || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
} catch (error) {
    currentTheme = 'light';
}
document.documentElement.dataset.theme = currentTheme;

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[name="appointment_date"]').forEach(function (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    });

    let themeToggle = document.querySelector('[data-theme-toggle]');
    if (!themeToggle) {
        themeToggle = document.createElement('button');
        themeToggle.type = 'button';
        themeToggle.className = 'theme-toggle theme-floating';
        themeToggle.dataset.themeToggle = '';
        themeToggle.innerHTML = '<span data-theme-icon></span><span data-theme-label></span>';
        document.body.append(themeToggle);
    }

    function renderThemeToggle() {
        const isDark = document.documentElement.dataset.theme === 'dark';
        const label = isDark ? 'Switch to light mode' : 'Switch to dark mode';
        themeToggle.setAttribute('aria-label', label);
        themeToggle.setAttribute('title', label);
        themeToggle.setAttribute('aria-pressed', String(isDark));
        const labelElement = themeToggle.querySelector('[data-theme-label]');
        if (labelElement) labelElement.textContent = isDark ? 'Light mode' : 'Dark mode';
        const iconElement = themeToggle.querySelector('[data-theme-icon]');
        if (iconElement) iconElement.innerHTML = `<i data-lucide="${isDark ? 'sun' : 'moon'}"></i>`;
        if (window.lucide) window.lucide.createIcons();
    }

    renderThemeToggle();
    themeToggle.addEventListener('click', function () {
        currentTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = currentTheme;
        try {
            localStorage.setItem(themeStorageKey, currentTheme);
        } catch (error) {
            // The current page still changes theme when storage is unavailable.
        }
        renderThemeToggle();
    });

    if (window.lucide) window.lucide.createIcons();
});
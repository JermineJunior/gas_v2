<style>
    :root {
        color-scheme: light;
        --color-bg: #f4f8f3;
        --color-surface: #ffffff;
        --color-surface-2: #e9f1e7;
        --color-primary: #16a34a;
        --color-primary-strong: #15803d;
        --color-primary-soft: #dcfce7;
        --color-primary-softer: #f0fdf4;
        --color-accent: #f59e0b;
        --color-accent-strong: #b45309;
        --color-accent-soft: #fef3c7;
        --color-text: #1f2937;
        --color-text-muted: #6b7280;
        --color-heading: #111827;
        --color-border: #d1d5db;
        --color-divider: rgba(17, 24, 39, 0.12);
    }

    html[data-theme="dark"] {
        color-scheme: dark;
        --color-bg: #0c1410;
        --color-surface: #17241c;
        --color-surface-2: #1f3126;
        --color-primary: #22c55e;
        --color-primary-strong: #16a34a;
        --color-primary-soft: #14532d;
        --color-primary-softer: #052e16;
        --color-accent: #fbbf24;
        --color-accent-strong: #f59e0b;
        --color-accent-soft: #78350f;
        --color-text: #e5e7eb;
        --color-text-muted: #9ca3af;
        --color-heading: #f9fafb;
        --color-border: #374151;
        --color-divider: rgba(229, 231, 235, 0.14);
    }

    body {
        background-color: var(--color-bg) !important;
        color: var(--color-text) !important;
        transition: background-color .25s ease, color .25s ease;
    }

    [x-cloak] {
        display: none !important;
    }

    html[data-theme="dark"] .bg-white { background-color: var(--color-surface) !important; }
    html[data-theme="dark"] .bg-gray-50,
    html[data-theme="dark"] .bg-gray-100,
    html[data-theme="dark"] .bg-gray-200 { background-color: var(--color-surface-2) !important; }
    html[data-theme="dark"] .bg-gray-300 { background-color: var(--color-border) !important; }
    html[data-theme="dark"] .text-gray-500 { color: var(--color-text-muted) !important; }
    html[data-theme="dark"] .text-gray-600,
    html[data-theme="dark"] .text-gray-700 { color: var(--color-text) !important; }
    html[data-theme="dark"] .text-gray-300 { color: var(--color-text-muted) !important; }
    html[data-theme="dark"] .text-gray-800 { color: var(--color-heading) !important; }
    html[data-theme="dark"] .border-gray-200,
    html[data-theme="dark"] .border-gray-300,
    html[data-theme="dark"] .border { border-color: var(--color-border) !important; }
    html[data-theme="dark"] .bg-black { background-color: rgba(0, 0, 0, 0.75) !important; }

    html[data-theme="dark"] table td,
    html[data-theme="dark"] table th { color: var(--color-text) !important; }
    html[data-theme="dark"] table thead tr:not(.bg-green-600) th { color: var(--color-text) !important; }

    html[data-theme="dark"] table tbody tr:hover { background-color: transparent !important; }

    html[data-theme="dark"] .hover\:bg-gray-100:hover { background-color: var(--color-surface-2) !important; }

    html[data-theme="dark"] .select2-dropdown {
        background-color: var(--color-surface) !important;
        border-color: var(--color-border) !important;
    }
    html[data-theme="dark"] .select2-results__option {
        color: var(--color-text) !important;
        background-color: var(--color-surface) !important;
    }
    html[data-theme="dark"] .select2-results__option--highlighted,
    html[data-theme="dark"] .select2-results__option[aria-selected="true"] {
        background-color: var(--color-surface-2) !important;
        color: var(--color-text) !important;
    }
    html[data-theme="dark"] .select2-search--dropdown .select2-search__field {
        background-color: var(--color-surface-2) !important;
        color: var(--color-text) !important;
        border-color: var(--color-border) !important;
    }

    html[data-theme="dark"] .swal2-popup {
        background-color: var(--color-surface) !important;
        color: var(--color-text) !important;
    }
    html[data-theme="dark"] .swal2-title { color: var(--color-heading) !important; }
    html[data-theme="dark"] .swal2-html-container { color: var(--color-text) !important; }
    html[data-theme="dark"] .swal2-styled { border: 1px solid var(--color-border) !important; }
    html[data-theme="dark"] .swal2-close { color: var(--color-text-muted) !important; }
    html[data-theme="dark"] .swal2-timer-progress-bar { background: var(--color-primary) !important; }

    html[data-theme="dark"] input,
    html[data-theme="dark"] select,
    html[data-theme="dark"] textarea {
        background-color: var(--color-surface-2) !important;
        color: var(--color-text) !important;
        border-color: var(--color-border) !important;
    }
    html[data-theme="dark"] input::placeholder { color: var(--color-text-muted) !important; }

    html[data-theme="dark"] .bg-yellow-50,
    html[data-theme="dark"] .bg-yellow-100 { background-color: var(--color-accent-soft) !important; }
    html[data-theme="dark"] .text-yellow-700 { color: var(--color-accent) !important; }
    html[data-theme="dark"] .border-yellow-300 { border-color: var(--color-accent) !important; }

    html[data-theme="dark"] .bg-red-50 { background-color: #7f1d1d !important; }
    html[data-theme="dark"] .bg-red-100 { background-color: #7f1d1d !important; }
    html[data-theme="dark"] .bg-red-500 { background-color: #dc2626 !important; }
    html[data-theme="dark"] .bg-red-600 { background-color: #dc2626 !important; }
    html[data-theme="dark"] .bg-green-50 { background-color: var(--color-primary-softer) !important; }
    html[data-theme="dark"] .bg-green-100 { background-color: var(--color-primary-soft) !important; }
    html[data-theme="dark"] .bg-green-600 { background-color: var(--color-primary) !important; }
    html[data-theme="dark"] .bg-green-700 { background-color: var(--color-primary-strong) !important; }
    html[data-theme="dark"] .bg-amber-50 { background-color: #78350f !important; }
    html[data-theme="dark"] .bg-orange-100 { background-color: #7c2d12 !important; }
    html[data-theme="dark"] .text-red-200 { color: #fca5a5 !important; }
    html[data-theme="dark"] .text-red-600 { color: #f87171 !important; }
    html[data-theme="dark"] .text-red-700 { color: #fca5a5 !important; }
    html[data-theme="dark"] .text-red-800 { color: #f87171 !important; }
    html[data-theme="dark"] .text-green-200 { color: #86efac !important; }
    html[data-theme="dark"] .text-green-600 { color: var(--color-primary) !important; }
    html[data-theme="dark"] .text-green-700 { color: #86efac !important; }
    html[data-theme="dark"] .text-green-800 { color: var(--color-primary) !important; }
    html[data-theme="dark"] .text-orange-700 { color: #fdba74 !important; }
    html[data-theme="dark"] .border-green-200 { border-color: var(--color-primary-soft) !important; }
    html[data-theme="dark"] .border-amber-200 { border-color: #78350f !important; }
    html[data-theme="dark"] .border-red-200 { border-color: #7f1d1d !important; }
    html[data-theme="dark"] .hover\:bg-green-700:hover { background-color: var(--color-primary-strong) !important; }

    .bg-blue-600 { background-color: var(--color-primary) !important; }
    .hover\:bg-blue-700:hover { background-color: var(--color-primary-strong) !important; }
    .bg-blue-50 { background-color: var(--color-primary-softer) !important; }
    .hover\:bg-blue-50:hover { background-color: var(--color-primary-softer) !important; }
    .bg-blue-100 { background-color: var(--color-primary-soft) !important; }
    .hover\:bg-blue-200:hover { background-color: var(--color-primary) !important; }
    html[data-theme="dark"] .bg-blue-100\/30 { background-color: var(--color-primary-soft) !important; }
    .text-blue-600 { color: var(--color-primary) !important; }
    .hover\:text-blue-600:hover { color: var(--color-primary) !important; }
    .text-blue-700 { color: var(--color-primary) !important; }
    .text-blue-800 { color: var(--color-heading) !important; }
    .text-blue-200 { color: #86efac !important; }
    .border-blue-200 { border-color: var(--color-primary-soft) !important; }
    .focus\:border-blue-500:focus { border-color: var(--color-primary) !important; }
    .focus\:ring-blue-500:focus { --tw-ring-color: var(--color-primary) !important; }
    .from-blue-600 { --tw-gradient-from: var(--color-primary) !important; }
    .to-cyan-400 { --tw-gradient-to: var(--color-accent) !important; }

    html[data-theme="dark"] .nav-link.active { border-bottom-color: var(--color-accent) !important; }

    .theme-toggle {
        position: fixed;
        bottom: 1rem;
        left: 1rem;
        z-index: 9999;
        width: 2.75rem;
        height: 2.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 9999px;
        cursor: pointer;
        background-color: var(--color-primary);
        color: #fff;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
        transition: background-color .2s ease, transform .2s ease;
    }

    .theme-toggle:hover {
        background-color: var(--color-primary-strong);
        transform: translateY(-2px);
    }

    .theme-toggle:focus-visible {
        outline: 2px solid var(--color-accent);
        outline-offset: 2px;
    }

    .theme-toggle svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    html[data-theme="light"] .theme-toggle-moon { display: none; }
    html[data-theme="dark"] .theme-toggle-sun { display: none; }

    .bg-primary { background-color: var(--color-primary) !important; }
    .bg-primary-strong { background-color: var(--color-primary-strong) !important; }
    .bg-primary-soft { background-color: var(--color-primary-soft) !important; }
    .bg-primary-softer { background-color: var(--color-primary-softer) !important; }
    .bg-accent { background-color: var(--color-accent) !important; }
    .bg-accent-soft { background-color: var(--color-accent-soft) !important; }
    .bg-accent-strong { background-color: var(--color-accent-strong) !important; }
    .hover\:bg-accent-strong:hover { background-color: var(--color-accent-strong) !important; }
    .text-accent-strong { color: var(--color-accent-strong) !important; }
    .bg-surface { background-color: var(--color-surface) !important; }
    .bg-surface-2 { background-color: var(--color-surface-2) !important; }

    .hover\:bg-primary:hover { background-color: var(--color-primary) !important; }
    .hover\:bg-primary-strong:hover { background-color: var(--color-primary-strong) !important; }
    .hover\:bg-primary-soft:hover { background-color: var(--color-primary-soft) !important; }
    .hover\:bg-accent:hover { background-color: var(--color-accent) !important; }
    .hover\:bg-surface-2:hover { background-color: var(--color-surface-2) !important; }

    .text-primary { color: var(--color-primary) !important; }
    .text-primary-strong { color: var(--color-primary-strong) !important; }
    .text-heading { color: var(--color-heading) !important; }
    .text-accent { color: var(--color-accent) !important; }

    .hover\:text-primary:hover { color: var(--color-primary) !important; }
    .hover\:text-primary-strong:hover { color: var(--color-primary-strong) !important; }

    .border-primary { border-color: var(--color-primary) !important; }
    .border-primary-soft { border-color: var(--color-primary-soft) !important; }
    .border-surface-border { border-color: var(--color-border) !important; }

    .focus\:border-primary:focus { border-color: var(--color-primary) !important; }
    .focus\:ring-primary:focus { --tw-ring-color: var(--color-primary) !important; }
    .focus\:ring-primary-strong:focus { --tw-ring-color: var(--color-primary-strong) !important; }

    @media print {
        .theme-toggle { display: none !important; }
    }
</style>

<script>
    (function () {
        var theme = null;
        try { theme = localStorage.getItem('theme'); } catch (e) {}
        if (theme !== 'dark' && theme !== 'light') theme = 'light';
        document.documentElement.setAttribute('data-theme', theme);

        window.__toggleTheme = function () {
            var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
        };
    })();
</script>

<button type="button" id="themeToggle" class="theme-toggle"
    onclick="window.__toggleTheme && window.__toggleTheme();"
    aria-label="تبديل المظهر" title="تبديل المظهر">
    <svg class="theme-toggle-sun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25M12 18.75V21M3 12h2.25M18.75 12H21M5.636 5.636l1.591 1.591M16.773 16.773l1.591 1.591M5.636 18.364l1.591-1.591M16.773 7.227l1.591-1.591M12 8.25a3.75 3.75 0 100 7.5 3.75 3.75 0 000-7.5z" />
    </svg>
    <svg class="theme-toggle-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
    </svg>
</button>

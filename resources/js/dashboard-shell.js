const storageKey = 'ds_sidebar_collapsed';

/**
 * Alpine factory for project-list TomSelect filters.
 * Avoids embedding @json() inside x-data="…" (breaks the HTML attribute).
 *
 * Usage: x-data="dsTomSelectFilter({ wireProp: 'filterCommune', placeholder: @js(__('…')) })"
 */
window.dsTomSelectFilter = function dsTomSelectFilter({ wireProp, placeholder = '' } = {}) {
    return {
        ts: null,
        init() {
            const TomSelect = window.TomSelect;
            if (! TomSelect || ! this.$refs.select) {
                return;
            }

            this.ts = new TomSelect(this.$refs.select, {
                dropdownParent: 'body',
                maxOptions: null,
                placeholder,
                sortField: { field: 'text', direction: 'asc' },
                onChange: (v) => {
                    this.$wire.set(wireProp, v || '');
                    setTimeout(() => this.ts?.blur(), 50);
                },
            });

            const current = this.$wire.get?.(wireProp) ?? this.$wire[wireProp];
            if (current) {
                this.ts.setValue(current);
            }

            window.addEventListener('clear-filters', () => this.ts?.clear());
        },
    };
};

function readCollapsedState() {
    try {
        return localStorage.getItem(storageKey) === '1';
    } catch {
        return false;
    }
}

function writeCollapsedState(isCollapsed) {
    try {
        localStorage.setItem(storageKey, isCollapsed ? '1' : '0');
    } catch {
        // Ignore storage failures (private mode / blocked storage).
    }
}

function initDashboardShell() {
    const root = document.getElementById('dashboard-shell');
    if (!root) return;

    // Re-sync state on each init (important with Livewire DOM updates).
    root.classList.toggle('ds-sidebar-collapsed', readCollapsedState());

    // Use a window-level flag so the document listener is registered only once.
    if (window.__dsShellInitialized) return;
    window.__dsShellInitialized = true;

    let activeProfileMenu = null;

    function closeProfileMenu(menu) {
        if (!menu) return;
        menu.classList.remove('ds-open');
        const btn = menu.closest('[data-ds-profile-toggle]')?.querySelector('button');
        if (btn) btn.setAttribute('aria-expanded', 'false');
    }

    // Delegate on document — survives Livewire morphdom updates that might
    // replace the #dashboard-shell node.
    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) return;

        const root = document.getElementById('dashboard-shell');
        if (!root) return;

        // Close any open profile menu on clicks outside it.
        const openMenu = root.querySelector('.ds-profile-menu.ds-open');
        if (openMenu && !openMenu.contains(target) && !target.closest('[data-ds-profile-toggle]')) {
            closeProfileMenu(openMenu);
            activeProfileMenu = null;
            return;
        }

        if (target.closest('[data-ds-sidebar-collapse]')) {
            root.classList.toggle('ds-sidebar-collapsed');
            writeCollapsedState(root.classList.contains('ds-sidebar-collapsed'));
            return;
        }

        if (target.closest('[data-ds-mobile-open]')) {
            root.classList.add('ds-mobile-open');
            return;
        }

        if (target.closest('[data-ds-mobile-close]')) {
            root.classList.remove('ds-mobile-open');
            return;
        }

        // Profile menu toggle (handles clicks that weren't caught by inline onclick).
        const toggle = target.closest('[data-ds-profile-toggle]');
        if (toggle) {
            const menu = toggle.querySelector('.ds-profile-menu');
            if (menu) {
                const wasOpen = menu.classList.contains('ds-open');
                if (activeProfileMenu && activeProfileMenu !== menu) {
                    closeProfileMenu(activeProfileMenu);
                }
                if (wasOpen) {
                    closeProfileMenu(menu);
                    activeProfileMenu = null;
                } else {
                    menu.classList.add('ds-open');
                    toggle.querySelector('button')?.setAttribute('aria-expanded', 'true');
                    activeProfileMenu = menu;
                }
            }
            event.stopPropagation();
            return;
        }

        // Click inside shell but outside menu → close.
        if (activeProfileMenu && !activeProfileMenu.contains(target)) {
            closeProfileMenu(activeProfileMenu);
            activeProfileMenu = null;
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardShell);
} else {
    initDashboardShell();
}

document.addEventListener('livewire:navigated', initDashboardShell);

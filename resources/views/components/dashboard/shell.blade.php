@php
    $currentUser = auth()->user();

    // Only show pages the signed-in role can actually open.
    $nav = collect(config('dashboard.nav', []))
        ->filter(function ($item) use ($currentUser) {
            $permission = $item['permission'] ?? null;
            if (blank($permission)) {
                return true;
            }
            $keys = is_array($permission) ? $permission : [$permission];
            foreach ($keys as $key) {
                if ($currentUser?->hasPermission($key)) {
                    return true;
                }
            }

            return false;
        })
        ->values()
        ->all();
@endphp

<div id="dashboard-shell" class="ds-app-mesh ds-app-inner">
    <div class="ds-drawer-overlay fixed inset-0 z-40 lg:hidden" data-ds-mobile-close aria-hidden="true"></div>

    {{-- Sidebar --}}
    <aside class="ds-sidebar-aside ds-sidebar-shell fixed inset-y-0 left-0 z-50 flex flex-col" aria-label="{{ __('ui.nav.section') }}">
        {{-- Brand --}}
        <div class="ds-sidebar-brand mx-3 mt-4 mb-2 flex items-center gap-3 rounded-xl px-3 py-3">
            <div class="ds-floating-orb flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-xs font-black text-white shadow-md shadow-emerald-900/30">
                IP
            </div>
            <div class="ds-brand-block min-w-0 flex-1">
                <p class="ds-brand-title truncate text-[13px] font-bold leading-tight text-white">{{ __('ui.brand') }}</p>
                <p class="ds-brand-sub mt-0.5 text-[10px] font-medium text-slate-400">LOLF 18-15</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 pb-4 pt-3">
            <p class="ds-collapse-hint mb-1.5 px-2 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-500">{{ __('ui.nav.section') }}</p>
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}"
                   @class(['ds-nav-link', 'ds-nav-active' => request()->routeIs($item['route'])])>
                    <x-dashboard.nav-icon :name="$item['icon']" />
                    <span class="ds-nav-label truncate">{{ __($item['label_key']) }}</span>
                </a>
            @endforeach
        </nav>

        {{-- Collapse toggle --}}
        <div class="border-t border-white/5 px-3 py-3">
            <button type="button" data-ds-sidebar-collapse
                    class="ds-nav-link hidden w-full cursor-pointer !text-slate-500 hover:!text-white lg:flex"
                    title="{{ __('ui.nav.collapse') }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
                </svg>
                <span class="ds-nav-label text-xs">{{ __('ui.nav.collapse') }}</span>
            </button>
        </div>
    </aside>

    {{-- Main --}}
    <div class="ds-main-panel flex min-w-0 flex-col">
        {{-- Topbar --}}
        <header class="ds-topbar sticky top-0 z-30 flex h-14 shrink-0 items-center gap-3 px-4 sm:px-6">
            <button type="button" data-ds-mobile-open
                    class="inline-flex items-center justify-center rounded-lg border border-[var(--ds-border)] bg-white p-1.5 text-[var(--ds-text-muted)] shadow-sm transition hover:text-[var(--ds-text)] lg:hidden"
                    aria-label="{{ __('ui.nav.open_menu') }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <div class="min-w-0 flex-1">
                <p class="truncate text-[11px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ now()->format('D, M j Y') }}</p>
            </div>

            @php $user = $currentUser; @endphp
            <div class="flex items-center gap-2">
                <x-locale-switcher />
                @if ($user)
                    <span class="hidden rounded-full border border-[var(--ds-border)] bg-white px-3 py-1 text-[11px] font-semibold text-[var(--ds-text-muted)] sm:inline-block"
                          title="{{ $user->roleLabel() }} — {{ $user->isNational() ? __('ui.nav.scope_national') : __('ui.nav.scope_wilaya', ['name' => $user->wilaya?->name_fr ?? $user->wilaya_id]) }}">
                        {{ $user->roleLabel() }}
                        @unless ($user->isNational())
                            <span class="text-[var(--ds-text-faint)]">· {{ $user->wilaya?->name_fr ?? '—' }}</span>
                        @endunless
                    </span>
                @endif
                @if ($user?->hasPermission('alerts.view'))
                    <livewire:dashboard.notification-bell />
                @endif
                <div class="ds-profile-trigger relative" data-ds-profile-toggle>
                    <button type="button"
                        onclick="event.stopPropagation(); var m=this.parentElement.querySelector('.ds-profile-menu');if(m){m.classList.toggle('ds-open');this.setAttribute('aria-expanded',m.classList.contains('ds-open'));}"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-[11px] font-bold text-white shadow-sm ring-0 transition hover:ring-2 hover:ring-emerald-300/60 focus:ring-2 focus:ring-emerald-300/60 focus:outline-none" aria-haspopup="true" aria-expanded="false">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    </button>

                    <div class="ds-profile-menu" role="menu">
                        <div class="ds-profile-menu-header">
                            <p class="ds-profile-menu-name">{{ $user?->name ?? __('ui.common.user') }}</p>
                            <p class="ds-profile-menu-email">{{ $user?->email ?? '' }}</p>
                            @if ($user)
                                <p class="ds-profile-menu-email mt-1 font-semibold text-emerald-600">{{ $user->roleLabel() }}</p>
                            @endif
                        </div>
                        <div class="ds-profile-menu-divider"></div>
                        <a href="{{ route('dashbored.profile') }}" class="ds-profile-menu-item" role="menuitem">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            {{ __('ui.nav.settings') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="ds-profile-menu-item p-0">
                            @csrf
                            <button type="submit" class="ds-profile-menu-item w-full" role="menuitem">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                                {{ __('ui.nav.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 px-4 py-5 sm:px-6 lg:px-8 lg:py-6">
            {{ $slot }}
        </main>
    </div>
</div>

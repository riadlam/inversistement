<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('ui.auth.page_title') }} — {{ __('ui.brand_full') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|dm-sans:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="ds-auth-body">
    <div class="ds-auth-wrapper">
        {{-- Left panel — branding --}}
        <div class="ds-auth-brand-panel">
            <div class="ds-auth-brand-inner">
                <div class="ds-auth-brand-orb">IP</div>
                <h1 class="ds-auth-brand-title">{{ __('ui.brand_full') }}</h1>
                <p class="ds-auth-brand-sub">{{ __('ui.auth_tagline') }}<br><span class="opacity-60">{{ __('ui.auth_tagline_sub') }}</span></p>
                <div class="mt-6"><x-locale-switcher /></div>
                <div class="ds-auth-brand-dots">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>

        {{-- Right panel — form --}}
        <div class="ds-auth-form-panel">
            {{ $slot }}
        </div>
    </div>
    @livewireScripts
</body>
</html>

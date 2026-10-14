@php $locale = app()->getLocale(); @endphp
<form method="POST" action="{{ route('locale.update') }}" class="inline-flex items-center rounded-full border border-[var(--ds-border)] bg-white p-0.5 text-[10px] font-bold tracking-wide">
    @csrf
    <button type="submit" name="locale" value="en"
            class="rounded-full px-2 py-0.5 {{ $locale === 'en' ? 'bg-emerald-600 text-white' : 'text-[var(--ds-text-muted)]' }}"
            aria-label="{{ __('ui.locale.switch') }} EN">{{ __('ui.locale.en') }}</button>
    <button type="submit" name="locale" value="fr"
            class="rounded-full px-2 py-0.5 {{ $locale === 'fr' ? 'bg-emerald-600 text-white' : 'text-[var(--ds-text-muted)]' }}"
            aria-label="{{ __('ui.locale.switch') }} FR">{{ __('ui.locale.fr') }}</button>
</form>

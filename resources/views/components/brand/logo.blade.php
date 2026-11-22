@props([
    'theme' => 'light',
    'showSubtitle' => false,
    'size' => 'md',
])

@php
    $isDark = $theme === 'dark';
    $primaryArm = $isDark ? '#FDFBF7' : '#1F1B16';
    $secondaryArm = $isDark ? '#C7BFB3' : '#4A4137';
    $cherry = $isDark ? '#F43F5E' : '#E11D48';

    $iconSize = match ($size) {
        'sm' => 'w-7 h-7',
        'lg' => 'w-10 h-10',
        default => 'w-8 h-8',
    };

    $textSize = match ($size) {
        'sm' => 'text-base',
        'lg' => 'text-xl',
        default => 'text-lg',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 select-none']) }}>
    <svg viewBox="0 0 36 36" fill="none" class="{{ $iconSize }} shrink-0" aria-hidden="true">
        <path d="M5.5 9.5H12.2L18 22.8L14.7 30L5.5 9.5Z" fill="{{ $primaryArm }}" />
        <path d="M30.5 9.5H23.8L16.3 26.5L18 30.2L30.5 9.5Z" fill="{{ $secondaryArm }}" />
        <circle cx="18" cy="11.2" r="3.6" fill="{{ $cherry }}" />
    </svg>

    <span class="flex flex-col leading-none">
        <span class="font-display {{ $textSize }} tracking-[-0.025em]">
            <span class="font-semibold {{ $isDark ? 'text-porcelain' : 'text-espresso' }}">Vevey</span>
            <span class="font-normal {{ $isDark ? 'text-[#AAA398]' : 'text-espresso-muted' }}">Doces</span>
        </span>
        @if ($showSubtitle)
            <span class="text-xs font-normal mt-0.5 {{ $isDark ? 'text-[#8A847A]' : 'text-espresso-muted' }}">
                Doces, embalagens e festas
            </span>
        @endif
    </span>
</span>

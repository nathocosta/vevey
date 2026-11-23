@props([
    'theme' => 'light',
    'showSubtitle' => false,
    'size' => 'md',
])

@php
    $isDark = $theme === 'dark';
    $sealColor = $isDark ? '#F43F5E' : '#E11D48';

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
    <!-- Selo Confeitaria Vevey (medalhão ondulado de 8 pétalas com V em espaço negativo) -->
    <svg viewBox="0 0 256 256" fill="none" class="{{ $iconSize }} shrink-0" aria-hidden="true">
        <path
            fill="{{ $sealColor }}"
            fill-rule="evenodd"
            d="M128 24 C146 24 156 36 168 41 C180 46 196 42 206 52 C216 62 212 78 217 90 C222 102 234 112 234 128 C234 144 222 154 217 166 C212 178 216 194 206 204 C196 214 180 210 168 215 C156 220 146 232 128 232 C110 232 100 220 88 215 C76 210 60 214 50 204 C40 194 44 178 39 166 C34 154 22 144 22 128 C22 112 34 102 39 90 C44 78 40 62 50 52 C60 42 76 46 88 41 C100 36 110 24 128 24 Z M74 78 L108 78 L128 136 L148 78 L182 78 L142 186 L114 186 Z"
        />
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

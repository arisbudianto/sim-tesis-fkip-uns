@props(['name' => '', 'seed' => 0])

@php
    $palette = ['bg-[#0B3C5D]', 'bg-[#12719E]', 'bg-[#B4801A]', 'bg-[#1B7A5A]', 'bg-[#5A4B8C]', 'bg-[#A03A2C]'];
    $color = $palette[$seed % count($palette)];
    $words = preg_split('/\s+/', trim($name));
    $initials = strtoupper(collect($words)->take(2)->map(fn($w) => mb_substr($w, 0, 1))->implode(''));
@endphp

<span class="h-9 w-9 shrink-0 rounded-xl flex items-center justify-center text-[11px] font-extrabold text-white {{ $color }}">
    {{ $initials ?: '-' }}
</span>

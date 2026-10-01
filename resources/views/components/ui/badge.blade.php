@props(['color' => 'slate'])

@php
    $colors = [
        'green'  => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'yellow' => 'bg-amber-50 text-amber-800 border-amber-200',
        'red'    => 'bg-rose-50 text-rose-800 border-rose-200',
        'blue'   => 'bg-sky-50 text-sky-800 border-sky-200',
        'purple' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
        'slate'  => 'bg-slate-100 text-slate-600 border-slate-200',
    ];
    $cls = $colors[$color] ?? $colors['slate'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border px-2.5 py-1 text-[10.5px] font-bold whitespace-nowrap $cls"]) }}>
    {{ $slot }}
</span>

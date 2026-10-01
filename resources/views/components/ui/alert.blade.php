@props(['type' => 'error'])

@php
    $styles = [
        'error' => 'bg-rose-50 text-rose-700 border-rose-200',
        'info'  => 'bg-sky-50 text-sky-700 border-sky-200',
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    ][$type] ?? 'bg-slate-50 text-slate-700 border-slate-200';
@endphp

<div class="rounded-xl border px-4 py-3 text-[13px] mb-4 {{ $styles }}">
    {{ $slot }}
</div>

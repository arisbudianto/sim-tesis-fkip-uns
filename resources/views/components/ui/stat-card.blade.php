@props([
    'kicker',
    'name',
    'desc',
    'count',
    'pct' => 100,
    'done' => false,
    'icon' => null,
])

<button
    type="button"
    {{ $attributes->class([
        'rounded-2xl border bg-white p-3.5 text-left w-full transition-all duration-150 cursor-pointer',
        'border-slate-200 hover:border-primary-300 hover:shadow-sm',
    ]) }}
>
    <div class="flex items-start justify-between gap-2">
        <span class="inline-flex items-center justify-center h-9 w-9 rounded-xl bg-slate-100 text-primary-800">
            {{ $icon }}
        </span>
        <span class="text-[24px] font-extrabold leading-none tracking-tight {{ $done ? 'text-emerald-700' : 'text-primary-900' }}">
            {{ $count }}
        </span>
    </div>
    <div class="mt-3">
        <div class="text-[9.5px] font-bold uppercase tracking-[0.13em] text-slate-400">{{ $kicker }}</div>
        <div class="mt-0.5 text-[13px] font-extrabold tracking-tight leading-tight text-primary-900">{{ $name }}</div>
        <div class="mt-1 text-[10.5px] text-slate-500 leading-snug">{{ $desc }}</div>
    </div>
    <div class="mt-3 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
        <span class="block h-full rounded-full {{ $done ? 'bg-emerald-600' : 'bg-gradient-to-r from-primary-800 to-accent' }}"
              style="width:{{ $pct }}%"></span>
    </div>
</button>

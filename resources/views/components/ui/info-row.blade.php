@props(['label'])

<div class="flex justify-between items-center py-2.5 border-b border-slate-100 last:border-0 text-[13.5px]">
    <span class="font-semibold text-slate-500">{{ $label }}</span>
    <span class="text-right">{{ $slot }}</span>
</div>

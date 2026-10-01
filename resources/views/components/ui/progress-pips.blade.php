@props(['status'])

@php
    $stepMap = [
        'tahap_1_bimbingan' => 1,
        'tahap_2_sempro' => 2,
        'tahap_3_semhas' => 3,
        'tahap_4_ujian' => 4,
        'selesai_yudisium' => 4,
    ];
    $step = $stepMap[$status] ?? 0;
    $done = $status === 'selesai_yudisium';
@endphp

<div class="mt-2 flex items-center gap-1">
    @for ($n = 1; $n <= 4; $n++)
        <span class="h-1.5 w-4 rounded-full {{ $n <= $step ? ($done ? 'bg-emerald-500' : 'bg-primary-800') : 'bg-slate-200' }}"></span>
    @endfor
    <span class="ml-1 text-[9.5px] font-bold text-slate-400">{{ $done ? 'Selesai' : "$step/4" }}</span>
</div>

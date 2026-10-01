<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['status']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $map = [
        'tahap_1_bimbingan' => ['label' => 'Tahap 1: Bimbingan', 'badge' => 'bg-sky-50 text-sky-800 border-sky-200', 'dot' => 'bg-sky-500'],
        'tahap_2_sempro'    => ['label' => 'Tahap 2: Sempro', 'badge' => 'bg-amber-50 text-amber-800 border-amber-200', 'dot' => 'bg-amber-500'],
        'tahap_3_semhas'    => ['label' => 'Tahap 3: Semhas', 'badge' => 'bg-indigo-50 text-indigo-800 border-indigo-200', 'dot' => 'bg-indigo-500'],
        'tahap_4_ujian'     => ['label' => 'Tahap 4: Ujian Tesis', 'badge' => 'bg-rose-50 text-rose-800 border-rose-200', 'dot' => 'bg-rose-500'],
        'selesai_yudisium'  => ['label' => 'Lulus / Yudisium', 'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200', 'dot' => 'bg-emerald-500'],
    ];
    $s = $map[$status] ?? ['label' => $status, 'badge' => 'bg-slate-100 text-slate-700 border-slate-200', 'dot' => 'bg-slate-400'];
?>

<span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10.5px] font-bold whitespace-nowrap <?php echo e($s['badge']); ?>">
    <span class="h-1.5 w-1.5 rounded-full <?php echo e($s['dot']); ?>"></span><?php echo e($s['label']); ?>

</span>
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/components/ui/status-badge.blade.php ENDPATH**/ ?>
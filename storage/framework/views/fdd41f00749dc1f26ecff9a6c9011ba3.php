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
    $stepMap = [
        'tahap_1_bimbingan' => 1,
        'tahap_2_sempro' => 2,
        'tahap_3_semhas' => 3,
        'tahap_4_ujian' => 4,
        'selesai_yudisium' => 4,
    ];
    $step = $stepMap[$status] ?? 0;
    $done = $status === 'selesai_yudisium';
?>

<div class="mt-2 flex items-center gap-1">
    <?php for($n = 1; $n <= 4; $n++): ?>
        <span class="h-1.5 w-4 rounded-full <?php echo e($n <= $step ? ($done ? 'bg-emerald-500' : 'bg-primary-800') : 'bg-slate-200'); ?>"></span>
    <?php endfor; ?>
    <span class="ml-1 text-[9.5px] font-bold text-slate-400"><?php echo e($done ? 'Selesai' : "$step/4"); ?></span>
</div>
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/components/ui/progress-pips.blade.php ENDPATH**/ ?>
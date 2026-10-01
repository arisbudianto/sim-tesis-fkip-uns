<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['color' => 'slate']));

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

foreach (array_filter((['color' => 'slate']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $colors = [
        'green'  => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'yellow' => 'bg-amber-50 text-amber-800 border-amber-200',
        'red'    => 'bg-rose-50 text-rose-800 border-rose-200',
        'blue'   => 'bg-sky-50 text-sky-800 border-sky-200',
        'purple' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
        'slate'  => 'bg-slate-100 text-slate-600 border-slate-200',
    ];
    $cls = $colors[$color] ?? $colors['slate'];
?>

<span <?php echo e($attributes->merge(['class' => "inline-flex items-center rounded-full border px-2.5 py-1 text-[10.5px] font-bold whitespace-nowrap $cls"])); ?>>
    <?php echo e($slot); ?>

</span>
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/components/ui/badge.blade.php ENDPATH**/ ?>
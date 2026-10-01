<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name' => '', 'seed' => 0]));

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

foreach (array_filter((['name' => '', 'seed' => 0]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $palette = ['bg-[#0B3C5D]', 'bg-[#12719E]', 'bg-[#B4801A]', 'bg-[#1B7A5A]', 'bg-[#5A4B8C]', 'bg-[#A03A2C]'];
    $color = $palette[$seed % count($palette)];
    $words = preg_split('/\s+/', trim($name));
    $initials = strtoupper(collect($words)->take(2)->map(fn($w) => mb_substr($w, 0, 1))->implode(''));
?>

<span class="h-9 w-9 shrink-0 rounded-xl flex items-center justify-center text-[11px] font-extrabold text-white <?php echo e($color); ?>">
    <?php echo e($initials ?: '-'); ?>

</span>
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/components/ui/avatar.blade.php ENDPATH**/ ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title', 'highlight' => null, 'subtitle' => null]));

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

foreach (array_filter((['title', 'highlight' => null, 'subtitle' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section class="relative overflow-hidden rounded-2xl p-7"
         style="background:linear-gradient(125deg,#002B49 0%,#0B3C5D 45%,#12719E 78%,#1A8FC0 100%)">
    <div class="absolute inset-0 opacity-[0.16]"
         style="background-image:linear-gradient(#fff 1px,transparent 1px),linear-gradient(90deg,#fff 1px,transparent 1px); background-size:48px 48px"></div>
    <div class="absolute -right-20 -top-20 h-60 w-60 rounded-full"
         style="background:radial-gradient(circle,#F2B41B55,transparent 65%)"></div>

    <div class="relative flex flex-col gap-4">
        <div class="inline-flex items-center gap-2 self-start rounded-full bg-white/10 border border-white/25 pl-2 pr-3 py-1">
            <span class="h-2 w-2 rounded-full bg-accent"></span>
            <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-white/90">Sistem Informasi Manajemen Tesis</span>
        </div>

        <h1 class="text-white font-extrabold tracking-tight text-[24px] md:text-[27px] leading-[1.15]">
            <?php echo e($title); ?>

            <?php if($highlight): ?>
                <span class="text-[#FFD35C]"><?php echo e($highlight); ?></span>
            <?php endif; ?>
        </h1>

        <?php if($subtitle): ?>
            <p class="text-[13px] leading-relaxed text-white/80 max-w-2xl"><?php echo e($subtitle); ?></p>
        <?php endif; ?>

        <?php if(isset($actions)): ?>
            <div class="flex flex-wrap gap-2.5 pt-0.5">
                <?php echo e($actions); ?>

            </div>
        <?php endif; ?>
    </div>
</section>
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/components/ui/hero.blade.php ENDPATH**/ ?>
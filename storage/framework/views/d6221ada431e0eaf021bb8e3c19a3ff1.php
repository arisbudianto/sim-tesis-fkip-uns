<?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Audit & Transisi Tahap','subtitle' => '20 catatan terakhir. Hanya terlihat oleh Komisi Tesis, Kaprodi, dan Admin Prodi.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Audit & Transisi Tahap','subtitle' => '20 catatan terakhir. Hanya terlihat oleh Komisi Tesis, Kaprodi, dan Admin Prodi.']); ?>
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <h4 class="text-[13px] font-extrabold text-primary-900 mb-2">Log aksi</h4>
            <div class="overflow-x-auto -mx-1 max-h-72 overflow-y-auto">
                <table class="ui-table">
                    <thead><tr><th>Waktu</th><th>Aktor</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = ($auditLogs ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-[11px] whitespace-nowrap"><?php echo e(optional($log->created_at)->format('d/m H:i')); ?></td>
                            <td class="text-[12px]"><?php echo e($log->actor_name); ?><br><span class="text-slate-400"><?php echo e($log->actor_role); ?></span></td>
                            <td class="text-[12px]"><?php echo e($log->action); ?><br><span class="text-slate-500"><?php echo e(\Illuminate\Support\Str::limit($log->description, 80)); ?></span></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3" class="text-center text-slate-400 py-4">Belum ada audit log.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div>
            <h4 class="text-[13px] font-extrabold text-primary-900 mb-2">Transisi tahap</h4>
            <div class="overflow-x-auto -mx-1 max-h-72 overflow-y-auto">
                <table class="ui-table">
                    <thead><tr><th>Waktu</th><th>Dari → Ke</th><th>Aktor</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = ($stateLogs ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-[11px] whitespace-nowrap"><?php echo e(optional($log->created_at)->format('d/m H:i')); ?></td>
                            <td class="text-[12px]"><?php echo e($log->from_state); ?> → <?php echo e($log->to_state); ?>

                                <?php if($log->is_override): ?><?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'yellow']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'yellow']); ?>override <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?><?php endif; ?>
                            </td>
                            <td class="text-[12px]"><?php echo e($log->actor_name); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3" class="text-center text-slate-400 py-4">Belum ada transisi tercatat.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/dashboard/tabs/audit.blade.php ENDPATH**/ ?>
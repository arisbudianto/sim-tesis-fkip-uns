<div class="ui-card !p-0 overflow-hidden">
    <div class="p-5 flex items-center gap-4 border-b border-slate-200">
        <div class="flex-1">
            <h2 class="text-[16px] font-extrabold tracking-tight text-primary-900">Daftar Pengajuan Tesis Berjalan</h2>
            <p class="text-[12px] text-slate-500 mt-0.5">Menampilkan <?php echo e($pengajuans->count()); ?> pengajuan tesis.</p>
        </div>
    </div>

    <table class="ui-table">
        <thead>
            <tr>
                <th>NIM &amp; Mahasiswa</th>
                <th>Judul Tesis &amp; Bidang Fokus</th>
                <th>Tim Pembimbing (1 &amp; 2)</th>
                <th>Status Tahap</th>
                <th class="text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $pengajuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td>
                    <div class="flex items-start gap-2.5">
                        <?php if (isset($component)) { $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.avatar','data' => ['name' => $p->mahasiswa->name ?? '-','seed' => $i]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p->mahasiswa->name ?? '-'),'seed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($i)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $attributes = $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $component = $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
                        <div>
                            <div class="text-[12.5px] font-bold text-primary-900 leading-tight"><?php echo e($p->mahasiswa->name ?? '-'); ?></div>
                            <div class="mt-0.5 text-[10.5px] font-semibold text-slate-500 font-mono"><?php echo e($p->mahasiswa->identifier ?? '-'); ?></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="text-[12px] font-semibold text-slate-800 leading-snug"><?php echo e($p->judul_tesis); ?></div>
                    <div class="mt-1.5 inline-block rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600"><?php echo e($p->bidang_fokus); ?></div>
                </td>
                <td>
                    <div class="flex flex-col gap-1 text-[11px] text-slate-700 leading-snug">
                        <div class="flex gap-1.5"><span class="text-slate-400 font-bold">1.</span><span class="font-semibold"><?php echo e($p->pembimbing1->name ?? 'Belum Dialokasikan'); ?></span></div>
                        <div class="flex gap-1.5"><span class="text-slate-400 font-bold">2.</span><span class="font-semibold"><?php echo e($p->pembimbing2->name ?? 'Belum Dialokasikan'); ?></span></div>
                    </div>
                </td>
                <td>
                    <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['status' => $p->status_tahap]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p->status_tahap)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal2e89b595a815d72fa4e87a45f2653865 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2e89b595a815d72fa4e87a45f2653865 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.progress-pips','data' => ['status' => $p->status_tahap]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.progress-pips'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p->status_tahap)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2e89b595a815d72fa4e87a45f2653865)): ?>
<?php $attributes = $__attributesOriginal2e89b595a815d72fa4e87a45f2653865; ?>
<?php unset($__attributesOriginal2e89b595a815d72fa4e87a45f2653865); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2e89b595a815d72fa4e87a45f2653865)): ?>
<?php $component = $__componentOriginal2e89b595a815d72fa4e87a45f2653865; ?>
<?php unset($__componentOriginal2e89b595a815d72fa4e87a45f2653865); ?>
<?php endif; ?>
                </td>
                <td class="text-right">
                    <?php if(Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi'])): ?>
                        <?php if($p->status_tahap === 'tahap_1_bimbingan'): ?>
                            <div class="flex flex-col items-end gap-1.5">
                                <a href="<?php echo e(route('pengajuan.edit', $p->id)); ?>" class="ui-btn ui-btn-sm ui-btn-primary">Edit</a>
                                <form action="<?php echo e(route('pengajuan.destroy', $p->id)); ?>" method="POST"
                                      onsubmit="return confirm('Yakin hapus pengajuan tesis <?php echo e($p->mahasiswa->name ?? ''); ?>? Tindakan ini tidak bisa dibatalkan.');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="ui-btn ui-btn-sm ui-btn-danger">Hapus</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <span class="ui-btn-muted">Terkunci (&gt; Tahap 1)</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="ui-btn-muted">&mdash;</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5" class="text-center text-slate-500 py-8">Belum ada data pengajuan tesis. Silakan isi form pada tab FR-01.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/dashboard/tabs/_semua-pengajuan.blade.php ENDPATH**/ ?>
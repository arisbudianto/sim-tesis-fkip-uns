    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Master Data Mahasiswa','subtitle' => 'Kelola akun mahasiswa. Reset password default: user123.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Master Data Mahasiswa','subtitle' => 'Kelola akun mahasiswa. Reset password default: user123.']); ?>
        <?php
            $pengajuanByMhs = ($pengajuans ?? collect())->keyBy('mahasiswa_id');
            $bolehReset = Auth::user()->hasAnyRole(['admin_prodi', 'komisi_tesis']);
        ?>
        <div class="overflow-x-auto -mx-1" x-data="{ q: '' }">
            <input type="search" x-model="q" class="ui-input mb-3" placeholder="Cari NIM atau nama...">
            <table class="ui-table w-full table-fixed">
                <thead>
                    <tr>
                        <th class="w-[28%]">Mahasiswa</th>
                        <th class="w-[52%]">Judul / Tahap</th>
                        <th class="w-[20%]">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = ($mahasiswas ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $pj = $pengajuanByMhs->get($m->id); ?>
                        <tr x-show="q === '' || '<?php echo e(strtolower($m->identifier.' '.$m->name)); ?>'.includes(q.toLowerCase())">
                            <td class="align-top">
                                <div class="font-semibold text-[13px] break-all"><?php echo e($m->identifier); ?></div>
                                <div class="text-[13px] leading-snug"><?php echo e($m->name); ?></div>
                                <div class="text-[11px] text-slate-500 break-all"><?php echo e($m->email); ?></div>
                            </td>
                            <td class="align-top">
                                <?php if($pj): ?>
                                    <div class="text-[12.5px]"><?php echo e(\Illuminate\Support\Str::limit($pj->judul_tesis, 50)); ?></div>
                                    <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['status' => $pj->status_tahap]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pj->status_tahap)]); ?>
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
                                <?php else: ?>
                                    <span class="text-slate-400 text-[12.5px]">Belum mengajukan judul</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($bolehReset): ?>
                                <form action="<?php echo e(route('pengguna.resetPassword', $m)); ?>" method="POST" onsubmit="return confirm('Reset password <?php echo e($m->name); ?> ke user123?')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="ui-btn ui-btn-sm ui-btn-outline">Reset PW</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center text-slate-500 py-6">Belum ada akun mahasiswa.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
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

    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Master Data Dosen & Staf','subtitle' => 'Akun dosen, komisi, kaprodi, dan admin. Password default setelah reset: user123.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Master Data Dosen & Staf','subtitle' => 'Akun dosen, komisi, kaprodi, dan admin. Password default setelah reset: user123.']); ?>
        <div class="overflow-x-auto -mx-1" x-data="{ q2: '' }">
            <input type="search" x-model="q2" class="ui-input mb-3" placeholder="Cari NIP atau nama...">
            <table class="ui-table w-full table-fixed">
                <thead><tr><th class="w-[28%]">NIP</th><th class="w-[42%]">Nama</th><th class="w-[15%]">Peran</th><th class="w-[15%]">Aksi</th></tr></thead>
                <tbody>
                    <?php $__currentLoopData = ($stafs ?? $dosens ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr x-show="q2 === '' || '<?php echo e(strtolower(($d->identifier ?? '').' '.($d->name ?? ''))); ?>'.includes(q2.toLowerCase())">
                        <td class="font-semibold whitespace-nowrap"><?php echo e($d->identifier); ?></td>
                        <td><?php echo e($d->name); ?></td>
                        <td class="text-[12px]"><?php echo e($d->role); ?></td>
                        <td>
                            <?php if($bolehReset): ?>
                            <form action="<?php echo e(route('pengguna.resetPassword', $d)); ?>" method="POST" onsubmit="return confirm('Reset password <?php echo e($d->name); ?> ke user123?')">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="ui-btn ui-btn-sm ui-btn-outline">Reset PW</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
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

<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/dashboard/tabs/_master-data.blade.php ENDPATH**/ ?>
<?php
    $role = Auth::user()->role ?? null;
    $bolehBlast = in_array($role, ['komisi_tesis', 'kaprodi', 'admin_prodi'], true);
    $sidangList = ($sidangs ?? collect())->sortByDesc('waktu_mulai');
    $logs = \App\Domain\Notifikasi\Models\NotifikasiLog::latest()->limit(15)->get();
    $waSiap = filled(config('whatsapp.url')) && filled(config('whatsapp.token'));
?>

<?php if($bolehBlast): ?>
<?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Notifikasi WA Blast','subtitle' => 'Kirim undangan menguji ke dewan penguji dan/atau pengumuman jadwal ke mahasiswa melalui WhatsApp.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Notifikasi WA Blast','subtitle' => 'Kirim undangan menguji ke dewan penguji dan/atau pengumuman jadwal ke mahasiswa melalui WhatsApp.']); ?>
    <?php if (! ($waSiap)): ?>
        <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-[12.5px] text-amber-900">
            Gateway WhatsApp belum dikonfigurasi. Untuk Wablas isi di <code>.env</code>:
            <code>WHATSAPP_API_URL=https://deu.wablas.com/api/send-message</code>,
            <code>WHATSAPP_API_TOKEN</code> (Device → Settings),
            <code>WHATSAPP_API_SECRET</code> (secret key device), lalu <code>php artisan config:clear</code>.
        </div>
    <?php endif; ?>
    <?php if(session('wa_blast_hasil')): ?>
        <div class="mb-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-[12.5px]">
            <div class="font-semibold text-primary-900 mb-1">Hasil pengiriman terakhir</div>
            <ul class="list-disc pl-4 space-y-0.5">
                <?php $__currentLoopData = session('wa_blast_hasil'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <?php echo e($h['nama']); ?> (<?php echo e($h['peran']); ?>)
                        — <?php echo e($h['nomor'] ?: 'nomor WA kosong'); ?>

                        — <strong><?php echo e($h['status']); ?></strong>
                        <?php if(!empty($h['error'])): ?> <span class="text-rose-600"><?php echo e($h['error']); ?></span> <?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('notifikasi.waBlast')); ?>" method="POST" class="grid md:grid-cols-3 gap-3 items-end">
        <?php echo csrf_field(); ?>
        <div class="ui-field !mb-0 md:col-span-2">
            <label class="ui-label">Sidang tujuan</label>
            <select name="sidang_id" class="ui-input" required>
                <option value="">— Pilih mahasiswa / tahap —</option>
                <?php $__empty_1 = true; $__currentLoopData = $sidangList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <option value="<?php echo e($s->id); ?>">
                        <?php echo e($s->pengajuanTesis->mahasiswa->name ?? 'Mahasiswa'); ?>

                        — <?php echo e(strtoupper($s->tahap_sidang)); ?>

                        — <?php echo e(optional($s->waktu_mulai)->translatedFormat('d M Y H:i') ?? 'jadwal belum diisi'); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <option value="" disabled>Belum ada sidang terplot</option>
                <?php endif; ?>
            </select>
        </div>
        <div class="ui-field !mb-0">
            <label class="ui-label">Penerima</label>
            <select name="target" class="ui-input" required>
                <option value="semua">Penguji + Mahasiswa</option>
                <option value="penguji">Dewan Penguji saja</option>
                <option value="mahasiswa">Mahasiswa saja</option>
            </select>
        </div>
        <div class="md:col-span-3 flex flex-wrap gap-2">
            <button type="submit" class="ui-btn ui-btn-primary">Kirim WA Blast</button>
        </div>
    </form>

    <div class="mt-5">
        <div class="text-[12px] font-bold uppercase tracking-wider text-slate-500 mb-2">Log notifikasi terbaru</div>
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Waktu</th><th>Template</th><th>Tujuan</th><th>Status</th><th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="text-[12px] whitespace-nowrap"><?php echo e($log->created_at?->format('d/m/Y H:i')); ?></td>
                        <td class="text-[12px]"><?php echo e($log->template_key ?? $log->key ?? '-'); ?></td>
                        <td class="text-[12px]"><?php echo e($log->nomor_tujuan ?? '-'); ?></td>
                        <td>
                            <?php if(($log->status ?? '') === 'terkirim'): ?>
                                <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green']); ?>Terkirim <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                            <?php elseif(($log->status ?? '') === 'pending'): ?>
                                <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'yellow']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'yellow']); ?>Pending <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                            <?php else: ?>
                                <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'red']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'red']); ?><?php echo e($log->status ?? 'gagal'); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-[11.5px] text-slate-500"><?php echo e(\Illuminate\Support\Str::limit($log->error_message ?? '', 80)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center text-slate-500 py-6">Belum ada log notifikasi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
<?php endif; ?>
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/dashboard/tabs/wa-blast.blade.php ENDPATH**/ ?>
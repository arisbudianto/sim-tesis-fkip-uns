<div class="flex flex-col gap-4">

    <?php
        $role = Auth::user()->role ?? null;
        // Kebocoran data yang diperbaiki: tabel jadwal & dewan penguji di
        // bawah dulu menampilkan SEMUA sidang (semua mahasiswa) ke siapa
        // pun yang login. Sekarang di-filter dulu sesuai lingkup akses.
        $sidangUntukTabel = match(true) {
            $role === 'mahasiswa' => $myPengajuan?->aktivitasSidangs ?? collect(),
            $role === 'dosen' => ($myTugasPenguji ?? collect())->pluck('sidang')->filter()->unique('id'),
            default => $sidangs, // pengendali akademik: lihat semua
        };
        $fokusTahap = $fokusTahap ?? null;
        if ($fokusTahap) {
            $sidangUntukTabel = collect($sidangUntukTabel)->where('tahap_sidang', $fokusTahap);
        }
    ?>

    <?php if(Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi'])): ?>
    <?php
        $eligiblePlotting = [];
        foreach ($pengajuans as $pe) {
            if ((!$fokusTahap || $fokusTahap === 'sempro')
                && $pe->status_tahap === 'tahap_2_sempro'
                && $pe->pendaftaranSempro?->status_verifikasi_admin === 'verified'
                && !$pe->aktivitasSidangs->firstWhere('tahap_sidang', 'sempro')) {
                $eligiblePlotting[] = ['pengajuan' => $pe, 'tahap' => 'sempro', 'label' => $pe->mahasiswa->name . ' — Sempro'];
            }
            if ((!$fokusTahap || $fokusTahap === 'semhas')
                && $pe->status_tahap === 'tahap_3_semhas'
                && $pe->pendaftaranSemhas?->status_verifikasi_admin === 'verified'
                && !$pe->aktivitasSidangs->firstWhere('tahap_sidang', 'semhas')) {
                $eligiblePlotting[] = ['pengajuan' => $pe, 'tahap' => 'semhas', 'label' => $pe->mahasiswa->name . ' — Semhas'];
            }
        }
    ?>
    <?php if(count($eligiblePlotting) > 0 || $errors->any()): ?>
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Plotting Jadwal & Dewan Penguji Baru','subtitle' => 'Isi jadwal sidang dan 4 penguji untuk mahasiswa yang pendaftarannya sudah disetujui tetapi belum dijadwalkan.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Plotting Jadwal & Dewan Penguji Baru','subtitle' => 'Isi jadwal sidang dan 4 penguji untuk mahasiswa yang pendaftarannya sudah disetujui tetapi belum dijadwalkan.']); ?>

        <?php if($errors->any()): ?>
            <?php if (isset($component)) { $__componentOriginal746de018ded8594083eb43be3f1332e1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal746de018ded8594083eb43be3f1332e1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.alert','data' => ['type' => 'error']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'error']); ?>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($error); ?><br> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $attributes = $__attributesOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__attributesOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $component = $__componentOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__componentOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php if(count($eligiblePlotting) === 0): ?>
            <p class="text-slate-500 text-[13.5px]">Tidak ada mahasiswa yang menunggu plotting.</p>
        <?php else: ?>
        <form id="form-plotting" method="POST">
            <?php echo csrf_field(); ?>
            <div class="ui-field">
                <label class="ui-label">Pilih Mahasiswa & Tahap Sidang</label>
                <select id="plotting-select" class="ui-input" onchange="updatePlottingAction(this)">
                    <option value="">-- Pilih --</option>
                    <?php $__currentLoopData = $eligiblePlotting; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($ep['tahap']); ?>"
                            data-pengajuan-id="<?php echo e($ep['pengajuan']->id); ?>"
                            data-pembimbing1="<?php echo e($ep['pengajuan']->pembimbing_1_id); ?>"
                            data-pembimbing2="<?php echo e($ep['pengajuan']->pembimbing_2_id); ?>"><?php echo e($ep['label']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3.5">
                <div class="ui-field">
                    <label class="ui-label">Waktu Mulai</label>
                    <input type="datetime-local" name="waktu_mulai" class="ui-input" required>
                </div>
                <div class="ui-field">
                    <label class="ui-label">Waktu Selesai</label>
                    <input type="datetime-local" name="waktu_selesai" class="ui-input" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3.5">
                <div class="ui-field">
                    <label class="ui-label">Ruangan (kosongkan kalau daring)</label>
                    <input type="text" name="ruangan" class="ui-input" placeholder="Ruang Sidang FKIP">
                </div>
                <div class="ui-field">
                    <label class="ui-label">Link Zoom (kalau daring)</label>
                    <input type="text" name="link_zoom" class="ui-input" placeholder="https://zoom.us/j/...">
                </div>
            </div>
            <div class="ui-field">
                <label class="ui-label">Komisi Tesis Penanggung Jawab</label>
                <select name="komisi_tesis_id" class="ui-input" required>
                    <?php $__currentLoopData = $dosens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($d->role === 'komisi_tesis' || $d->is_komisi_tesis): ?>
                            <option value="<?php echo e($d->id); ?>" <?php echo e($komisi && $komisi->id === $d->id ? 'selected' : ''); ?>><?php echo e($d->name); ?></option>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($komisi && !$dosens->contains('id', $komisi->id)): ?>
                        <option value="<?php echo e($komisi->id); ?>" selected><?php echo e($komisi->name); ?></option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="text-[12px] font-bold uppercase tracking-wider text-slate-500 mt-5 mb-3 pb-1.5 border-b border-slate-100">Dewan Penguji &mdash; 4 Dosen</div>

            <div class="grid grid-cols-2 gap-3.5">
                <div class="ui-field">
                    <label class="ui-label">Ketua Penguji</label>
                    <select name="penguji[0][dosen_id]" class="ui-input" required>
                        <option value="">-- Pilih Dosen --</option>
                        <?php $__currentLoopData = $dosens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($d->id); ?>"><?php echo e($d->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <input type="hidden" name="penguji[0][peran_penguji]" value="ketua_penguji">
                </div>
                <div class="ui-field">
                    <label class="ui-label">Sekretaris Penguji</label>
                    <select name="penguji[1][dosen_id]" class="ui-input" required>
                        <option value="">-- Pilih Dosen --</option>
                        <?php $__currentLoopData = $dosens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($d->id); ?>"><?php echo e($d->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <input type="hidden" name="penguji[1][peran_penguji]" value="sekretaris_penguji">
                </div>
                <div class="ui-field">
                    <label class="ui-label">Anggota 1 (Pembimbing 1)</label>
                    <select id="plotting-anggota1" name="penguji[2][dosen_id]" class="ui-input" required>
                        <option value="">-- Pilih Dosen --</option>
                        <?php $__currentLoopData = $dosens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($d->id); ?>"><?php echo e($d->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <input type="hidden" name="penguji[2][peran_penguji]" value="pembimbing_1">
                </div>
                <div class="ui-field">
                    <label class="ui-label">Anggota 2 (Pembimbing 2)</label>
                    <select id="plotting-anggota2" name="penguji[3][dosen_id]" class="ui-input" required>
                        <option value="">-- Pilih Dosen --</option>
                        <?php $__currentLoopData = $dosens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($d->id); ?>"><?php echo e($d->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <input type="hidden" name="penguji[3][peran_penguji]" value="pembimbing_2">
                </div>
            </div>

            <button type="submit" class="ui-btn ui-btn-success mt-2">Simpan Plotting</button>
        </form>
        <?php endif; ?>
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
    <?php endif; ?>

    <?php
        $judulDaftarSidang = match ($fokusTahap ?? null) {
            'sempro' => 'Daftar mahasiswa yang sudah menempuh Seminar Proposal',
            'semhas' => 'Daftar mahasiswa yang sudah menempuh Seminar Hasil',
            'ujian' => 'Daftar mahasiswa yang sudah menempuh Ujian Tesis',
            default => 'Daftar sidang yang sudah diplotting',
        };
    ?>
    <?php if (! ($fokusTahap)): ?>
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => $judulDaftarSidang]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($judulDaftarSidang)]); ?>
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead>
                    <tr><th>Tahap Sidang</th><th>Mahasiswa</th><th>Jadwal & Ruangan</th><th>Dewan Penguji Terplotting</th><th>Kalender</th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $sidangUntukTabel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e(strtoupper($s->tahap_sidang)); ?></strong></td>
                        <td><?php echo e($s->pengajuanTesis->mahasiswa->name ?? '-'); ?></td>
                        <td><?php echo e($s->waktu_mulai); ?> s.d <?php echo e($s->waktu_selesai); ?><br><span class="text-slate-500 text-[11px]">Ruang: <?php echo e($s->ruangan ?? 'Zoom Cloud'); ?></span></td>
                        <td>
                            <?php $__currentLoopData = $s->pengujiSidangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ps): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                &bull; <?php echo e($ps->dosen->name); ?> (<em><?php echo e($ps->peran_penguji); ?></em>)<br>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </td>
                        <td>
                            <a href="<?php echo e(route('sidang.kalenderIcs', $s->id)); ?>" class="ui-btn ui-btn-sm ui-btn-outline">.ics</a>
                            <?php if(Auth::check() && Auth::user()->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi'])): ?>
                                <a href="<?php echo e(route('dokumen.bundle', $s->id)); ?>" target="_blank" class="ui-btn ui-btn-sm ui-btn-outline">Cetak Bundle</a>
                                <a href="<?php echo e(route('sidang.penilaian', $s->id)); ?>" class="ui-btn ui-btn-sm ui-btn-primary">Rekap / Nilai</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center text-slate-500 py-6">Belum ada plotting jadwal sidang yang aktif.</td></tr>
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
    <?php endif; ?>
</div>
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/dashboard/tabs/sidang.blade.php ENDPATH**/ ?>
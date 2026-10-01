<?php use App\Http\Controllers\BerkasController; ?>
<div class="flex flex-col gap-4">

    <?php
        $role = Auth::user()->role ?? null;
        // Kebocoran data yang sudah diperbaiki: baris tabel H-14 di bawah
        // dulu menampilkan SEMUA mahasiswa ke siapa pun yang login. Sekarang
        // di-filter dulu sesuai lingkup akses role sebelum di-loop.
        $baris = match(true) {
            $role === 'mahasiswa' => collect($myPengajuan ? [$myPengajuan] : []),
            $role === 'dosen' => $myBimbingan ?? collect(),
            default => $pengajuans, // pengendali akademik: lihat semua
        };
        $fokusTahap = $fokusTahap ?? null; // sempro | semhas | ujian | null=semua
    ?>

    <?php if((!$fokusTahap || $fokusTahap === 'sempro') && Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi'])): ?>
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Mahasiswa Seminar Proposal','subtitle' => 'Satu daftar: menunggu verifikasi, menunggu jadwal, atau sudah dijadwalkan.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Mahasiswa Seminar Proposal','subtitle' => 'Satu daftar: menunggu verifikasi, menunggu jadwal, atau sudah dijadwalkan.']); ?>
        <div class="overflow-x-auto -mx-1" x-data="{ editId: null }">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Mahasiswa</th><th>Judul</th><th>Jadwal & Penguji</th><th>Status</th><th>Dokumen</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $pengajuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php if($p->status_tahap === 'tahap_2_sempro'): ?>
                        <?php $ps = $p->pendaftaranSempro; ?>
                        <tr>
                            <?php
                                $sidangSempro = $p->aktivitasSidangs->firstWhere('tahap_sidang', 'sempro');
                                $pengujiLengkap = $sidangSempro && $sidangSempro->pengujiSidangs->count() >= 3;
                            ?>
                            <td><strong><?php echo e($p->mahasiswa->name); ?></strong><br><?php echo e($p->mahasiswa->identifier); ?></td>
                            <td class="text-[12.5px]"><?php echo e(\Illuminate\Support\Str::limit($p->judul_tesis, 50)); ?></td>
                            <td class="text-[12.5px]">
                                <?php if($sidangSempro): ?>
                                    <div class="font-semibold"><?php echo e(optional($sidangSempro->waktu_mulai)->translatedFormat('d F Y, H:i') ?? $sidangSempro->waktu_mulai); ?></div>
                                    <div class="text-slate-500 text-[11px]">Ruang: <?php echo e($sidangSempro->ruangan ?? 'TBA'); ?></div>
                                    <div class="text-[11px] text-slate-600 mt-1">
                                        <?php $__currentLoopData = $sidangSempro->pengujiSidangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php echo e($pj->dosen->name ?? '-'); ?> <em>(<?php echo e(str_replace('_',' ',$pj->peran_penguji)); ?>)</em><?php if(!$loop->last): ?><br><?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php elseif($ps && $ps->jadwal_usulan_sidang): ?>
                                    Usulan: <?php echo e(\Carbon\Carbon::parse($ps->jadwal_usulan_sidang)->translatedFormat('d F Y, H:i')); ?>

                                    <div class="text-[11px] text-slate-400">Belum dijadwalkan Komisi</div>
                                <?php else: ?>
                                    <span class="text-slate-400">Belum ada pendaftaran Sempro</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($sidangSempro): ?>
                                    <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green']); ?>Sudah dijadwalkan <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                                <?php elseif($ps && $ps->status_verifikasi_admin === 'verified'): ?>
                                    <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'yellow']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'yellow']); ?>Menunggu jadwal <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                                <?php elseif($ps->status_verifikasi_admin === 'rejected'): ?>
                                    <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'red']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'red']); ?>Ditolak <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'yellow']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'yellow']); ?>Menunggu Verifikasi <?php echo $__env->renderComponent(); ?>
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
                            <td class="space-y-1">
                                <?php if($ps && $ps->form_fpt_ti_01_url): ?>
                                    <a href="<?php echo e(BerkasController::url($ps->form_fpt_ti_01_url)); ?>" target="_blank" class="ui-btn ui-btn-sm ui-btn-primary">Form FPT-TI-01</a>
                                <?php else: ?>
                                    <span class="text-rose-600 text-[12px] block">Form belum diunggah</span>
                                <?php endif; ?>
                                <?php if($ps && $ps->status_verifikasi_admin === 'verified' && $pengujiLengkap): ?>
                                    <a href="<?php echo e(route('dokumen.cetak', ['kode' => 'SURAT-TUGAS-SEMPRO', 'id' => $sidangSempro->id])); ?>" class="ui-btn ui-btn-sm ui-btn-outline" target="_blank">Surat Tugas</a>
                                    <a href="<?php echo e(route('dokumen.cetak', ['kode' => 'UNDANGAN-SEMPRO', 'id' => $sidangSempro->id])); ?>" class="ui-btn ui-btn-sm ui-btn-outline" target="_blank">Undangan</a>
                                <?php elseif($ps && $ps->status_verifikasi_admin === 'verified'): ?>
                                    <span class="text-[11px] text-slate-400 block">Surat tugas menunggu plotting</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1.5">
                                    <?php if($ps && $ps->status_verifikasi_admin === 'pending'): ?>
                                        <button type="button" class="ui-btn ui-btn-sm ui-btn-outline"
                                            @click="editId = editId === '<?php echo e($ps->id); ?>' ? null : '<?php echo e($ps->id); ?>'">
                                            Edit
                                        </button>
                                        <form action="<?php echo e(route('sempro.verifikasi', $ps->id)); ?>" method="POST">
                                            <?php echo csrf_field(); ?><input type="hidden" name="status_verifikasi_admin" value="verified">
                                            <button type="submit" class="ui-btn ui-btn-sm ui-btn-success">Setujui</button>
                                        </form>
                                        <form action="<?php echo e(route('sempro.verifikasi', $ps->id)); ?>" method="POST">
                                            <?php echo csrf_field(); ?><input type="hidden" name="status_verifikasi_admin" value="rejected">
                                            <button type="submit" class="ui-btn ui-btn-sm ui-btn-danger">Tolak</button>
                                        </form>
                                    <?php else: ?>
                                        <?php if(!empty($sidangSempro)): ?>
                                            <a href="<?php echo e(route('sidang.penilaian', $sidangSempro->id)); ?>" class="ui-btn ui-btn-sm ui-btn-primary">Rekap / Nilai</a>
                                        <?php else: ?>
                                            <span class="text-[12px] text-slate-400">—</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <tr x-show="editId === '<?php echo e($ps->id); ?>'" x-cloak>
                            <td colspan="5" class="bg-slate-50">
                                <form action="<?php echo e(route('sempro.update.post', $ps->id)); ?>" method="POST" class="p-3 flex flex-col gap-3">
                                    <?php echo csrf_field(); ?>
                                    <?php
                                        $sidangSempro = $p->aktivitasSidangs->firstWhere('tahap_sidang', 'sempro');
                                        $ketuaId = optional(optional($sidangSempro)->pengujiSidangs)->firstWhere('peran_penguji', 'ketua_penguji')->dosen_id ?? null;
                                        $sekreId = optional(optional($sidangSempro)->pengujiSidangs)->firstWhere('peran_penguji', 'sekretaris_penguji')->dosen_id ?? null;
                                    ?>
                                    <p class="text-[13px] font-semibold text-primary-900">Edit Jadwal & Penguji — <?php echo e($p->mahasiswa->name); ?></p>
                                    <p class="text-[12px] text-slate-500 -mt-2">Hanya tanggal sidang dan penguji eksternal yang bisa diubah. Pembimbing utama & pendamping tetap.</p>
                                    <div class="grid md:grid-cols-2 gap-3">
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Hari / Tanggal Sidang <span class="text-rose-600">*</span></label>
                                            <input type="datetime-local" name="jadwal_usulan_sidang" class="ui-input" required
                                                value="<?php echo e(\Carbon\Carbon::parse($ps->jadwal_usulan_sidang)->format('Y-m-d\TH:i')); ?>">
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Pembimbing Utama</label>
                                            <input type="text" class="ui-input bg-slate-100" value="<?php echo e($p->pembimbing1->name ?? 'Belum ditetapkan'); ?>" disabled>
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Pembimbing Pendamping</label>
                                            <input type="text" class="ui-input bg-slate-100" value="<?php echo e($p->pembimbing2->name ?? 'Belum ditetapkan'); ?>" disabled>
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Ketua Penguji</label>
                                            <select name="ketua_penguji_id" class="ui-input">
                                                <option value="">— Pilih dosen —</option>
                                                <?php $__currentLoopData = ($dosens ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if($d->id !== $p->pembimbing_1_id && $d->id !== $p->pembimbing_2_id): ?>
                                                        <option value="<?php echo e($d->id); ?>" <?php if($ketuaId === $d->id): echo 'selected'; endif; ?>><?php echo e($d->name); ?></option>
                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Sekretaris Penguji</label>
                                            <select name="sekretaris_penguji_id" class="ui-input">
                                                <option value="">— Pilih dosen —</option>
                                                <?php $__currentLoopData = ($dosens ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if($d->id !== $p->pembimbing_1_id && $d->id !== $p->pembimbing_2_id): ?>
                                                        <option value="<?php echo e($d->id); ?>" <?php if($sekreId === $d->id): echo 'selected'; endif; ?>><?php echo e($d->name); ?></option>
                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                    </div>
                                    <?php $__errorArgs = ['jadwal_usulan_sidang'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="text-[12px] text-rose-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <?php $__errorArgs = ['ketua_penguji_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="text-[12px] text-rose-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <div class="flex gap-2">
                                        <button type="submit" class="ui-btn ui-btn-primary">Simpan Perubahan</button>
                                        <button type="button" class="ui-btn ui-btn-ghost" @click="editId = null">Batal</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
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

    <?php if((!$fokusTahap || in_array($fokusTahap, ['semhas', 'ujian'], true)) && Auth::check() && Auth::user()->role === 'dosen'): ?>
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Approval Naskah Semhas — Bimbingan Anda','subtitle' => 'Naskah Bab I-V wajib disetujui Pembimbing 1 & 2 sebelum Admin Prodi bisa memverifikasi pendaftaran Semhas.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Approval Naskah Semhas — Bimbingan Anda','subtitle' => 'Naskah Bab I-V wajib disetujui Pembimbing 1 & 2 sebelum Admin Prodi bisa memverifikasi pendaftaran Semhas.']); ?>
        <?php
            $needApproval = $pengajuans->filter(function ($p) {
                if (!$p->pendaftaranSemhas) return false;
                $isPembimbing1 = $p->pembimbing_1_id === Auth::id() && !$p->pendaftaranSemhas->approval_pembimbing_1;
                $isPembimbing2 = $p->pembimbing_2_id === Auth::id() && !$p->pendaftaranSemhas->approval_pembimbing_2;
                return $isPembimbing1 || $isPembimbing2;
            });
        ?>
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead><tr><th>Mahasiswa</th><th>Naskah Bab I-V</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $needApproval; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($p->mahasiswa->name); ?></strong><br><?php echo e($p->mahasiswa->identifier); ?></td>
                        <td><a href="<?php echo e($p->pendaftaranSemhas->naskah_bab_1_5_url); ?>" target="_blank" class="ui-btn ui-btn-sm ui-btn-outline">Lihat Naskah</a></td>
                        <td>
                            <form action="<?php echo e(route('semhas.approveNaskah', $p->pendaftaranSemhas->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="ui-btn ui-btn-sm ui-btn-success">Setujui Naskah</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3" class="text-center text-slate-500 py-6">Tidak ada naskah Semhas yang menunggu persetujuan Anda.</td></tr>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Persetujuan Tertulis Ujian Tesis — Bimbingan Anda','subtitle' => 'Persetujuan tertulis digital Pembimbing 1 & 2 wajib diberikan sebelum Komisi Tesis bisa melakukan plotting jadwal & dewan penguji.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Persetujuan Tertulis Ujian Tesis — Bimbingan Anda','subtitle' => 'Persetujuan tertulis digital Pembimbing 1 & 2 wajib diberikan sebelum Komisi Tesis bisa melakukan plotting jadwal & dewan penguji.']); ?>
        <?php
            $needAccUjian = $pengajuans->filter(function ($p) {
                if (!$p->pendaftaranUjian) return false;
                $isPembimbing1 = $p->pembimbing_1_id === Auth::id() && !$p->pendaftaranUjian->acc_tertulis_pembimbing_1;
                $isPembimbing2 = $p->pembimbing_2_id === Auth::id() && !$p->pendaftaranUjian->acc_tertulis_pembimbing_2;
                return $isPembimbing1 || $isPembimbing2;
            });
        ?>
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead><tr><th>Mahasiswa</th><th>Naskah Tesis Lengkap</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $needAccUjian; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($p->mahasiswa->name); ?></strong><br><?php echo e($p->mahasiswa->identifier); ?></td>
                        <td><a href="<?php echo e($p->pendaftaranUjian->naskah_tesis_lengkap_url); ?>" target="_blank" class="ui-btn ui-btn-sm ui-btn-outline">Lihat Naskah</a></td>
                        <td>
                            <form action="<?php echo e(route('ujian.accPembimbing', $p->pendaftaranUjian->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="ui-btn ui-btn-sm ui-btn-success">Berikan Persetujuan Tertulis</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3" class="text-center text-slate-500 py-6">Tidak ada pendaftaran Ujian Tesis yang menunggu persetujuan Anda.</td></tr>
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

    <?php if((!$fokusTahap || $fokusTahap === 'semhas') && Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi'])): ?>
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Mahasiswa Seminar Hasil','subtitle' => 'Satu daftar: verifikasi, jadwal, penguji, dokumen, dan nilai.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Mahasiswa Seminar Hasil','subtitle' => 'Satu daftar: verifikasi, jadwal, penguji, dokumen, dan nilai.']); ?>
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Mahasiswa</th><th>Judul</th><th>Jadwal & Penguji</th><th>Status</th><th>Dokumen</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $pengajuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php if($p->status_tahap === 'tahap_3_semhas'): ?>
                        <?php
                            $ph = $p->pendaftaranSemhas;
                            $sidangSemhas = $p->aktivitasSidangs->firstWhere('tahap_sidang', 'semhas');
                        ?>
                        <tr>
                            <td><strong><?php echo e($p->mahasiswa->name); ?></strong><br><?php echo e($p->mahasiswa->identifier); ?></td>
                            <td class="text-[12.5px]"><?php echo e(\Illuminate\Support\Str::limit($p->judul_tesis, 50)); ?></td>
                            <td class="text-[12.5px]">
                                <?php if($sidangSemhas): ?>
                                    <div class="font-semibold"><?php echo e(optional($sidangSemhas->waktu_mulai)->translatedFormat('d F Y, H:i') ?? $sidangSemhas->waktu_mulai); ?></div>
                                    <div class="text-slate-500 text-[11px]">Ruang: <?php echo e($sidangSemhas->ruangan ?? 'TBA'); ?></div>
                                    <?php $__currentLoopData = $sidangSemhas->pengujiSidangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="text-[11px]"><?php echo e($pj->dosen->name ?? '-'); ?> <em>(<?php echo e(str_replace('_',' ',$pj->peran_penguji)); ?>)</em></div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php elseif($ph && $ph->jadwal_usulan_sidang): ?>
                                    Usulan: <?php echo e(\Carbon\Carbon::parse($ph->jadwal_usulan_sidang)->translatedFormat('d F Y, H:i')); ?>

                                <?php else: ?>
                                    <span class="text-slate-400">Belum ada pendaftaran</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($sidangSemhas): ?>
                                    <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green']); ?>Sudah dijadwalkan <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                                <?php elseif($ph && $ph->status_verifikasi_admin === 'verified'): ?>
                                    <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'yellow']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'yellow']); ?>Menunggu jadwal <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                                <?php elseif($ph && $ph->status_verifikasi_admin === 'pending'): ?>
                                    <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'yellow']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'yellow']); ?>Menunggu Verifikasi <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'yellow']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'yellow']); ?>Tahap Semhas <?php echo $__env->renderComponent(); ?>
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
                            <td class="space-y-1">
                                <?php if($ph && $ph->form_fpt_sh_01_url): ?>
                                    <a href="<?php echo e(BerkasController::url($ph->form_fpt_sh_01_url)); ?>" target="_blank" class="ui-btn ui-btn-sm ui-btn-primary">Permohonan</a>
                                <?php endif; ?>
                                <?php if($sidangSemhas): ?>
                                    <a href="<?php echo e(route('dokumen.cetak', ['kode' => 'SURAT-TUGAS-SEMHAS', 'id' => $sidangSemhas->id])); ?>" class="ui-btn ui-btn-sm ui-btn-outline" target="_blank">Surat Tugas</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($ph && $ph->status_verifikasi_admin === 'pending'): ?>
                                    <div class="flex flex-wrap gap-1.5">
                                        <form action="<?php echo e(route('semhas.verifikasi', $ph->id)); ?>" method="POST">
                                            <?php echo csrf_field(); ?><input type="hidden" name="status_verifikasi_admin" value="verified">
                                            <button type="submit" class="ui-btn ui-btn-sm ui-btn-success">Setujui</button>
                                        </form>
                                        <form action="<?php echo e(route('semhas.verifikasi', $ph->id)); ?>" method="POST">
                                            <?php echo csrf_field(); ?><input type="hidden" name="status_verifikasi_admin" value="rejected">
                                            <button type="submit" class="ui-btn ui-btn-sm ui-btn-danger">Tolak</button>
                                        </form>
                                    </div>
                                <?php elseif($sidangSemhas): ?>
                                    <a href="<?php echo e(route('sidang.penilaian', $sidangSemhas->id)); ?>" class="ui-btn ui-btn-sm ui-btn-primary">Rekap / Nilai</a>
                                <?php else: ?>
                                    <span class="text-[12px] text-slate-400">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="text-center text-slate-500 py-6">Belum ada mahasiswa Seminar Hasil.</td></tr>
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

    <?php if((!$fokusTahap || $fokusTahap === 'ujian') && Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi'])): ?>
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Mahasiswa Ujian Tesis','subtitle' => 'Satu daftar: pendaftaran, jadwal, penguji, dan nilai.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Mahasiswa Ujian Tesis','subtitle' => 'Satu daftar: pendaftaran, jadwal, penguji, dan nilai.']); ?>
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Mahasiswa</th><th>Judul</th><th>Jadwal & Penguji</th><th>Status</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $pengajuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php if($p->status_tahap === 'tahap_4_ujian'): ?>
                        <?php
                            $pu = $p->pendaftaranUjian;
                            $sidangUjian = $p->aktivitasSidangs->firstWhere('tahap_sidang', 'ujian');
                        ?>
                        <tr>
                            <td><strong><?php echo e($p->mahasiswa->name); ?></strong><br><?php echo e($p->mahasiswa->identifier); ?></td>
                            <td class="text-[12.5px]"><?php echo e(\Illuminate\Support\Str::limit($p->judul_tesis, 50)); ?></td>
                            <td class="text-[12.5px]">
                                <?php if($sidangUjian): ?>
                                    <div class="font-semibold"><?php echo e(optional($sidangUjian->waktu_mulai)->translatedFormat('d F Y, H:i') ?? $sidangUjian->waktu_mulai); ?></div>
                                    <div class="text-slate-500 text-[11px]">Ruang: <?php echo e($sidangUjian->ruangan ?? 'TBA'); ?></div>
                                    <?php $__currentLoopData = $sidangUjian->pengujiSidangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="text-[11px]"><?php echo e($pj->dosen->name ?? '-'); ?> <em>(<?php echo e(str_replace('_',' ',$pj->peran_penguji)); ?>)</em></div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php elseif($pu && $pu->jadwal_usulan_sidang): ?>
                                    Usulan: <?php echo e(\Carbon\Carbon::parse($pu->jadwal_usulan_sidang)->translatedFormat('d F Y, H:i')); ?>

                                <?php else: ?>
                                    <span class="text-slate-400">Belum dijadwalkan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($sidangUjian): ?>
                                    <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green']); ?>Sudah dijadwalkan <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                                <?php elseif($pu): ?>
                                    <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'yellow']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'yellow']); ?>Terdaftar <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['color' => 'yellow']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'yellow']); ?>Tahap Ujian <?php echo $__env->renderComponent(); ?>
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
                            <td>
                                <?php if($sidangUjian): ?>
                                    <a href="<?php echo e(route('sidang.penilaian', $sidangUjian->id)); ?>" class="ui-btn ui-btn-sm ui-btn-primary">Rekap / Nilai</a>
                                <?php else: ?>
                                    <span class="text-[12px] text-slate-400">Menunggu plotting</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center text-slate-500 py-6">Belum ada mahasiswa Ujian Tesis.</td></tr>
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
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/dashboard/tabs/pendaftaran.blade.php ENDPATH**/ ?>
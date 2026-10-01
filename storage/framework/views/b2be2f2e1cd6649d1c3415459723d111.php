<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Panduan SOP dan Demo — SIM-TESIS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Panduan SOP dan Demo — SIM-TESIS']); ?>

    <div class="max-w-3xl mx-auto w-full flex flex-col gap-4">

        <a href="<?php echo e(route('public.index')); ?>" class="text-slate-500 hover:text-primary-800 text-[13px] font-semibold">&larr; Kembali ke Halaman Utama</a>

        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Standar Operasional Prosedur (SOP) Tesis Magister']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Standar Operasional Prosedur (SOP) Tesis Magister']); ?>
            <div class="flex flex-col gap-3.5">
                <div class="rounded-xl border-l-4 border-primary-800 bg-slate-50 p-4">
                    <h3 class="text-[14.5px] font-extrabold text-primary-900 mb-1">Tahap 1: Pengajuan Judul dan Penetapan Pembimbing</h3>
                    <p class="text-[13px] text-slate-500 leading-relaxed">Mahasiswa mengajukan judul + 2 usulan dosen + PDF FPT-TI-00. Komisi Tesis menyetujui atau menolak, lalu menetapkan Pembimbing 1 (bidang studi) dan Pembimbing 2 (kependidikan) sesuai kuota.</p>
                </div>
                <div class="rounded-xl border-l-4 border-primary-800 bg-slate-50 p-4">
                    <h3 class="text-[14.5px] font-extrabold text-primary-900 mb-1">Tahap 2: Seminar Proposal</h3>
                    <p class="text-[13px] text-slate-500 leading-relaxed">Pendaftaran minimal H-14, unggah FPT-TI-01. Komisi memverifikasi, memplot jadwal dan 4 penguji, mengirim WA, lalu penguji mengisi rubrik 10 indikator. Komisi merilis rekap dan keputusan sidang.</p>
                </div>
                <div class="rounded-xl border-l-4 border-primary-800 bg-slate-50 p-4">
                    <h3 class="text-[14.5px] font-extrabold text-primary-900 mb-1">Tahap 3: Seminar Hasil</h3>
                    <p class="text-[13px] text-slate-500 leading-relaxed">Syarat: revisi Sempro disahkan Kaprodi, naskah Bab I–V, luaran publikasi. Alur verifikasi, plotting, penilaian, dan rekap sama seperti Sempro.</p>
                </div>
                <div class="rounded-xl border-l-4 border-primary-800 bg-slate-50 p-4">
                    <h3 class="text-[14.5px] font-extrabold text-primary-900 mb-1">Tahap 4: Ujian Tesis dan Yudisium</h3>
                    <p class="text-[13px] text-slate-500 leading-relaxed">Berkas ujian (termasuk TOEFL/EAP dan Turnitin), plotting dewan penguji, rubrik 4 dimensi, BAP, matriks revisi, lalu yudisium.</p>
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

    </div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/public/panduan.blade.php ENDPATH**/ ?>
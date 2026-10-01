<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Sistem Informasi Manajemen Tesis S2 Pendidikan Guru Vokasi']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Sistem Informasi Manajemen Tesis S2 Pendidikan Guru Vokasi']); ?>

    <?php if (isset($component)) { $__componentOriginal66fe996fa752c4d645db36fb63438e8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal66fe996fa752c4d645db36fb63438e8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.hero','data' => ['title' => 'Sistem Informasi Manajemen Tesis','highlight' => 'S2 Pendidikan Guru Vokasi','subtitle' => '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Sistem Informasi Manajemen Tesis','highlight' => 'S2 Pendidikan Guru Vokasi','subtitle' => '']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="ui-btn ui-btn-accent">Masuk ke Dashboard Sistem</a>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="ui-btn ui-btn-accent">Mulai Akses SIM-TESIS</a>
                <a href="<?php echo e(route('public.panduan')); ?>" class="ui-btn text-white border border-white/40">Lihat Alur SOP &rarr;</a>
            <?php endif; ?>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal66fe996fa752c4d645db36fb63438e8e)): ?>
<?php $attributes = $__attributesOriginal66fe996fa752c4d645db36fb63438e8e; ?>
<?php unset($__attributesOriginal66fe996fa752c4d645db36fb63438e8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal66fe996fa752c4d645db36fb63438e8e)): ?>
<?php $component = $__componentOriginal66fe996fa752c4d645db36fb63438e8e; ?>
<?php unset($__componentOriginal66fe996fa752c4d645db36fb63438e8e); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => '4 Pilar Tahapan Tesis']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => '4 Pilar Tahapan Tesis']); ?>
        <div class="grid md:grid-cols-2 gap-4">
            <div class="rounded-xl border border-slate-200 p-4">
                <h4 class="text-primary-800 font-extrabold text-[14px] mb-1.5">1. Penetapan Pembimbing</h4>
                <p class="text-slate-500 text-[13px] leading-relaxed">Alokasi Pembimbing 1 (Bidang Studi) dan Pembimbing 2 (Kependidikan) dengan validasi kuota otomatis oleh Komisi Tesis.</p>
            </div>
            <div class="rounded-xl border border-slate-200 p-4">
                <h4 class="text-primary-800 font-extrabold text-[14px] mb-1.5">2. Seminar Proposal (Sempro)</h4>
                <p class="text-slate-500 text-[13px] leading-relaxed">Pendaftaran minimal H-14, verifikasi berkas FPT-TI-01 s.d 09, dan pengesahan izin riset lapangan.</p>
            </div>
            <div class="rounded-xl border border-slate-200 p-4">
                <h4 class="text-primary-800 font-extrabold text-[14px] mb-1.5">3. Seminar Hasil (Semhas)</h4>
                <p class="text-slate-500 text-[13px] leading-relaxed">Telaah komprehensif Bab I–V serta validasi luaran artikel ilmiah (min. 2 draf &amp; 1 under review jurnal terakreditasi).</p>
            </div>
            <div class="rounded-xl border border-slate-200 p-4">
                <h4 class="text-primary-800 font-extrabold text-[14px] mb-1.5">4. Ujian Tesis & Yudisium</h4>
                <p class="text-slate-500 text-[13px] leading-relaxed">Plotting 4 Dewan Penguji bebas bentrok jadwal, rubrik 4 dimensi evaluasi, BAP daring, dan matriks revisi kelulusan.</p>
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
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/public/index.blade.php ENDPATH**/ ?>
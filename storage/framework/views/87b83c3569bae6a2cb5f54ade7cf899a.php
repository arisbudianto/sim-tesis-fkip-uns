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

<?php
    $tahapAktif = request('tahap', 'ringkasan');
    $tahapValid = ['ringkasan','tahap_1_bimbingan','tahap_2_sempro','tahap_3_semhas','tahap_4_ujian','selesai_yudisium'];
    if (!in_array($tahapAktif, $tahapValid, true)) {
        $tahapAktif = 'ringkasan';
    }
    $kartuTahap = [
        ['id'=>'tahap_1_bimbingan','kicker'=>'Tahap 1','name'=>'Bimbingan','desc'=>'Penetapan pembimbing & proses bimbingan','count'=>$stats['tahap_1_bimbingan'] ?? 0,'done'=>false],
        ['id'=>'tahap_2_sempro','kicker'=>'Tahap 2','name'=>'Sempro','desc'=>'Seminar proposal tesis','count'=>$stats['tahap_2_sempro'] ?? 0,'done'=>false],
        ['id'=>'tahap_3_semhas','kicker'=>'Tahap 3','name'=>'Semhas','desc'=>'Seminar hasil penelitian','count'=>$stats['tahap_3_semhas'] ?? 0,'done'=>false],
        ['id'=>'tahap_4_ujian','kicker'=>'Tahap 4','name'=>'Ujian Tesis','desc'=>'Ujian akhir & revisi naskah','count'=>$stats['tahap_4_ujian'] ?? 0,'done'=>false],
        ['id'=>'selesai_yudisium','kicker'=>'Selesai','name'=>'Lulus / Yudisium','desc'=>'Yudisium & penyerahan naskah','count'=>$stats['selesai_yudisium'] ?? 0,'done'=>true],
    ];
    $labelTahap = [
        'tahap_1_bimbingan' => 'Tahap 1 — Bimbingan',
        'tahap_2_sempro' => 'Tahap 2 — Sempro',
        'tahap_3_semhas' => 'Tahap 3 — Semhas',
        'tahap_4_ujian' => 'Tahap 4 — Ujian Tesis',
        'selesai_yudisium' => 'Selesai — Yudisium',
    ];
?>

<section class="flex flex-col gap-5">
    <div>
        <h2 class="text-[17px] font-extrabold tracking-tight text-primary-900">Tahapan Tesis</h2>
        <p class="text-[12px] text-slate-500 mt-0.5">Pilih tahap untuk membuka data dan aksi.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-2.5">
        <?php $__currentLoopData = $kartuTahap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $aktif = $tahapAktif === $card['id']; ?>
            <a href="<?php echo e(route('dashboard', ['tahap' => $card['id']])); ?>"
               class="rounded-2xl border bg-white p-3.5 text-left w-full transition-all duration-150 block
                      <?php echo e($aktif ? ($card['done'] ? 'ring-2 ring-emerald-300 border-emerald-600 shadow-sm' : 'ring-2 ring-primary-300 border-primary-600 shadow-sm') : 'border-slate-200 hover:border-primary-300 hover:shadow-sm'); ?>">
                <div class="flex items-start justify-between gap-2">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-xl bg-slate-100 text-primary-800"></span>
                    <span class="text-[24px] font-extrabold leading-none tracking-tight <?php echo e($card['done'] ? 'text-emerald-700' : 'text-primary-900'); ?>"><?php echo e($card['count']); ?></span>
                </div>
                <div class="mt-3">
                    <div class="text-[9.5px] font-bold uppercase tracking-[0.13em] text-slate-400"><?php echo e($card['kicker']); ?></div>
                    <div class="mt-0.5 text-[13px] font-extrabold tracking-tight leading-tight text-primary-900"><?php echo e($card['name']); ?></div>
                    <div class="mt-1 text-[10.5px] text-slate-500 leading-snug"><?php echo e($card['desc']); ?></div>
                </div>
                <div class="mt-3 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                    <span class="block h-full rounded-full <?php echo e($card['done'] ? 'bg-emerald-600' : 'bg-gradient-to-r from-primary-800 to-accent'); ?>" style="width:100%"></span>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="<?php echo e(route('dashboard')); ?>"
           class="px-3.5 py-1.5 rounded-lg text-[12.5px] font-bold border transition-colors
                  <?php echo e($tahapAktif === 'ringkasan' ? 'bg-primary-800 text-white border-primary-800' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>">
            Ringkasan Sistem
        </a>
        <?php if($tahapAktif !== 'ringkasan'): ?>
            <span class="text-[12px] text-slate-500">
                Menampilkan: <strong class="text-primary-800"><?php echo e($labelTahap[$tahapAktif]); ?></strong>
            </span>
        <?php endif; ?>
    </div>

    <?php if($tahapAktif === 'ringkasan'): ?>
        <?php echo $__env->make('dashboard.tabs.overview', ['pengajuans' => $pengajuans], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php if($tahapAktif === 'tahap_1_bimbingan'): ?>
        <div class="flex flex-col gap-4">
            <?php echo $__env->make('dashboard.tabs.pengajuan', ['pengajuans' => $pengajuans, 'dosens' => $dosens, 'mahasiswas' => $mahasiswas], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('dashboard.tabs._filter-tahap', ['filterStatus' => 'tahap_1_bimbingan', 'judul' => 'Mahasiswa di Tahap 1 — Bimbingan', 'pengajuans' => $pengajuans], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?>

    <?php if($tahapAktif === 'tahap_2_sempro'): ?>
        <div class="flex flex-col gap-4">
            <h3 class="text-[16px] font-extrabold text-primary-900">Tahap 2 — Seminar Proposal</h3>
            <?php echo $__env->make('dashboard.tabs.pendaftaran', ['pengajuans' => $pengajuans, 'dosens' => $dosens, 'fokusTahap' => 'sempro'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('dashboard.tabs.wa-blast', ['sidangs' => $sidangs->where('tahap_sidang', 'sempro')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('dashboard.tabs.sidang', ['pengajuans' => $pengajuans, 'dosens' => $dosens, 'komisi' => $komisi, 'sidangs' => $sidangs, 'fokusTahap' => 'sempro'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?>

    <?php if($tahapAktif === 'tahap_3_semhas'): ?>
        <div class="flex flex-col gap-4">
            <h3 class="text-[16px] font-extrabold text-primary-900">Tahap 3 — Seminar Hasil</h3>
            <?php echo $__env->make('dashboard.tabs.pendaftaran', ['pengajuans' => $pengajuans, 'dosens' => $dosens, 'fokusTahap' => 'semhas'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('dashboard.tabs.wa-blast', ['sidangs' => $sidangs->where('tahap_sidang', 'semhas')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('dashboard.tabs.sidang', ['pengajuans' => $pengajuans, 'dosens' => $dosens, 'komisi' => $komisi, 'sidangs' => $sidangs, 'fokusTahap' => 'semhas'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?>

    <?php if($tahapAktif === 'tahap_4_ujian'): ?>
        <div class="flex flex-col gap-4">
            <h3 class="text-[16px] font-extrabold text-primary-900">Tahap 4 — Ujian Tesis</h3>
            <?php echo $__env->make('dashboard.tabs.pendaftaran', ['pengajuans' => $pengajuans, 'dosens' => $dosens, 'fokusTahap' => 'ujian'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('dashboard.tabs.wa-blast', ['sidangs' => $sidangs->where('tahap_sidang', 'ujian')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('dashboard.tabs.sidang', ['pengajuans' => $pengajuans, 'dosens' => $dosens, 'komisi' => $komisi, 'sidangs' => $sidangs, 'fokusTahap' => 'ujian'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('dashboard.tabs._filter-tahap', ['filterStatus' => 'tahap_4_ujian', 'judul' => 'Mahasiswa di Tahap 4 — Ujian Tesis', 'pengajuans' => $pengajuans], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?>

    <?php if($tahapAktif === 'selesai_yudisium'): ?>
        <div class="flex flex-col gap-4">
            <h3 class="text-[16px] font-extrabold text-primary-900">Selesai — Yudisium</h3>
            <?php if ($__env->exists('dashboard.tabs.revisi')) echo $__env->make('dashboard.tabs.revisi', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('dashboard.tabs._filter-tahap', ['filterStatus' => 'selesai_yudisium', 'judul' => 'Mahasiswa Lulus / Siap Yudisium', 'pengajuans' => $pengajuans], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?>
</section>

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
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/dashboard.blade.php ENDPATH**/ ?>
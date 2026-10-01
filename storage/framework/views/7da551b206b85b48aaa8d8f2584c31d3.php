<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? 'Sistem Informasi Manajemen Tesis S2 Pendidikan Guru Vokasi'); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-[420px]">
        <div class="flex items-center justify-center gap-2.5 mb-5">
            <img src="<?php echo e(asset('assets/logo-uns.png')); ?>" alt="Logo UNS" class="h-10 w-10 object-contain">
            <span class="text-[16px] font-extrabold tracking-tight text-primary-900">SIM-TESIS</span>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
            <?php echo e($slot); ?>

        </div>
    </div>

</body>
</html>
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/components/layouts/guest.blade.php ENDPATH**/ ?>
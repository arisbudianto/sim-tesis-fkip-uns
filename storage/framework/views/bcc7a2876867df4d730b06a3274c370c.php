<?php
    $role = Auth::user()->role ?? null;
    $roles = Auth::user()->roleList();
?>

<?php if(in_array('mahasiswa', $roles, true) && !in_array('kaprodi', $roles, true) && !in_array('komisi_tesis', $roles, true)): ?>
    <?php echo $__env->make('dashboard.roles.mahasiswa', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<?php if($role === 'komisi_tesis'): ?>
    <?php echo $__env->make('dashboard.roles.komisi-tesis', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="mt-4 flex flex-col gap-4">
        <?php echo $__env->make('dashboard.tabs._master-data', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
<?php endif; ?>

<?php if($role === 'admin_prodi'): ?>
    <?php echo $__env->make('dashboard.roles.admin-prodi', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="mt-4 flex flex-col gap-4">
        <?php echo $__env->make('dashboard.tabs._master-data', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
<?php endif; ?>

<?php if(in_array('kaprodi', $roles, true)): ?>
    <?php echo $__env->make('dashboard.roles.kaprodi', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<?php if(in_array('dosen', $roles, true) && !in_array('kaprodi', $roles, true) && $role !== 'komisi_tesis'): ?>
    <?php echo $__env->make('dashboard.roles.dosen', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<?php if(in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi'])): ?>
    <?php echo $__env->make('dashboard.tabs.audit', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="mt-4">
        <h3 class="text-[14px] font-extrabold text-primary-900 mb-2">Seluruh Pengajuan Tesis (Operasional)</h3>
        <?php echo $__env->make('dashboard.tabs._semua-pengajuan', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
<?php endif; ?>
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/dashboard/tabs/overview.blade.php ENDPATH**/ ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun - SIM-TESIS FKIP UNS</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f1f5f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 24px 16px; }
        .reg-card { background: white; width: 100%; max-width: 460px; padding: 32px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08); }
        .reg-card h2 { margin: 0 0 6px 0; color: #1e3a8a; font-size: 22px; font-weight: 700; text-align: center; }
        .reg-card p { margin: 0 0 20px 0; color: #64748b; font-size: 13.5px; text-align: center; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; color: #334155; }
        .form-control { width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; }
        .form-control:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .btn-submit { width: 100%; padding: 11px; background: #16a34a; color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s; margin-top: 8px; }
        .btn-submit:hover { background: #15803d; }
        .alert { padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 16px; background: #fee2e2; color: #991b1b; }
        .footer-link { text-align: center; margin-top: 18px; font-size: 13.5px; color: #64748b; }
        .footer-link a { color: #2563eb; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="reg-card">
        <h2>Registrasi Akun SIM-TESIS</h2>
        <p>Pendaftaran Civitas Akademika FKIP UNS</p>

        <?php if($errors->any()): ?>
            <div class="alert"><?php echo e($errors->first()); ?></div>
        <?php endif; ?>

        <form action="<?php echo e(route('register.post')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label>Nama Lengkap (beserta gelar jika dosen):</label>
                <input type="text" name="name" class="form-control" value="<?php echo e(old('name')); ?>" required placeholder="Contoh: Budi Santoso, S.Pd.">
            </div>
            <div class="form-group">
                <label>Nomor Induk (NIM / NIP):</label>
                <input type="text" name="identifier" class="form-control" value="<?php echo e(old('identifier')); ?>" required placeholder="Contoh: S032608001">
            </div>
            <div class="form-group">
                <label>Email Resmi (@uns.ac.id / @student.uns.ac.id):</label>
                <input type="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>" required placeholder="nama@student.uns.ac.id">
            </div>
            <div class="form-group">
                <label>Peran / Role Pengguna:</label>
                <select name="role" class="form-control" required onchange="toggleBidang(this.value)">
                    <option value="mahasiswa">Mahasiswa Pascasarjana</option>
                    <option value="dosen">Dosen Pembimbing / Penguji</option>
                </select>
            </div>
            <div class="form-group" id="group-bidang" style="display:none;">
                <label>Bidang Keahlian Dosen:</label>
                <select name="bidang_keahlian" class="form-control">
                    <option value="studi">Spesialis Bidang Studi Kejuruan</option>
                    <option value="pendidikan">Spesialis Metodologi & Kependidikan</option>
                </select>
            </div>
            <div class="form-group">
                <label>Kata Sandi (Minimal 6 karakter):</label>
                <input type="password" name="password" class="form-control" required placeholder="Buat kata sandi...">
            </div>
            <div class="form-group">
                <label>Konfirmasi Kata Sandi:</label>
                <input type="password" name="password_confirmation" class="form-control" required placeholder="Ulangi kata sandi...">
            </div>
            <button type="submit" class="btn-submit">Daftar Akun Sekarang</button>
        </form>

        <div class="footer-link">
            Sudah memiliki akun? <a href="<?php echo e(route('login')); ?>">Masuk di sini</a><br>
            <a href="<?php echo e(route('public.index')); ?>" style="display:inline-block; margin-top:8px; color:#64748b;">← Halaman Publik</a>
        </div>
    </div>

    <script>
        function toggleBidang(role) {
            document.getElementById('group-bidang').style.display = (role === 'dosen') ? 'block' : 'none';
        }
    </script>
</body>
</html>
<?php /**PATH /home/speakver/pgv.speakverse.id/resources/views/auth/register.blade.php ENDPATH**/ ?>
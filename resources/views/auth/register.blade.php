<x-layouts.guest title="Daftar Akun — SIM-TESIS FKIP UNS">

    <h2 class="text-[19px] font-extrabold text-primary-900 text-center mb-1">Registrasi Akun SIM-TESIS</h2>
    <p class="text-slate-500 text-[13px] text-center mb-6">Pendaftaran Civitas Akademika FKIP UNS</p>

    @if($errors->any())
        <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <form action="{{ route('register.post') }}" method="POST" x-data="{ role: 'mahasiswa' }">
        @csrf
        <div class="ui-field">
            <label class="ui-label">Nama Lengkap (beserta gelar jika dosen)</label>
            <input type="text" name="name" class="ui-input" value="{{ old('name') }}" required placeholder="Contoh: Budi Santoso, S.Pd.">
        </div>
        <div class="ui-field">
            <label class="ui-label">Nomor Induk (NIM / NIP)</label>
            <input type="text" name="identifier" class="ui-input" value="{{ old('identifier') }}" required placeholder="Contoh: S032608001">
        </div>
        <div class="ui-field">
            <label class="ui-label">Email Resmi (@uns.ac.id / @student.uns.ac.id)</label>
            <input type="email" name="email" class="ui-input" value="{{ old('email') }}" required placeholder="nama@student.uns.ac.id">
        </div>
        <div class="ui-field">
            <label class="ui-label">Nomor WhatsApp Aktif</label>
            <input type="text" name="nomor_wa" class="ui-input" value="{{ old('nomor_wa') }}" placeholder="08123456789">
            <p class="text-[11.5px] text-slate-500 mt-1">Dipakai sistem untuk mengirim notifikasi undangan sidang & status pendaftaran. Boleh dikosongkan, tapi Anda tidak akan menerima notifikasi WhatsApp.</p>
        </div>
        <div class="ui-field">
            <label class="ui-label">Peran / Role Pengguna</label>
            <select name="role" class="ui-input" required x-model="role">
                <option value="mahasiswa">Mahasiswa Pascasarjana</option>
                <option value="dosen">Dosen Pembimbing / Penguji</option>
            </select>
        </div>
        <div class="ui-field" x-show="role === 'dosen'" x-cloak>
            <label class="ui-label">Bidang Keahlian Dosen</label>
            <select name="bidang_keahlian" class="ui-input">
                <option value="studi">Spesialis Bidang Studi Kejuruan</option>
                <option value="pendidikan">Spesialis Metodologi & Kependidikan</option>
            </select>
        </div>
        <div class="ui-field">
            <label class="ui-label">Kata Sandi (Minimal 6 karakter)</label>
            <input type="password" name="password" class="ui-input" required placeholder="Buat kata sandi...">
        </div>
        <div class="ui-field">
            <label class="ui-label">Konfirmasi Kata Sandi</label>
            <input type="password" name="password_confirmation" class="ui-input" required placeholder="Ulangi kata sandi...">
        </div>
        <button type="submit" class="ui-btn ui-btn-success w-full justify-center py-2.5 mt-1">Daftar Akun Sekarang</button>
    </form>

    <div class="text-center mt-5 text-[13px] text-slate-500">
        Sudah memiliki akun? <a href="{{ route('login') }}" class="font-bold text-primary-800">Masuk di sini</a>
        <div class="mt-2.5">
            <a href="{{ route('public.index') }}" class="text-slate-400 hover:text-primary-800">&larr; Halaman Publik</a>
        </div>
    </div>

</x-layouts.guest>

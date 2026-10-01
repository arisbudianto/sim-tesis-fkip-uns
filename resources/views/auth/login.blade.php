<x-layouts.guest title="Masuk — SIM-TESIS FKIP UNS">

    <h2 class="text-[19px] font-extrabold text-primary-900 text-center mb-1">Masuk ke Sistem</h2>
    <p class="text-slate-500 text-[13px] text-center mb-6">Portal Akademik Manajemen Tesis Magister</p>

    @if($errors->any())
        <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    @if(session('info'))
        <x-ui.alert type="info">{{ session('info') }}</x-ui.alert>
    @endif

    <form action="{{ route('login.post') }}" method="POST">
        @csrf
        <div class="ui-field">
            <label class="ui-label">NIM (Mahasiswa) / NIP (Dosen/Admin)</label>
            <input type="text" name="identifier" class="ui-input" value="{{ old('identifier') }}" required
                   placeholder="Contoh: S032608001 atau NIP" autofocus>
        </div>
        <div class="ui-field">
            <label class="ui-label">Kata Sandi</label>
            <input type="password" name="password" class="ui-input" required placeholder="Masukkan kata sandi...">
            <div class="text-right mt-1.5">
                <a href="{{ route('password.request') }}" class="text-[11.5px] font-semibold text-primary-800">Lupa password?</a>
            </div>
        </div>
        <button type="submit" class="ui-btn ui-btn-primary w-full justify-center py-2.5">Masuk ke Sistem</button>
    </form>

    <div class="text-center mt-5 text-[13px] text-slate-500">
        <a href="{{ route('public.index') }}" class="text-slate-400 hover:text-primary-800">&larr; Kembali ke Halaman Publik</a>
    </div>

</x-layouts.guest>

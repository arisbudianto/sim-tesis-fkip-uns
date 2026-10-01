<x-layouts.guest title="Lupa Password — SIM-TESIS FKIP UNS">

    <h2 class="text-[19px] font-extrabold text-primary-900 text-center mb-1">Lupa Password</h2>
    <p class="text-slate-500 text-[13px] text-center mb-6">Masukkan email terdaftar Anda, kami kirimkan tautan reset password.</p>

    @if(session('info'))
        <x-ui.alert type="success">{{ session('info') }}</x-ui.alert>
    @endif
    @if($errors->any())
        <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <form action="{{ route('password.email') }}" method="POST">
        @csrf
        <div class="ui-field">
            <label class="ui-label">Email Terdaftar</label>
            <input type="email" name="email" class="ui-input" value="{{ old('email') }}" required autofocus placeholder="nama@student.uns.ac.id">
        </div>
        <button type="submit" class="ui-btn ui-btn-primary w-full justify-center py-2.5">Kirim Tautan Reset</button>
    </form>

    <div class="text-center mt-5 text-[13px] text-slate-500">
        <a href="{{ route('login') }}" class="font-bold text-primary-800">&larr; Kembali ke Login</a>
    </div>

</x-layouts.guest>

<x-layouts.guest title="Reset Password — SIM-TESIS FKIP UNS">

    <h2 class="text-[19px] font-extrabold text-primary-900 text-center mb-1">Reset Password</h2>
    <p class="text-slate-500 text-[13px] text-center mb-6">Masukkan password baru Anda.</p>

    @if($errors->any())
        <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="ui-field">
            <label class="ui-label">Email</label>
            <input type="email" name="email" class="ui-input" value="{{ old('email', $email) }}" required autofocus>
        </div>
        <div class="ui-field">
            <label class="ui-label">Password Baru (minimal 6 karakter)</label>
            <input type="password" name="password" class="ui-input" required>
        </div>
        <div class="ui-field">
            <label class="ui-label">Konfirmasi Password Baru</label>
            <input type="password" name="password_confirmation" class="ui-input" required>
        </div>
        <button type="submit" class="ui-btn ui-btn-primary w-full justify-center py-2.5">Reset Password</button>
    </form>

    <div class="text-center mt-5 text-[13px] text-slate-500">
        <a href="{{ route('login') }}" class="font-bold text-primary-800">&larr; Kembali ke Login</a>
    </div>

</x-layouts.guest>

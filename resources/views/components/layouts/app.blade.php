<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Sistem Informasi Manajemen Tesis S2 Pendidikan Guru Vokasi' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-slate-100">

    <div class="max-w-[1180px] mx-auto p-5 flex flex-col gap-5">

        {{-- Topbar: identitas sistem + navigasi publik + status auth --}}
        <header class="flex items-center justify-between gap-4 pb-4 border-b-2 border-primary-800 bg-white rounded-2xl px-5 py-3.5 shadow-sm">
            <div class="flex items-center gap-3.5">
                <img src="{{ asset('assets/logo-uns.png') }}" alt="Logo Universitas Sebelas Maret" class="h-11 w-11 object-contain shrink-0">
                <div class="h-9 w-px bg-slate-200"></div>
                <div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-[15.5px] font-extrabold tracking-tight text-primary-900">SIM-TESIS</span>
                        <span class="text-[9px] font-bold uppercase tracking-widest text-[#B4801A] bg-accent-soft px-2 py-[3px] rounded-full">v2.0</span>
                    </div>
                    <div class="text-[11px] leading-tight text-slate-500">S2 Pendidikan Guru Vokasi</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('public.index') }}" class="px-2.5 py-1.5 text-[12.5px] font-semibold text-slate-600 hover:text-primary-800">Halaman Publik</a>
                <a href="{{ route('public.panduan') }}" class="px-2.5 py-1.5 text-[12.5px] font-semibold text-slate-600 hover:text-primary-800">Panduan SOP</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-2.5 py-1.5 text-[12.5px] font-semibold text-slate-600 hover:text-primary-800">Dashboard Saya</a>
                @endauth

                @auth
                    <span class="px-3.5 py-1.5 rounded-full text-[12px] font-bold text-primary-800 bg-slate-100">
                        {{ Auth::user()->name }} &middot; {{ strtoupper(Auth::user()->role) }}
                    </span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="ui-btn ui-btn-sm ui-btn-danger">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="ui-btn ui-btn-sm ui-btn-primary">Masuk</a>
                @endauth
            </div>
        </header>

        <main class="flex flex-col gap-5">
            {{ $slot }}
        </main>

    </div>

</body>
</html>

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

    <div x-data="{ sidebarOpen: false }" class="md:flex md:items-start">

        {{-- Backdrop, mobile only --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             class="fixed inset-0 bg-slate-900/40 z-40 md:hidden" x-transition.opacity></div>

        {{-- Sidebar --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed md:sticky top-0 left-0 z-50 h-screen w-72 shrink-0 bg-white border-r border-slate-200
                   transition-transform duration-200 ease-out md:translate-x-0">
            <button @click="sidebarOpen = false" class="md:hidden absolute top-3 right-3 p-1.5 rounded-lg text-slate-400 hover:bg-slate-100">
                <x-ui.icon name="x-mark" class="h-5 w-5" />
            </button>
            @include('components.layouts.partials.sidebar', ['stats' => $stats ?? null])
        </aside>

        {{-- Konten --}}
        <div class="flex-1 min-w-0">

            {{-- Topbar mobile: tombol buka menu --}}
            <div class="md:hidden sticky top-0 z-30 flex items-center gap-3 bg-white border-b border-slate-200 px-4 py-3">
                <button @click="sidebarOpen = true" class="p-1.5 -ml-1.5 rounded-lg text-primary-800 hover:bg-slate-100">
                    <x-ui.icon name="bars-3" class="h-5 w-5" />
                </button>
                <span class="text-[13.5px] font-extrabold text-primary-900">SIM-TESIS</span>
            </div>

            <main class="max-w-[1180px] mx-auto p-5 flex flex-col gap-5">
                @if(session('success'))
                    <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
                @endif
                @if(session('info'))
                    <x-ui.alert type="info">{{ session('info') }}</x-ui.alert>
                @endif
                @if($errors->any() && request()->routeIs('dashboard'))
                    <x-ui.alert type="error">
                        @foreach($errors->all() as $error) {{ $error }}<br> @endforeach
                    </x-ui.alert>
                @endif

                {{ $slot }}
            </main>
        </div>

    </div>

</body>
</html>

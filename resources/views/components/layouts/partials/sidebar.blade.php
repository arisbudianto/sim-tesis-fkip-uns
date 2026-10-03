@php
    // Sidebar mandiri: ambil data sendiri dari Auth + request, supaya SEMUA
    // halaman yang pakai x-layouts.app (termasuk halaman publik & form yang
    // tidak mengirim $stats/$tahapAktif) tetap bisa menampilkan menu tanpa
    // perlu setiap controller mengoper props tambahan.
    $navUser = Auth::user();
    $navRoles = $navUser ? $navUser->roleList() : [];
    $navIsPengendali = $navUser && $navUser->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi']);
    $navTahapAktif = request()->routeIs('dashboard') ? request('tahap', 'ringkasan') : null;

    $navPipeline = [
        ['id' => 'tahap_1_bimbingan', 'label' => 'Tahap 1 — Bimbingan', 'icon' => 'users'],
        ['id' => 'tahap_2_sempro', 'label' => 'Tahap 2 — Sempro', 'icon' => 'document-text'],
        ['id' => 'tahap_3_semhas', 'label' => 'Tahap 3 — Semhas', 'icon' => 'chart-bar'],
        ['id' => 'tahap_4_ujian', 'label' => 'Tahap 4 — Ujian Tesis', 'icon' => 'academic-cap'],
        ['id' => 'selesai_yudisium', 'label' => 'Selesai — Yudisium', 'icon' => 'check-badge'],
    ];
@endphp

<div class="flex flex-col h-full">

    {{-- Brand --}}
    <div class="flex items-center gap-3 px-4 pt-5 pb-4">
        <img src="{{ asset('assets/logo-uns.png') }}" alt="Logo Universitas Sebelas Maret" class="h-10 w-10 object-contain shrink-0">
        <div class="min-w-0">
            <div class="flex items-baseline gap-1.5">
                <span class="text-[14.5px] font-extrabold tracking-tight text-primary-900">SIM-TESIS</span>
                <span class="text-[8.5px] font-bold uppercase tracking-widest text-[#B4801A] bg-accent-soft px-1.5 py-[2px] rounded-full">v2.0</span>
            </div>
            <div class="text-[10.5px] leading-tight text-slate-500 truncate">S2 Pendidikan Guru Vokasi</div>
        </div>
    </div>

    <div class="h-px bg-slate-200 mx-4 mb-3"></div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto px-3 pb-4">

        @if($navUser)
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-[13px] mb-1 transition-colors
                      {{ $navTahapAktif === 'ringkasan' ? 'bg-slate-100 text-primary-900 font-extrabold' : 'text-slate-600 font-semibold hover:bg-slate-50 hover:text-primary-800' }}">
                <x-ui.icon name="home" class="h-[18px] w-[18px] shrink-0" />
                Ringkasan Sistem
            </a>

            <div class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-primary-700/70 px-3 mt-5 mb-2">Pipeline Tesis</div>
            @foreach($navPipeline as $item)
                @php $aktif = $navTahapAktif === $item['id']; @endphp
                <a href="{{ route('dashboard', ['tahap' => $item['id']]) }}"
                   class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-[13px] mb-1 transition-colors
                          {{ $aktif ? 'bg-slate-100 text-primary-900 font-extrabold' : 'text-slate-600 font-semibold hover:bg-slate-50 hover:text-primary-800' }}">
                    <x-ui.icon name="{{ $item['icon'] }}" class="h-[18px] w-[18px] shrink-0" />
                    <span class="truncate">{{ $item['label'] }}</span>
                    @if(isset($stats[$item['id']]))
                        <span class="ml-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10.5px] font-extrabold
                                      {{ $aktif ? 'bg-primary-800 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $stats[$item['id']] }}</span>
                    @endif
                </a>
            @endforeach

            @if($navIsPengendali)
                <div class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-primary-700/70 px-3 mt-5 mb-2">Administrasi</div>

                <a href="{{ route('dashboard', ['tahap' => 'semua_pengajuan']) }}"
                   class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-[13px] mb-1 transition-colors
                          {{ $navTahapAktif === 'semua_pengajuan' ? 'bg-slate-100 text-primary-900 font-extrabold' : 'text-slate-600 font-semibold hover:bg-slate-50 hover:text-primary-800' }}">
                    <x-ui.icon name="table-cells" class="h-[18px] w-[18px] shrink-0" />
                    Seluruh Pengajuan
                </a>
                @if($navUser->hasAnyRole(['komisi_tesis', 'admin_prodi']))
                <a href="{{ route('dashboard', ['tahap' => 'master_data']) }}"
                   class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-[13px] mb-1 transition-colors
                          {{ $navTahapAktif === 'master_data' ? 'bg-slate-100 text-primary-900 font-extrabold' : 'text-slate-600 font-semibold hover:bg-slate-50 hover:text-primary-800' }}">
                    <x-ui.icon name="circle-stack" class="h-[18px] w-[18px] shrink-0" />
                    Data Master
                </a>
                @endif
                <a href="{{ route('dashboard', ['tahap' => 'audit_log']) }}"
                   class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-[13px] mb-1 transition-colors
                          {{ $navTahapAktif === 'audit_log' ? 'bg-slate-100 text-primary-900 font-extrabold' : 'text-slate-600 font-semibold hover:bg-slate-50 hover:text-primary-800' }}">
                    <x-ui.icon name="clipboard-list" class="h-[18px] w-[18px] shrink-0" />
                    Audit Log & Histori
                </a>
            @endif
        @endif

        <div class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-primary-700/70 px-3 mt-5 mb-2">Lainnya</div>
        <a href="{{ route('public.index') }}"
           class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-[13px] mb-1 transition-colors
                  {{ request()->routeIs('public.index') ? 'bg-slate-100 text-primary-900 font-extrabold' : 'text-slate-600 font-semibold hover:bg-slate-50 hover:text-primary-800' }}">
            <x-ui.icon name="globe-alt" class="h-[18px] w-[18px] shrink-0" />
            Halaman Publik
        </a>
        <a href="{{ route('public.panduan') }}"
           class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-[13px] mb-1 transition-colors
                  {{ request()->routeIs('public.panduan') ? 'bg-slate-100 text-primary-900 font-extrabold' : 'text-slate-600 font-semibold hover:bg-slate-50 hover:text-primary-800' }}">
            <x-ui.icon name="book-open" class="h-[18px] w-[18px] shrink-0" />
            Panduan SOP
        </a>
    </nav>

    {{-- Footer: akun --}}
    <div class="border-t border-slate-200 p-3.5">
        @auth
            <div class="flex items-center gap-2.5 rounded-xl px-2 py-2 mb-1.5">
                <span class="flex items-center justify-center h-8 w-8 rounded-full bg-slate-100 text-primary-800 shrink-0">
                    <x-ui.icon name="user-circle" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <div class="text-[12.5px] font-bold text-primary-900 truncate">{{ Auth::user()->name }}</div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ str_replace('_', ' ', Auth::user()->role) }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="ml-auto">
                    @csrf
                    <button type="submit" title="Keluar" class="p-2 rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                        <x-ui.icon name="logout" class="h-[18px] w-[18px]" />
                    </button>
                </form>
            </div>
        @else
            <a href="{{ route('login') }}" class="ui-btn ui-btn-primary w-full">Masuk</a>
        @endauth
        <p class="text-center text-[10.5px] text-slate-400 mt-2">&copy; {{ now()->year }} SIM-TESIS S2 PGV</p>
    </div>
</div>

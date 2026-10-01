<x-layouts.app title="Sistem Informasi Manajemen Tesis S2 Pendidikan Guru Vokasi">

    <x-ui.hero
        title="Sistem Informasi Manajemen Tesis"
        highlight="S2 Pendidikan Guru Vokasi"
        subtitle=""
    />

@php
    $tahapAktif = request('tahap', 'ringkasan');
    $tahapValid = ['ringkasan','tahap_1_bimbingan','tahap_2_sempro','tahap_3_semhas','tahap_4_ujian','selesai_yudisium'];
    if (!in_array($tahapAktif, $tahapValid, true)) {
        $tahapAktif = 'ringkasan';
    }
    $kartuTahap = [
        ['id'=>'tahap_1_bimbingan','kicker'=>'Tahap 1','name'=>'Bimbingan','desc'=>'Penetapan pembimbing & proses bimbingan','count'=>$stats['tahap_1_bimbingan'] ?? 0,'done'=>false],
        ['id'=>'tahap_2_sempro','kicker'=>'Tahap 2','name'=>'Sempro','desc'=>'Seminar proposal tesis','count'=>$stats['tahap_2_sempro'] ?? 0,'done'=>false],
        ['id'=>'tahap_3_semhas','kicker'=>'Tahap 3','name'=>'Semhas','desc'=>'Seminar hasil penelitian','count'=>$stats['tahap_3_semhas'] ?? 0,'done'=>false],
        ['id'=>'tahap_4_ujian','kicker'=>'Tahap 4','name'=>'Ujian Tesis','desc'=>'Ujian akhir & revisi naskah','count'=>$stats['tahap_4_ujian'] ?? 0,'done'=>false],
        ['id'=>'selesai_yudisium','kicker'=>'Selesai','name'=>'Lulus / Yudisium','desc'=>'Yudisium & penyerahan naskah','count'=>$stats['selesai_yudisium'] ?? 0,'done'=>true],
    ];
    $labelTahap = [
        'tahap_1_bimbingan' => 'Tahap 1 — Bimbingan',
        'tahap_2_sempro' => 'Tahap 2 — Sempro',
        'tahap_3_semhas' => 'Tahap 3 — Semhas',
        'tahap_4_ujian' => 'Tahap 4 — Ujian Tesis',
        'selesai_yudisium' => 'Selesai — Yudisium',
    ];
@endphp

<section class="flex flex-col gap-5">
    <div>
        <h2 class="text-[17px] font-extrabold tracking-tight text-primary-900">Tahapan Tesis</h2>
        <p class="text-[12px] text-slate-500 mt-0.5">Pilih tahap untuk membuka data dan aksi.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-2.5">
        @foreach($kartuTahap as $card)
            @php $aktif = $tahapAktif === $card['id']; @endphp
            <a href="{{ route('dashboard', ['tahap' => $card['id']]) }}"
               class="rounded-2xl border bg-white p-3.5 text-left w-full transition-all duration-150 block
                      {{ $aktif ? ($card['done'] ? 'ring-2 ring-emerald-300 border-emerald-600 shadow-sm' : 'ring-2 ring-primary-300 border-primary-600 shadow-sm') : 'border-slate-200 hover:border-primary-300 hover:shadow-sm' }}">
                <div class="flex items-start justify-between gap-2">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-xl bg-slate-100 text-primary-800"></span>
                    <span class="text-[24px] font-extrabold leading-none tracking-tight {{ $card['done'] ? 'text-emerald-700' : 'text-primary-900' }}">{{ $card['count'] }}</span>
                </div>
                <div class="mt-3">
                    <div class="text-[9.5px] font-bold uppercase tracking-[0.13em] text-slate-400">{{ $card['kicker'] }}</div>
                    <div class="mt-0.5 text-[13px] font-extrabold tracking-tight leading-tight text-primary-900">{{ $card['name'] }}</div>
                    <div class="mt-1 text-[10.5px] text-slate-500 leading-snug">{{ $card['desc'] }}</div>
                </div>
                <div class="mt-3 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                    <span class="block h-full rounded-full {{ $card['done'] ? 'bg-emerald-600' : 'bg-gradient-to-r from-primary-800 to-accent' }}" style="width:100%"></span>
                </div>
            </a>
        @endforeach
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('dashboard') }}"
           class="px-3.5 py-1.5 rounded-lg text-[12.5px] font-bold border transition-colors
                  {{ $tahapAktif === 'ringkasan' ? 'bg-primary-800 text-white border-primary-800' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            Ringkasan Sistem
        </a>
        @if($tahapAktif !== 'ringkasan')
            <span class="text-[12px] text-slate-500">
                Menampilkan: <strong class="text-primary-800">{{ $labelTahap[$tahapAktif] }}</strong>
            </span>
        @endif
    </div>

    @if($tahapAktif === 'ringkasan')
        @include('dashboard.tabs.overview', ['pengajuans' => $pengajuans])
    @endif

    @if($tahapAktif === 'tahap_1_bimbingan')
        <div class="flex flex-col gap-4">
            @include('dashboard.tabs.pengajuan', ['pengajuans' => $pengajuans, 'dosens' => $dosens, 'mahasiswas' => $mahasiswas])
            @include('dashboard.tabs._filter-tahap', ['filterStatus' => 'tahap_1_bimbingan', 'judul' => 'Mahasiswa di Tahap 1 — Bimbingan', 'pengajuans' => $pengajuans])
        </div>
    @endif

    @if($tahapAktif === 'tahap_2_sempro')
        <div class="flex flex-col gap-4">
            <h3 class="text-[16px] font-extrabold text-primary-900">Tahap 2 — Seminar Proposal</h3>
            @include('dashboard.tabs.pendaftaran', ['pengajuans' => $pengajuans, 'dosens' => $dosens, 'fokusTahap' => 'sempro'])
            @include('dashboard.tabs.wa-blast', ['sidangs' => $sidangs->where('tahap_sidang', 'sempro')])
            @include('dashboard.tabs.sidang', ['pengajuans' => $pengajuans, 'dosens' => $dosens, 'komisi' => $komisi, 'sidangs' => $sidangs, 'fokusTahap' => 'sempro'])
        </div>
    @endif

    @if($tahapAktif === 'tahap_3_semhas')
        <div class="flex flex-col gap-4">
            <h3 class="text-[16px] font-extrabold text-primary-900">Tahap 3 — Seminar Hasil</h3>
            @include('dashboard.tabs.pendaftaran', ['pengajuans' => $pengajuans, 'dosens' => $dosens, 'fokusTahap' => 'semhas'])
            @include('dashboard.tabs.wa-blast', ['sidangs' => $sidangs->where('tahap_sidang', 'semhas')])
            @include('dashboard.tabs.sidang', ['pengajuans' => $pengajuans, 'dosens' => $dosens, 'komisi' => $komisi, 'sidangs' => $sidangs, 'fokusTahap' => 'semhas'])
        </div>
    @endif

    @if($tahapAktif === 'tahap_4_ujian')
        <div class="flex flex-col gap-4">
            <h3 class="text-[16px] font-extrabold text-primary-900">Tahap 4 — Ujian Tesis</h3>
            @include('dashboard.tabs.pendaftaran', ['pengajuans' => $pengajuans, 'dosens' => $dosens, 'fokusTahap' => 'ujian'])
            @include('dashboard.tabs.wa-blast', ['sidangs' => $sidangs->where('tahap_sidang', 'ujian')])
            @include('dashboard.tabs.sidang', ['pengajuans' => $pengajuans, 'dosens' => $dosens, 'komisi' => $komisi, 'sidangs' => $sidangs, 'fokusTahap' => 'ujian'])
            @include('dashboard.tabs._filter-tahap', ['filterStatus' => 'tahap_4_ujian', 'judul' => 'Mahasiswa di Tahap 4 — Ujian Tesis', 'pengajuans' => $pengajuans])
        </div>
    @endif

    @if($tahapAktif === 'selesai_yudisium')
        <div class="flex flex-col gap-4">
            <h3 class="text-[16px] font-extrabold text-primary-900">Selesai — Yudisium</h3>
            @includeIf('dashboard.tabs.revisi')
            @include('dashboard.tabs._filter-tahap', ['filterStatus' => 'selesai_yudisium', 'judul' => 'Mahasiswa Lulus / Siap Yudisium', 'pengajuans' => $pengajuans])
        </div>
    @endif
</section>

</x-layouts.app>

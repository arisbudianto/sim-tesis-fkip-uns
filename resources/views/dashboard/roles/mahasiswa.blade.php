@if(!$myPengajuan)
<x-ui.card title="Belum Ada Pengajuan Tesis">
    <p class="text-slate-500 text-[13.5px]">Anda belum mengajukan judul tesis. Silakan isi form di tab "Usulan &amp; Alokasi Pembimbing".</p>
</x-ui.card>
@else
<div class="flex flex-col gap-4">

    <x-ui.card title="Status Tesis Saya">
        <x-ui.info-row label="Judul Tesis">{{ $myPengajuan->judul_tesis }}</x-ui.info-row>
        <x-ui.info-row label="Pembimbing 1">{{ $myPengajuan->pembimbing1->name ?? '(belum ditetapkan)' }}</x-ui.info-row>
        <x-ui.info-row label="Pembimbing 2">{{ $myPengajuan->pembimbing2->name ?? '(belum ditetapkan)' }}</x-ui.info-row>
        <x-ui.info-row label="Status Saat Ini">
            <x-ui.status-badge :status="$myPengajuan->status_tahap" />
        </x-ui.info-row>
    </x-ui.card>

    <x-ui.card title="Sidang Berikutnya / Dokumen yang Perlu Diunggah">
        @php
            $tahap = $myPengajuan->status_tahap;
        @endphp
        @if($tahap === 'tahap_1_bimbingan')
            @if(!$myPengajuan->pembimbing_1_id)
                <p class="text-[13.5px] text-slate-600">Menunggu Komisi Tesis menetapkan Pembimbing 1 &amp; 2. Belum ada dokumen yang perlu diunggah.</p>
            @else
                <p class="text-[13.5px] text-slate-600 mb-2">Pembimbing sudah ditetapkan — Anda bisa mendaftar Seminar Proposal (Sempro).</p>
                <a href="{{ route('sempro.create', $myPengajuan->id) }}" class="ui-btn ui-btn-primary">Daftar Sempro Sekarang</a>
            @endif
        @elseif($tahap === 'tahap_2_sempro' && !$myPengajuan->pendaftaranSempro)
            <a href="{{ route('sempro.create', $myPengajuan->id) }}" class="ui-btn ui-btn-primary">Lengkapi Pendaftaran Sempro</a>
        @elseif($tahap === 'tahap_2_sempro')
            <x-ui.badge color="yellow">Sedang menjalani Seminar Proposal</x-ui.badge>
            <p class="text-[12.5px] text-slate-500 mt-2">Status verifikasi berkas: {{ $myPengajuan->pendaftaranSempro->status_verifikasi_admin ?? '-' }}</p>
        @elseif($tahap === 'tahap_3_semhas' && !$myPengajuan->pendaftaranSemhas)
            <a href="{{ route('semhas.create', $myPengajuan->id) }}" class="ui-btn ui-btn-primary">Lengkapi Pendaftaran Semhas</a>
        @elseif($tahap === 'tahap_3_semhas')
            <x-ui.badge color="yellow">Sedang menjalani Seminar Hasil</x-ui.badge>
            @if($myPengajuan->pendaftaranSemhas)
                <div class="mt-2 flex gap-2">
                    <x-ui.badge color="{{ $myPengajuan->pendaftaranSemhas->approval_pembimbing_1 ? 'green' : 'yellow' }}">Approval P1</x-ui.badge>
                    <x-ui.badge color="{{ $myPengajuan->pendaftaranSemhas->approval_pembimbing_2 ? 'green' : 'yellow' }}">Approval P2</x-ui.badge>
                </div>
            @endif
        @elseif($tahap === 'tahap_4_ujian' && !$myPengajuan->pendaftaranUjian)
            <a href="{{ route('ujian.create', $myPengajuan->id) }}" class="ui-btn ui-btn-primary">Lengkapi Pendaftaran Ujian Tesis</a>
        @elseif($tahap === 'tahap_4_ujian')
            <x-ui.badge color="yellow">Sedang menjalani Ujian Tesis</x-ui.badge>
            @if($myPengajuan->pendaftaranUjian)
                <div class="mt-2 flex gap-2">
                    <x-ui.badge color="{{ $myPengajuan->pendaftaranUjian->acc_tertulis_pembimbing_1 ? 'green' : 'yellow' }}">Approval P1</x-ui.badge>
                    <x-ui.badge color="{{ $myPengajuan->pendaftaranUjian->acc_tertulis_pembimbing_2 ? 'green' : 'yellow' }}">Approval P2</x-ui.badge>
                </div>
            @endif
        @elseif($tahap === 'selesai_yudisium')
            <x-ui.badge color="green">Selamat! Anda sudah Siap Yudisium 🎓</x-ui.badge>
        @endif
    </x-ui.card>

    <x-ui.card title="Riwayat Proses per Tahap" subtitle="Histori lengkap transisi status (bisa diaudit).">
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead><tr><th>Waktu</th><th>Dari</th><th>Ke</th><th>Oleh</th></tr></thead>
                <tbody>
                    @forelse($myPengajuan->stateTransitionLogs as $log)
                    <tr>
                        <td class="whitespace-nowrap">{{ $log->created_at->translatedFormat('d M Y, H:i') }}</td>
                        <td><x-ui.status-badge :status="$log->from_state" /></td>
                        <td><x-ui.status-badge :status="$log->to_state" /></td>
                        <td>{{ $log->actor_name ?? '(sistem)' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-slate-500 py-6">Belum ada histori transisi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

</div>
@endif

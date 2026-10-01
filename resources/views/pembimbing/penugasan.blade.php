<x-layouts.app title="Status Penugasan Pembimbing — SIM-TESIS FKIP UNS">

    <div class="max-w-2xl mx-auto w-full flex flex-col gap-4">

        <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-primary-800 text-[13px] font-semibold">&larr; Kembali ke Dashboard</a>

        <x-ui.hero title="Status Penugasan Pembimbing" :subtitle="($pengajuan->mahasiswa->name ?? '-') . ' · ' . ($pengajuan->mahasiswa->identifier ?? '-')" />

        <x-ui.card>
            <x-ui.info-row label="Judul Tesis">{{ $pengajuan->judul_tesis }}</x-ui.info-row>
            <x-ui.info-row label="Status Tahap"><x-ui.status-badge :status="$pengajuan->status_tahap" /></x-ui.info-row>
            <x-ui.info-row label="Pembimbing 1">
                @if($pengajuan->pembimbing1)
                    <span class="text-emerald-700 font-semibold">{{ $pengajuan->pembimbing1->name }}</span>
                @else
                    <x-ui.badge color="yellow">Belum Ditetapkan</x-ui.badge>
                @endif
            </x-ui.info-row>
            <x-ui.info-row label="Pembimbing 2">
                @if($pengajuan->pembimbing2)
                    <span class="text-emerald-700 font-semibold">{{ $pengajuan->pembimbing2->name }}</span>
                @else
                    <x-ui.badge color="yellow">Belum Ditetapkan</x-ui.badge>
                @endif
            </x-ui.info-row>
            @if($pengajuan->nomor_sk_pembimbing)
                <x-ui.info-row label="Nomor SK">{{ $pengajuan->nomor_sk_pembimbing }}</x-ui.info-row>
                <x-ui.info-row label="Tanggal SK">{{ optional($pengajuan->tanggal_sk_pembimbing)->translatedFormat('d F Y') }}</x-ui.info-row>
            @endif
        </x-ui.card>

        @if($pengajuan->pembimbing1 && $pengajuan->pembimbing2)
            <a href="{{ route('dokumen.cetak', ['kode' => 'SK-PEMBIMBING', 'id' => $pengajuan->id]) }}" target="_blank" class="ui-btn ui-btn-primary w-fit">Unduh Draft SK Pembimbing</a>
        @endif

    </div>

</x-layouts.app>

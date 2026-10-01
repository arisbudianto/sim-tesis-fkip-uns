<x-layouts.guest title="Verifikasi Dokumen — SIM-TESIS FKIP UNS">

    <h1 class="text-[17px] font-extrabold text-primary-900 mb-0.5">Verifikasi Dokumen</h1>
    <p class="text-slate-500 text-[13px] mb-4">SIM-TESIS FKIP UNS &mdash; Sistem Informasi Manajemen Tesis</p>

    @if($valid)
        <x-ui.badge color="green" class="!text-[13px] !px-3.5 !py-2 mb-4">&#10003; Dokumen Sah &amp; Terverifikasi</x-ui.badge>

        <x-ui.info-row label="Kode Dokumen">{{ $dokumen->kode_dokumen }}</x-ui.info-row>
        @if($dokumen->nomor_dokumen)
            <x-ui.info-row label="Nomor">{{ $dokumen->nomor_dokumen }}</x-ui.info-row>
        @endif
        <x-ui.info-row label="Dicetak Pada">{{ $dokumen->dicetak_at->translatedFormat('d F Y, H:i') }} WIB</x-ui.info-row>
        @if($dokumen->dicetakOleh)
            <x-ui.info-row label="Dicetak Oleh">{{ $dokumen->dicetakOleh->name }}</x-ui.info-row>
        @endif
        <x-ui.info-row label="Hash Verifikasi">
            <span class="text-[10.5px] break-all font-mono">{{ $dokumen->hash_verifikasi }}</span>
        </x-ui.info-row>
    @else
        <x-ui.badge color="red" class="!text-[13px] !px-3.5 !py-2 mb-4">&#10007; Dokumen Tidak Ditemukan</x-ui.badge>
        <p class="text-[13px] text-slate-600 leading-relaxed">
            Kode verifikasi ini tidak cocok dengan catatan sistem SIM-TESIS.
            Dokumen mungkin tidak sah, sudah dicabut, atau tautan/QR yang dipindai keliru.
        </p>
    @endif

</x-layouts.guest>

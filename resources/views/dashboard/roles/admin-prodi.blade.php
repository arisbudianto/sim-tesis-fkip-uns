<div class="flex flex-col gap-4">

    <div class="grid md:grid-cols-3 gap-4">
        <x-ui.card :title="'Antrean Verifikasi Sempro (' . $antreanVerifikasiSempro->count() . ')'">
            <div class="flex flex-col gap-2">
                @forelse($antreanVerifikasiSempro as $p)
                <div class="flex justify-between items-center py-2 border-b border-slate-100 last:border-0">
                    <div>
                        <div class="text-[13px] font-semibold text-slate-700">{{ $p->mahasiswa->name }}</div>
                        <div class="text-[11px] text-slate-500">{{ $p->mahasiswa->identifier }}</div>
                    </div>
                    <x-ui.badge color="yellow">Pending</x-ui.badge>
                </div>
                @empty
                <p class="text-slate-500 text-[13.5px]">Tidak ada antrean Sempro.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card :title="'Antrean Verifikasi Semhas (' . $antreanVerifikasiSemhas->count() . ')'">
            <div class="flex flex-col gap-2">
                @forelse($antreanVerifikasiSemhas as $p)
                <div class="flex justify-between items-center py-2 border-b border-slate-100 last:border-0">
                    <div>
                        <div class="text-[13px] font-semibold text-slate-700">{{ $p->mahasiswa->name }}</div>
                        <div class="text-[11px] text-slate-500">{{ $p->mahasiswa->identifier }}</div>
                    </div>
                    <x-ui.badge color="yellow">Pending</x-ui.badge>
                </div>
                @empty
                <p class="text-slate-500 text-[13.5px]">Tidak ada antrean Semhas.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card :title="'Antrean Verifikasi Ujian (' . ($antreanVerifikasiUjian->count() ?? 0) . ')'">
            <div class="flex flex-col gap-2">
                @forelse($antreanVerifikasiUjian ?? [] as $p)
                <div class="flex justify-between items-center py-2 border-b border-slate-100 last:border-0">
                    <div>
                        <div class="text-[13px] font-semibold text-slate-700">{{ $p->mahasiswa->name }}</div>
                        <div class="text-[11px] text-slate-500">{{ $p->mahasiswa->identifier }}</div>
                    </div>
                    <x-ui.badge color="yellow">Pending</x-ui.badge>
                </div>
                @empty
                <p class="text-slate-500 text-[13.5px]">Tidak ada antrean Ujian.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <x-ui.card title="Status Penerbitan Surat Terbaru" subtitle="15 dokumen resmi terakhir yang di-generate sistem.">
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead><tr><th>Kode Dokumen</th><th>Nomor</th><th>Dicetak Oleh</th><th>Waktu</th></tr></thead>
                <tbody>
                    @forelse($dokumenTerbaru as $d)
                    <tr>
                        <td>{{ $d->kode_dokumen }}</td>
                        <td>{{ $d->nomor_dokumen ?? '-' }}</td>
                        <td>{{ $d->dicetakOleh->name ?? '(sistem)' }}</td>
                        <td class="whitespace-nowrap">{{ $d->dicetak_at->translatedFormat('d M Y, H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-slate-500 py-6">Belum ada dokumen yang di-generate.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

</div>

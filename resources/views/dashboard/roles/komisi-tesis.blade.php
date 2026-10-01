<div class="flex flex-col gap-4">

    <div class="grid md:grid-cols-2 gap-4">
        <x-ui.card title="Kuota Bimbingan per Dosen">
            <div class="overflow-x-auto -mx-1 max-h-80 overflow-y-auto">
                <table class="ui-table">
                    <thead><tr><th>Dosen</th><th>Terpakai/Maks</th><th>Sisa</th></tr></thead>
                    <tbody>
                        @foreach($dosenKuota as $d)
                        <tr>
                            <td>{{ $d['name'] }}</td>
                            <td>{{ $d['kuota_terpakai'] }}/{{ $d['kuota_maksimum'] }}</td>
                            <td>
                                @if($d['sisa_kuota'] > 0)
                                    <x-ui.badge color="green">{{ $d['sisa_kuota'] }}</x-ui.badge>
                                @else
                                    <x-ui.badge color="red">Penuh</x-ui.badge>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <x-ui.card title="Rekap Nilai Lintas Tahap">
            <div class="flex flex-col gap-2">
                @forelse($rekapNilaiLintasTahap as $tahap => $data)
                <div class="flex justify-between items-center py-2 border-b border-slate-100 last:border-0">
                    <span class="text-[13px] font-semibold text-slate-700">{{ strtoupper($tahap) }}</span>
                    <span class="text-[13px] text-slate-500">{{ $data['jumlah'] }} sidang &middot; rata-rata {{ $data['rata_rata'] }}</span>
                </div>
                @empty
                <p class="text-slate-500 text-[13.5px]">Belum ada rekap nilai tercatat.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <x-ui.card title="Jadwal Sidang Keseluruhan (Mendatang)" subtitle="Seluruh sidang Sempro/Semhas/Ujian yang sudah terjadwal, diurutkan dari yang terdekat.">
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead><tr><th>Waktu</th><th>Tahap</th><th>Mahasiswa</th><th>Ruang/Zoom</th></tr></thead>
                <tbody>
                    @forelse($jadwalSidangMendatang as $s)
                    <tr>
                        <td class="whitespace-nowrap">{{ $s->waktu_mulai }}</td>
                        <td>{{ strtoupper($s->tahap_sidang) }}</td>
                        <td>{{ $s->pengajuanTesis->mahasiswa->name ?? '-' }}</td>
                        <td>{{ $s->ruangan ?? $s->link_zoom ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-slate-500 py-6">Belum ada jadwal sidang mendatang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

</div>

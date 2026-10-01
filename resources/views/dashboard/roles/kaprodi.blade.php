<div class="flex flex-col gap-4">

    <div class="grid grid-cols-2 gap-4">
        <x-ui.card>
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tingkat Kelulusan</div>
            <div class="text-[28px] font-extrabold text-emerald-700 mt-1">{{ $tingkatKelulusan }}%</div>
            <p class="text-[11.5px] text-slate-500 mt-1">Dari seluruh pengajuan tesis tercatat.</p>
        </x-ui.card>
        <x-ui.card>
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rata-rata Capaian Masa Studi</div>
            <div class="text-[28px] font-extrabold text-primary-900 mt-1">{{ $capaianMasaStudiBulan ?? '-' }} <span class="text-[14px] font-semibold text-slate-400">bulan</span></div>
            <p class="text-[11.5px] text-slate-500 mt-1">Dihitung dari mahasiswa yang sudah yudisium.</p>
        </x-ui.card>
    </div>

    <x-ui.card title="Legalitas Dewan Penguji — Menunggu Pengesahan Anda" subtitle="Revisi yang sudah disetujui 4/4 penguji, tinggal menunggu tanda tangan (QR TTE) Kaprodi untuk membuka gateway yudisium.">
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead><tr><th>Mahasiswa</th><th>Tahap Sidang</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($legalitasPending as $revisi)
                    <tr>
                        <td>{{ $revisi->sidang->pengajuanTesis->mahasiswa->name ?? '-' }}</td>
                        <td>{{ strtoupper($revisi->sidang->tahap_sidang ?? '-') }}</td>
                        <td><x-ui.badge color="green">4/4 ACC Penguji</x-ui.badge></td>
                        <td>
                            <form action="{{ route('revisi.pengesahanKaprodi', $revisi->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="ui-btn ui-btn-sm ui-btn-success">Sahkan Sekarang</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-slate-500 py-6">Tidak ada revisi yang menunggu pengesahan Anda saat ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>


    @isset($myBimbingan)
        @include('dashboard.roles.dosen')
    @endisset
</div>

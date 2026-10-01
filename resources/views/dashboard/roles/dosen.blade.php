<div class="flex flex-col gap-4">

    <x-ui.card title="Mahasiswa Bimbingan Saya" subtitle="Sebagai Pembimbing 1 atau Pembimbing 2.">
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead><tr><th>Mahasiswa</th><th>Judul Tesis</th><th>Status Tahap</th><th>Approval Pending</th></tr></thead>
                <tbody>
                    @forelse($myBimbingan as $p)
                    <tr>
                        <td><strong>{{ $p->mahasiswa->name ?? '-' }}</strong><br><span class="text-[12px] text-slate-500">{{ $p->mahasiswa->identifier ?? '-' }}</span></td>
                        <td>{{ \Illuminate\Support\Str::limit($p->judul_tesis, 45) }}</td>
                        <td><x-ui.status-badge :status="$p->status_tahap" /></td>
                        <td>
                            @php
                                $slot = $p->pembimbing_1_id === Auth::id() ? 1 : 2;
                                $pendingSemhas = $p->pendaftaranSemhas && !$p->pendaftaranSemhas->{"approval_pembimbing_$slot"};
                                $pendingUjian = $p->pendaftaranUjian && !$p->pendaftaranUjian->{"acc_tertulis_pembimbing_$slot"};
                            @endphp
                            @if($pendingSemhas)
                                <x-ui.badge color="yellow">Naskah Semhas belum di-ACC</x-ui.badge>
                            @elseif($pendingUjian)
                                <x-ui.badge color="yellow">Ujian Tesis belum di-ACC</x-ui.badge>
                            @else
                                <span class="text-slate-400 text-[12px]">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-slate-500 py-6">
                        <div class="flex flex-col items-center gap-1">
                            <span>Anda belum membimbing mahasiswa mana pun.</span>
                            <span class="text-[12px]">Kuota bimbingan akan muncul setelah Komisi Tesis menetapkan Anda sebagai pembimbing.</span>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <x-ui.card title="Tugas Sidang Saya (Dewan Penguji)" subtitle="Sidang 30 hari terakhir & mendatang. Klik tombol untuk mengisi penilaian.">
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead><tr><th>Mahasiswa</th><th>Tahap</th><th>Jadwal</th><th>Peran</th><th>Status Nilai</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($myTugasPenguji as $ps)
                    <tr>
                        <td>{{ $ps->sidang->pengajuanTesis->mahasiswa->name ?? '-' }}</td>
                        <td><x-ui.badge color="blue">{{ strtoupper($ps->sidang->tahap_sidang) }}</x-ui.badge></td>
                        <td class="whitespace-nowrap text-[13px]">{{ optional($ps->sidang->waktu_mulai)->format('d/m/Y H:i') }}</td>
                        <td class="text-[13px]">{{ str_replace('_', ' ', $ps->peran_penguji) }}</td>
                        <td>
                            @if($ps->nilai_total_angka !== null)
                                <x-ui.badge color="green">Sudah Dinilai ({{ number_format($ps->nilai_total_angka, 1) }})</x-ui.badge>
                            @else
                                <x-ui.badge color="yellow">Belum Dinilai</x-ui.badge>
                            @endif
                        </td>
                        <td>
                            @if($ps->nilai_total_angka === null)
                                <a href="{{ route('sidang.penilaian', $ps->sidang_id) }}" class="inline-flex items-center gap-1 rounded-md bg-primary-600 px-2.5 py-1 text-[12px] font-semibold text-white hover:bg-primary-700">
                                    Isi Nilai
                                </a>
                            @else
                                <a href="{{ route('sidang.penilaian', $ps->sidang_id) }}" class="text-[12px] text-primary-600 hover:underline">Lihat</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-slate-500 py-6">
                        <div class="flex flex-col items-center gap-1">
                            <span>Tidak ada penugasan sebagai penguji dalam 30 hari terakhir/mendatang.</span>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <x-ui.card title="Revisi yang Menunggu ACC Saya" subtitle="Matriks perbaikan naskah yang belum Anda setujui.">
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead><tr><th>Mahasiswa</th><th>Tahap Sidang</th><th>Uraian Perbaikan</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($myRevisiPending ?? [] as $rp)
                    <tr>
                        <td>{{ $rp->revisiDokumen->sidang->pengajuanTesis->mahasiswa->name ?? '-' }}</td>
                        <td>{{ strtoupper($rp->revisiDokumen->sidang->tahap_sidang ?? '-') }}</td>
                        <td class="text-[13px]">{{ \Illuminate\Support\Str::limit($rp->uraian_hasil_perbaikan, 60) }}</td>
                        <td>
                            <a href="{{ route('revisi.index', $rp->revisiDokumen->sidang_id) }}" class="inline-flex items-center gap-1 rounded-md bg-amber-500 px-2.5 py-1 text-[12px] font-semibold text-white hover:bg-amber-600">
                                Review & ACC
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-slate-500 py-6">Tidak ada revisi yang menunggu persetujuan Anda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

</div>

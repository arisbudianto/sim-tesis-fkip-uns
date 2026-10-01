@php use App\Http\Controllers\BerkasController; @endphp
<div class="flex flex-col gap-4">

    @php
        $role = Auth::user()->role ?? null;
        // Kebocoran data yang sudah diperbaiki: baris tabel H-14 di bawah
        // dulu menampilkan SEMUA mahasiswa ke siapa pun yang login. Sekarang
        // di-filter dulu sesuai lingkup akses role sebelum di-loop.
        $baris = match(true) {
            $role === 'mahasiswa' => collect($myPengajuan ? [$myPengajuan] : []),
            $role === 'dosen' => $myBimbingan ?? collect(),
            default => $pengajuans, // pengendali akademik: lihat semua
        };
        $fokusTahap = $fokusTahap ?? null; // sempro | semhas | ujian | null=semua
    @endphp

    @if((!$fokusTahap || $fokusTahap === 'sempro') && Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi']))
    <x-ui.card title="Mahasiswa Seminar Proposal" subtitle="Satu daftar: menunggu verifikasi, menunggu jadwal, atau sudah dijadwalkan.">
        <div class="overflow-x-auto -mx-1" x-data="{ editId: null }">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Mahasiswa</th><th>Judul</th><th>Jadwal & Penguji</th><th>Status</th><th>Dokumen</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        @if($p->status_tahap === 'tahap_2_sempro')
                        @php $ps = $p->pendaftaranSempro; @endphp
                        <tr>
                            @php
                                $sidangSempro = $p->aktivitasSidangs->firstWhere('tahap_sidang', 'sempro');
                                $pengujiLengkap = $sidangSempro && $sidangSempro->pengujiSidangs->count() >= 3;
                            @endphp
                            <td><strong>{{ $p->mahasiswa->name }}</strong><br>{{ $p->mahasiswa->identifier }}</td>
                            <td class="text-[12.5px]">{{ \Illuminate\Support\Str::limit($p->judul_tesis, 50) }}</td>
                            <td class="text-[12.5px]">
                                @if($sidangSempro)
                                    <div class="font-semibold">{{ optional($sidangSempro->waktu_mulai)->translatedFormat('d F Y, H:i') ?? $sidangSempro->waktu_mulai }}</div>
                                    <div class="text-slate-500 text-[11px]">Ruang: {{ $sidangSempro->ruangan ?? 'TBA' }}</div>
                                    <div class="text-[11px] text-slate-600 mt-1">
                                        @foreach($sidangSempro->pengujiSidangs as $pj)
                                            {{ $pj->dosen->name ?? '-' }} <em>({{ str_replace('_',' ',$pj->peran_penguji) }})</em>@if(!$loop->last)<br>@endif
                                        @endforeach
                                    </div>
                                @elseif($ps && $ps->jadwal_usulan_sidang)
                                    Usulan: {{ \Carbon\Carbon::parse($ps->jadwal_usulan_sidang)->translatedFormat('d F Y, H:i') }}
                                    <div class="text-[11px] text-slate-400">Belum dijadwalkan Komisi</div>
                                @else
                                    <span class="text-slate-400">Belum ada pendaftaran Sempro</span>
                                @endif
                            </td>
                            <td>
                                @if($sidangSempro)
                                    <x-ui.badge color="green">Sudah dijadwalkan</x-ui.badge>
                                @elseif($ps && $ps->status_verifikasi_admin === 'verified')
                                    <x-ui.badge color="yellow">Menunggu jadwal</x-ui.badge>
                                @elseif($ps->status_verifikasi_admin === 'rejected')
                                    <x-ui.badge color="red">Ditolak</x-ui.badge>
                                @else
                                    <x-ui.badge color="yellow">Menunggu Verifikasi</x-ui.badge>
                                @endif
                            </td>
                            <td class="space-y-1">
                                @if($ps && $ps->form_fpt_ti_01_url)
                                    <a href="{{ BerkasController::url($ps->form_fpt_ti_01_url) }}" target="_blank" class="ui-btn ui-btn-sm ui-btn-primary">Form FPT-TI-01</a>
                                @else
                                    <span class="text-rose-600 text-[12px] block">Form belum diunggah</span>
                                @endif
                                @if($ps && $ps->status_verifikasi_admin === 'verified' && $pengujiLengkap)
                                    <a href="{{ route('dokumen.cetak', ['kode' => 'SURAT-TUGAS-SEMPRO', 'id' => $sidangSempro->id]) }}" class="ui-btn ui-btn-sm ui-btn-outline" target="_blank">Surat Tugas</a>
                                    <a href="{{ route('dokumen.cetak', ['kode' => 'UNDANGAN-SEMPRO', 'id' => $sidangSempro->id]) }}" class="ui-btn ui-btn-sm ui-btn-outline" target="_blank">Undangan</a>
                                @elseif($ps && $ps->status_verifikasi_admin === 'verified')
                                    <span class="text-[11px] text-slate-400 block">Surat tugas menunggu plotting</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1.5">
                                    @if($ps && $ps->status_verifikasi_admin === 'pending')
                                        <button type="button" class="ui-btn ui-btn-sm ui-btn-outline"
                                            @click="editId = editId === '{{ $ps->id }}' ? null : '{{ $ps->id }}'">
                                            Edit
                                        </button>
                                        <form action="{{ route('sempro.verifikasi', $ps->id) }}" method="POST">
                                            @csrf<input type="hidden" name="status_verifikasi_admin" value="verified">
                                            <button type="submit" class="ui-btn ui-btn-sm ui-btn-success">Setujui</button>
                                        </form>
                                        <form action="{{ route('sempro.verifikasi', $ps->id) }}" method="POST">
                                            @csrf<input type="hidden" name="status_verifikasi_admin" value="rejected">
                                            <button type="submit" class="ui-btn ui-btn-sm ui-btn-danger">Tolak</button>
                                        </form>
                                    @else
                                        @if(!empty($sidangSempro))
                                            <a href="{{ route('sidang.penilaian', $sidangSempro->id) }}" class="ui-btn ui-btn-sm ui-btn-primary">Rekap / Nilai</a>
                                        @else
                                            <span class="text-[12px] text-slate-400">—</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr x-show="editId === '{{ $ps->id }}'" x-cloak>
                            <td colspan="5" class="bg-slate-50">
                                <form action="{{ route('sempro.update.post', $ps->id) }}" method="POST" class="p-3 flex flex-col gap-3">
                                    @csrf
                                    @php
                                        $sidangSempro = $p->aktivitasSidangs->firstWhere('tahap_sidang', 'sempro');
                                        $ketuaId = optional(optional($sidangSempro)->pengujiSidangs)->firstWhere('peran_penguji', 'ketua_penguji')->dosen_id ?? null;
                                        $sekreId = optional(optional($sidangSempro)->pengujiSidangs)->firstWhere('peran_penguji', 'sekretaris_penguji')->dosen_id ?? null;
                                    @endphp
                                    <p class="text-[13px] font-semibold text-primary-900">Edit Jadwal & Penguji — {{ $p->mahasiswa->name }}</p>
                                    <p class="text-[12px] text-slate-500 -mt-2">Hanya tanggal sidang dan penguji eksternal yang bisa diubah. Pembimbing utama & pendamping tetap.</p>
                                    <div class="grid md:grid-cols-2 gap-3">
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Hari / Tanggal Sidang <span class="text-rose-600">*</span></label>
                                            <input type="datetime-local" name="jadwal_usulan_sidang" class="ui-input" required
                                                value="{{ \Carbon\Carbon::parse($ps->jadwal_usulan_sidang)->format('Y-m-d\TH:i') }}">
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Pembimbing Utama</label>
                                            <input type="text" class="ui-input bg-slate-100" value="{{ $p->pembimbing1->name ?? 'Belum ditetapkan' }}" disabled>
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Pembimbing Pendamping</label>
                                            <input type="text" class="ui-input bg-slate-100" value="{{ $p->pembimbing2->name ?? 'Belum ditetapkan' }}" disabled>
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Ketua Penguji</label>
                                            <select name="ketua_penguji_id" class="ui-input">
                                                <option value="">— Pilih dosen —</option>
                                                @foreach(($dosens ?? collect()) as $d)
                                                    @if($d->id !== $p->pembimbing_1_id && $d->id !== $p->pembimbing_2_id)
                                                        <option value="{{ $d->id }}" @selected($ketuaId === $d->id)>{{ $d->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Sekretaris Penguji</label>
                                            <select name="sekretaris_penguji_id" class="ui-input">
                                                <option value="">— Pilih dosen —</option>
                                                @foreach(($dosens ?? collect()) as $d)
                                                    @if($d->id !== $p->pembimbing_1_id && $d->id !== $p->pembimbing_2_id)
                                                        <option value="{{ $d->id }}" @selected($sekreId === $d->id)>{{ $d->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @error('jadwal_usulan_sidang')
                                        <p class="text-[12px] text-rose-600">{{ $message }}</p>
                                    @enderror
                                    @error('ketua_penguji_id')
                                        <p class="text-[12px] text-rose-600">{{ $message }}</p>
                                    @enderror
                                    <div class="flex gap-2">
                                        <button type="submit" class="ui-btn ui-btn-primary">Simpan Perubahan</button>
                                        <button type="button" class="ui-btn ui-btn-ghost" @click="editId = null">Batal</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @endif
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
    @endif

    @if((!$fokusTahap || in_array($fokusTahap, ['semhas', 'ujian'], true)) && Auth::check() && Auth::user()->role === 'dosen')
    <x-ui.card title="Approval Naskah Semhas — Bimbingan Anda" subtitle="Naskah Bab I-V wajib disetujui Pembimbing 1 & 2 sebelum Admin Prodi bisa memverifikasi pendaftaran Semhas.">
        @php
            $needApproval = $pengajuans->filter(function ($p) {
                if (!$p->pendaftaranSemhas) return false;
                $isPembimbing1 = $p->pembimbing_1_id === Auth::id() && !$p->pendaftaranSemhas->approval_pembimbing_1;
                $isPembimbing2 = $p->pembimbing_2_id === Auth::id() && !$p->pendaftaranSemhas->approval_pembimbing_2;
                return $isPembimbing1 || $isPembimbing2;
            });
        @endphp
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead><tr><th>Mahasiswa</th><th>Naskah Bab I-V</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($needApproval as $p)
                    <tr>
                        <td><strong>{{ $p->mahasiswa->name }}</strong><br>{{ $p->mahasiswa->identifier }}</td>
                        <td><a href="{{ $p->pendaftaranSemhas->naskah_bab_1_5_url }}" target="_blank" class="ui-btn ui-btn-sm ui-btn-outline">Lihat Naskah</a></td>
                        <td>
                            <form action="{{ route('semhas.approveNaskah', $p->pendaftaranSemhas->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="ui-btn ui-btn-sm ui-btn-success">Setujui Naskah</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-slate-500 py-6">Tidak ada naskah Semhas yang menunggu persetujuan Anda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <x-ui.card title="Persetujuan Tertulis Ujian Tesis — Bimbingan Anda" subtitle="Persetujuan tertulis digital Pembimbing 1 & 2 wajib diberikan sebelum Komisi Tesis bisa melakukan plotting jadwal & dewan penguji.">
        @php
            $needAccUjian = $pengajuans->filter(function ($p) {
                if (!$p->pendaftaranUjian) return false;
                $isPembimbing1 = $p->pembimbing_1_id === Auth::id() && !$p->pendaftaranUjian->acc_tertulis_pembimbing_1;
                $isPembimbing2 = $p->pembimbing_2_id === Auth::id() && !$p->pendaftaranUjian->acc_tertulis_pembimbing_2;
                return $isPembimbing1 || $isPembimbing2;
            });
        @endphp
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead><tr><th>Mahasiswa</th><th>Naskah Tesis Lengkap</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($needAccUjian as $p)
                    <tr>
                        <td><strong>{{ $p->mahasiswa->name }}</strong><br>{{ $p->mahasiswa->identifier }}</td>
                        <td><a href="{{ $p->pendaftaranUjian->naskah_tesis_lengkap_url }}" target="_blank" class="ui-btn ui-btn-sm ui-btn-outline">Lihat Naskah</a></td>
                        <td>
                            <form action="{{ route('ujian.accPembimbing', $p->pendaftaranUjian->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="ui-btn ui-btn-sm ui-btn-success">Berikan Persetujuan Tertulis</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-slate-500 py-6">Tidak ada pendaftaran Ujian Tesis yang menunggu persetujuan Anda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
    @endif

    @if((!$fokusTahap || $fokusTahap === 'semhas') && Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi']))
    <x-ui.card title="Mahasiswa Seminar Hasil" subtitle="Satu daftar: verifikasi, jadwal, penguji, dokumen, dan nilai.">
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Mahasiswa</th><th>Judul</th><th>Jadwal & Penguji</th><th>Status</th><th>Dokumen</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        @if($p->status_tahap === 'tahap_3_semhas')
                        @php
                            $ph = $p->pendaftaranSemhas;
                            $sidangSemhas = $p->aktivitasSidangs->firstWhere('tahap_sidang', 'semhas');
                        @endphp
                        <tr>
                            <td><strong>{{ $p->mahasiswa->name }}</strong><br>{{ $p->mahasiswa->identifier }}</td>
                            <td class="text-[12.5px]">{{ \Illuminate\Support\Str::limit($p->judul_tesis, 50) }}</td>
                            <td class="text-[12.5px]">
                                @if($sidangSemhas)
                                    <div class="font-semibold">{{ optional($sidangSemhas->waktu_mulai)->translatedFormat('d F Y, H:i') ?? $sidangSemhas->waktu_mulai }}</div>
                                    <div class="text-slate-500 text-[11px]">Ruang: {{ $sidangSemhas->ruangan ?? 'TBA' }}</div>
                                    @foreach($sidangSemhas->pengujiSidangs as $pj)
                                        <div class="text-[11px]">{{ $pj->dosen->name ?? '-' }} <em>({{ str_replace('_',' ',$pj->peran_penguji) }})</em></div>
                                    @endforeach
                                @elseif($ph && $ph->jadwal_usulan_sidang)
                                    Usulan: {{ \Carbon\Carbon::parse($ph->jadwal_usulan_sidang)->translatedFormat('d F Y, H:i') }}
                                @else
                                    <span class="text-slate-400">Belum ada pendaftaran</span>
                                @endif
                            </td>
                            <td>
                                @if($sidangSemhas)
                                    <x-ui.badge color="green">Sudah dijadwalkan</x-ui.badge>
                                @elseif($ph && $ph->status_verifikasi_admin === 'verified')
                                    <x-ui.badge color="yellow">Menunggu jadwal</x-ui.badge>
                                @elseif($ph && $ph->status_verifikasi_admin === 'pending')
                                    <x-ui.badge color="yellow">Menunggu Verifikasi</x-ui.badge>
                                @else
                                    <x-ui.badge color="yellow">Tahap Semhas</x-ui.badge>
                                @endif
                            </td>
                            <td class="space-y-1">
                                @if($ph && $ph->form_fpt_sh_01_url)
                                    <a href="{{ BerkasController::url($ph->form_fpt_sh_01_url) }}" target="_blank" class="ui-btn ui-btn-sm ui-btn-primary">Permohonan</a>
                                @endif
                                @if($sidangSemhas)
                                    <a href="{{ route('dokumen.cetak', ['kode' => 'SURAT-TUGAS-SEMHAS', 'id' => $sidangSemhas->id]) }}" class="ui-btn ui-btn-sm ui-btn-outline" target="_blank">Surat Tugas</a>
                                @endif
                            </td>
                            <td>
                                @if($ph && $ph->status_verifikasi_admin === 'pending')
                                    <div class="flex flex-wrap gap-1.5">
                                        <form action="{{ route('semhas.verifikasi', $ph->id) }}" method="POST">
                                            @csrf<input type="hidden" name="status_verifikasi_admin" value="verified">
                                            <button type="submit" class="ui-btn ui-btn-sm ui-btn-success">Setujui</button>
                                        </form>
                                        <form action="{{ route('semhas.verifikasi', $ph->id) }}" method="POST">
                                            @csrf<input type="hidden" name="status_verifikasi_admin" value="rejected">
                                            <button type="submit" class="ui-btn ui-btn-sm ui-btn-danger">Tolak</button>
                                        </form>
                                    </div>
                                @elseif($sidangSemhas)
                                    <a href="{{ route('sidang.penilaian', $sidangSemhas->id) }}" class="ui-btn ui-btn-sm ui-btn-primary">Rekap / Nilai</a>
                                @else
                                    <span class="text-[12px] text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @empty
                    <tr><td colspan="6" class="text-center text-slate-500 py-6">Belum ada mahasiswa Seminar Hasil.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
    @endif

    @if((!$fokusTahap || $fokusTahap === 'ujian') && Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi']))
    <x-ui.card title="Mahasiswa Ujian Tesis" subtitle="Satu daftar: pendaftaran, jadwal, penguji, dan nilai.">
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Mahasiswa</th><th>Judul</th><th>Jadwal & Penguji</th><th>Status</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        @if($p->status_tahap === 'tahap_4_ujian')
                        @php
                            $pu = $p->pendaftaranUjian;
                            $sidangUjian = $p->aktivitasSidangs->firstWhere('tahap_sidang', 'ujian');
                        @endphp
                        <tr>
                            <td><strong>{{ $p->mahasiswa->name }}</strong><br>{{ $p->mahasiswa->identifier }}</td>
                            <td class="text-[12.5px]">{{ \Illuminate\Support\Str::limit($p->judul_tesis, 50) }}</td>
                            <td class="text-[12.5px]">
                                @if($sidangUjian)
                                    <div class="font-semibold">{{ optional($sidangUjian->waktu_mulai)->translatedFormat('d F Y, H:i') ?? $sidangUjian->waktu_mulai }}</div>
                                    <div class="text-slate-500 text-[11px]">Ruang: {{ $sidangUjian->ruangan ?? 'TBA' }}</div>
                                    @foreach($sidangUjian->pengujiSidangs as $pj)
                                        <div class="text-[11px]">{{ $pj->dosen->name ?? '-' }} <em>({{ str_replace('_',' ',$pj->peran_penguji) }})</em></div>
                                    @endforeach
                                @elseif($pu && $pu->jadwal_usulan_sidang)
                                    Usulan: {{ \Carbon\Carbon::parse($pu->jadwal_usulan_sidang)->translatedFormat('d F Y, H:i') }}
                                @else
                                    <span class="text-slate-400">Belum dijadwalkan</span>
                                @endif
                            </td>
                            <td>
                                @if($sidangUjian)
                                    <x-ui.badge color="green">Sudah dijadwalkan</x-ui.badge>
                                @elseif($pu)
                                    <x-ui.badge color="yellow">Terdaftar</x-ui.badge>
                                @else
                                    <x-ui.badge color="yellow">Tahap Ujian</x-ui.badge>
                                @endif
                            </td>
                            <td>
                                @if($sidangUjian)
                                    <a href="{{ route('sidang.penilaian', $sidangUjian->id) }}" class="ui-btn ui-btn-sm ui-btn-primary">Rekap / Nilai</a>
                                @else
                                    <span class="text-[12px] text-slate-400">Menunggu plotting</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @empty
                    <tr><td colspan="5" class="text-center text-slate-500 py-6">Belum ada mahasiswa Ujian Tesis.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
    @endif

</div>

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

        // Sama seperti di tab Sidang (form Plotting Jadwal & Dewan Penguji
        // Baru): Komisi Tesis & Kaprodi boleh jadi Ketua/Sekretaris Penguji
        // di SEMUA tahap, bukan cuma dosen biasa. $dosens (dipakai untuk
        // Pembimbing Utama/Pendamping) sengaja TIDAK diubah supaya Komisi
        // Tesis/Kaprodi tidak ikut muncul sebagai calon pembimbing tesis.
        $calonPenguji = \App\Models\User::whereIn('role', ['dosen', 'komisi_tesis', 'kaprodi'])
            ->orderBy('name')
            ->get();
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
                                    <div class="text-slate-500 text-[11px]">
                                        Ruang:
                                        @if(filled($sidangSempro->ruangan))
                                            {{ $sidangSempro->ruangan }}
                                        @elseif(filled($sidangSempro->link_zoom))
                                            Daring (<a href="{{ $sidangSempro->link_zoom }}" target="_blank" class="underline">Link Zoom</a>)
                                        @else
                                            TBA
                                        @endif
                                    </div>
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
                                <div class="text-[12px]">
                                    Naskah Lengkap:
                                    @if($ps && $ps->naskah_proposal_url)
                                        <a href="{{ BerkasController::url($ps->naskah_proposal_url) }}" target="_blank" class="text-primary-700 underline">Lihat</a>
                                    @else
                                        <span class="text-slate-400">(belum diunggah)</span>
                                    @endif
                                </div>
                                @if($ps && $ps->status_verifikasi_admin === 'verified' && $pengujiLengkap)
                                    <a href="{{ route('dokumen.cetak', ['kode' => 'SURAT-TUGAS-SEMPRO', 'id' => $sidangSempro->id]) }}" class="ui-btn ui-btn-sm ui-btn-outline" target="_blank">Surat Tugas</a>
                                    <a href="{{ route('dokumen.cetak', ['kode' => 'UNDANGAN-SEMPRO', 'id' => $sidangSempro->id]) }}" class="ui-btn ui-btn-sm ui-btn-outline" target="_blank">Undangan</a>
                                @elseif($ps && $ps->status_verifikasi_admin === 'verified')
                                    <span class="text-[11px] text-slate-400 block">Surat tugas menunggu plotting</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1.5">
                                    @if($ps)
                                        {{-- Edit judul/data proposal & jadwal selalu tersedia selama belum
                                        selesai sidang — tidak lagi dibatasi hanya saat status pending,
                                        supaya typo judul dkk masih bisa diperbaiki walau pendaftaran
                                        sudah diverifikasi. --}}
                                        <button type="button" class="ui-btn ui-btn-sm ui-btn-outline"
                                            @click="editId = editId === '{{ $ps->id }}' ? null : '{{ $ps->id }}'">
                                            Edit
                                        </button>
                                    @endif
                                    @if($ps && $ps->status_verifikasi_admin === 'pending')
                                        <form action="{{ route('sempro.verifikasi', $ps->id) }}" method="POST">
                                            @csrf<input type="hidden" name="status_verifikasi_admin" value="verified">
                                            <button type="submit" class="ui-btn ui-btn-sm ui-btn-success">Setujui</button>
                                        </form>
                                        <form action="{{ route('sempro.verifikasi', $ps->id) }}" method="POST">
                                            @csrf<input type="hidden" name="status_verifikasi_admin" value="rejected">
                                            <button type="submit" class="ui-btn ui-btn-sm ui-btn-danger">Tolak</button>
                                        </form>
                                    @elseif(!empty($sidangSempro))
                                        <a href="{{ route('sidang.penilaian', $sidangSempro->id) }}" class="ui-btn ui-btn-sm ui-btn-primary">Rekap / Nilai</a>
                                    @elseif(!$ps)
                                        <span class="text-[12px] text-slate-400">—</span>
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
                                    <p class="text-[13px] font-semibold text-primary-900">Edit Data Sempro — {{ $p->mahasiswa->name }}</p>
                                    <p class="text-[12px] text-slate-500 -mt-2">Judul proposal, bidang fokus, abstrak, tanggal sidang, dan penguji eksternal bisa diubah di sini. Pembimbing utama & pendamping tetap (ubah lewat menu Pengajuan kalau perlu).</p>
                                    <div class="ui-field !mb-0">
                                        <label class="ui-label">Naskah Lengkap</label>
                                        @if($ps && $ps->naskah_proposal_url)
                                            <p class="text-[12px] text-slate-600 !mt-0">
                                                <a href="{{ BerkasController::url($ps->naskah_proposal_url) }}" target="_blank" class="text-primary-700 underline font-semibold">Lihat Naskah</a>
                                                — isi link di bawah untuk mengganti.
                                            </p>
                                        @else
                                            <p class="text-[12px] text-rose-600 !mt-0">(belum diunggah)</p>
                                        @endif
                                        <input type="url" name="naskah_proposal_url" class="ui-input"
                                            placeholder="https://drive.google.com/... atau link cloud lainnya"
                                            value="{{ \Illuminate\Support\Str::startsWith($ps->naskah_proposal_url ?? '', ['http://', 'https://']) ? $ps->naskah_proposal_url : '' }}">
                                        <p class="text-[11px] text-slate-400 !mt-1">Tempel link Google Drive/cloud lain yang bisa diakses publik (bukan unggah file — menghindari batas ukuran upload di server). Kosongkan untuk tetap pakai naskah yang sudah ada.</p>
                                        @error('naskah_proposal_url')
                                            <p class="text-[12px] text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="ui-field !mb-0">
                                        <label class="ui-label">Judul Proposal <span class="text-rose-600">*</span></label>
                                        <textarea name="judul_tesis" class="ui-input" rows="2" maxlength="500" required>{{ $p->judul_tesis }}</textarea>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-3">
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Bidang Fokus <span class="text-rose-600">*</span></label>
                                            <input type="text" name="bidang_fokus" class="ui-input" required value="{{ $p->bidang_fokus }}">
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Hari / Tanggal Sidang <span class="text-rose-600">*</span></label>
                                            <input type="datetime-local" name="jadwal_usulan_sidang" class="ui-input" required
                                                value="{{ \Carbon\Carbon::parse($ps->jadwal_usulan_sidang)->format('Y-m-d\TH:i') }}">
                                        </div>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-3">
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Ruangan (kosongkan kalau daring)</label>
                                            <input type="text" name="ruangan" class="ui-input" placeholder="Ruang Sidang FKIP"
                                                value="{{ $sidangSempro->ruangan ?? '' }}">
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Link Zoom (kalau daring)</label>
                                            <input type="text" name="link_zoom" class="ui-input" placeholder="https://zoom.us/j/..."
                                                value="{{ $sidangSempro->link_zoom ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="ui-field !mb-0">
                                        <label class="ui-label">Abstrak Rencana</label>
                                        <textarea name="abstrak_rencana" class="ui-input" rows="3">{{ $p->abstrak_rencana }}</textarea>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-3">
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
                                                <option value="">— Pilih Dosen/Komisi Tesis/Kaprodi —</option>
                                                @foreach($calonPenguji as $d)
                                                    @if($d->id !== $p->pembimbing_1_id && $d->id !== $p->pembimbing_2_id)
                                                        <option value="{{ $d->id }}" @selected($ketuaId === $d->id)>{{ $d->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Sekretaris Penguji</label>
                                            <select name="sekretaris_penguji_id" class="ui-input">
                                                <option value="">— Pilih Dosen/Komisi Tesis/Kaprodi —</option>
                                                @foreach($calonPenguji as $d)
                                                    @if($d->id !== $p->pembimbing_1_id && $d->id !== $p->pembimbing_2_id)
                                                        <option value="{{ $d->id }}" @selected($sekreId === $d->id)>{{ $d->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @error('judul_tesis')
                                        <p class="text-[12px] text-rose-600">{{ $message }}</p>
                                    @enderror
                                    @error('bidang_fokus')
                                        <p class="text-[12px] text-rose-600">{{ $message }}</p>
                                    @enderror
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

    {{-- Sebelumnya dosen yang jadi dewan penguji TIDAK melihat menu input
    nilai sama sekali kalau membuka tab per-tahap (Tahap 2/3/4) langsung —
    kartu "Tugas Sidang Saya" cuma ada di halaman Ringkasan (dashboard.roles.dosen).
    Ditambahkan di sini juga, difilter sesuai tahap yang sedang dibuka, supaya
    dosen tetap bisa isi nilai dari tab manapun. $myTugasPenguji sudah otomatis
    tersedia di sini (ikut terbawa dari DashboardController lewat @include). --}}
    @if(Auth::check() && Auth::user()->role === 'dosen')
        @php
            $tugasPengujiTahapIni = ($myTugasPenguji ?? collect())
                ->when($fokusTahap, fn ($q) => $q->where('sidang.tahap_sidang', $fokusTahap));
        @endphp
        @if($tugasPengujiTahapIni->isNotEmpty())
        <x-ui.card title="Tugas Menguji Anda" subtitle="Sidang yang Anda tugaskan sebagai dewan penguji. Klik Isi Nilai untuk membuka form penilaian.">
            <div class="overflow-x-auto -mx-1">
                <table class="ui-table">
                    <thead><tr><th>Mahasiswa</th><th>Tahap</th><th>Jadwal</th><th>Peran</th><th>Status Nilai</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @foreach($tugasPengujiTahapIni as $ps)
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
                                <a href="{{ route('sidang.penilaian', $ps->sidang_id) }}" class="ui-btn ui-btn-sm ui-btn-primary">
                                    {{ $ps->nilai_total_angka === null ? 'Isi Nilai' : 'Lihat' }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
        @endif
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
        <div class="overflow-x-auto -mx-1" x-data="{ editId: null }">
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
                                    <div class="text-slate-500 text-[11px]">
                                        Ruang:
                                        @if(filled($sidangSemhas->ruangan))
                                            {{ $sidangSemhas->ruangan }}
                                        @elseif(filled($sidangSemhas->link_zoom))
                                            Daring (<a href="{{ $sidangSemhas->link_zoom }}" target="_blank" class="underline">Link Zoom</a>)
                                        @else
                                            TBA
                                        @endif
                                    </div>
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
                                <div class="text-[12px]">
                                    Naskah Lengkap:
                                    @if($ph && $ph->naskah_bab_1_5_url)
                                        <a href="{{ BerkasController::url($ph->naskah_bab_1_5_url) }}" target="_blank" class="text-primary-700 underline">Lihat</a>
                                    @else
                                        <span class="text-slate-400">(belum diunggah)</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1.5">
                                    @if($ph)
                                        <button type="button" class="ui-btn ui-btn-sm ui-btn-outline"
                                            @click="editId = editId === '{{ $ph->id }}' ? null : '{{ $ph->id }}'">
                                            Edit
                                        </button>
                                    @endif
                                    @if($ph && $ph->status_verifikasi_admin === 'pending')
                                        <form action="{{ route('semhas.verifikasi', $ph->id) }}" method="POST">
                                            @csrf<input type="hidden" name="status_verifikasi_admin" value="verified">
                                            <button type="submit" class="ui-btn ui-btn-sm ui-btn-success">Setujui</button>
                                        </form>
                                        <form action="{{ route('semhas.verifikasi', $ph->id) }}" method="POST">
                                            @csrf<input type="hidden" name="status_verifikasi_admin" value="rejected">
                                            <button type="submit" class="ui-btn ui-btn-sm ui-btn-danger">Tolak</button>
                                        </form>
                                    @elseif($sidangSemhas)
                                        <a href="{{ route('sidang.penilaian', $sidangSemhas->id) }}" class="ui-btn ui-btn-sm ui-btn-primary">Rekap / Nilai</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if($ph)
                        <tr x-show="editId === '{{ $ph->id }}'" x-cloak>
                            <td colspan="6" class="bg-slate-50">
                                {{-- Diadopsi dari form Edit Data Sempro: judul/bidang fokus/abstrak,
                                link Naskah Lengkap (Bab I-V), hari/tanggal + Ruangan/Link Zoom,
                                dan Ketua/Sekretaris Penguji — komposisi dewan penguji Semhas sama
                                persis dengan Sempro (Ketua + Sekretaris + 2 Pembimbing tetap). --}}
                                <form action="{{ route('semhas.update.post', $ph->id) }}" method="POST" class="p-3 flex flex-col gap-3">
                                    @csrf
                                    @php
                                        $ketuaIdSemhas = optional(optional($sidangSemhas)->pengujiSidangs)->firstWhere('peran_penguji', 'ketua_penguji')->dosen_id ?? null;
                                        $sekreIdSemhas = optional(optional($sidangSemhas)->pengujiSidangs)->firstWhere('peran_penguji', 'sekretaris_penguji')->dosen_id ?? null;
                                    @endphp
                                    <p class="text-[13px] font-semibold text-primary-900">Edit Data Semhas — {{ $p->mahasiswa->name }}</p>
                                    <p class="text-[12px] text-slate-500 -mt-2">Judul proposal, bidang fokus, abstrak, tanggal sidang, dan penguji eksternal bisa diubah di sini. Pembimbing utama & pendamping tetap (ubah lewat menu Pengajuan kalau perlu).</p>
                                    <div class="ui-field !mb-0">
                                        <label class="ui-label">Naskah Lengkap (Bab I-V)</label>
                                        @if($ph->naskah_bab_1_5_url)
                                            <p class="text-[12px] text-slate-600 !mt-0">
                                                <a href="{{ BerkasController::url($ph->naskah_bab_1_5_url) }}" target="_blank" class="text-primary-700 underline font-semibold">Lihat Naskah</a>
                                                — isi link di bawah untuk mengganti.
                                            </p>
                                        @else
                                            <p class="text-[12px] text-rose-600 !mt-0">(belum diunggah)</p>
                                        @endif
                                        <input type="url" name="naskah_bab_1_5_url" class="ui-input"
                                            placeholder="https://drive.google.com/... atau link cloud lainnya"
                                            value="{{ \Illuminate\Support\Str::startsWith($ph->naskah_bab_1_5_url ?? '', ['http://', 'https://']) ? $ph->naskah_bab_1_5_url : '' }}">
                                        <p class="text-[11px] text-slate-400 !mt-1">Tempel link Google Drive/cloud lain yang bisa diakses publik. Kosongkan untuk tetap pakai naskah yang sudah ada.</p>
                                        @error('naskah_bab_1_5_url')
                                            <p class="text-[12px] text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="ui-field !mb-0">
                                        <label class="ui-label">Judul Tesis <span class="text-rose-600">*</span></label>
                                        <textarea name="judul_tesis" class="ui-input" rows="2" maxlength="500" required>{{ $p->judul_tesis }}</textarea>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-3">
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Bidang Fokus <span class="text-rose-600">*</span></label>
                                            <input type="text" name="bidang_fokus" class="ui-input" required value="{{ $p->bidang_fokus }}">
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Hari / Tanggal Sidang <span class="text-rose-600">*</span></label>
                                            <input type="datetime-local" name="jadwal_usulan_sidang" class="ui-input" required
                                                value="{{ \Carbon\Carbon::parse($ph->jadwal_usulan_sidang)->format('Y-m-d\TH:i') }}">
                                        </div>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-3">
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Ruangan (kosongkan kalau daring)</label>
                                            <input type="text" name="ruangan" class="ui-input" placeholder="Ruang Sidang FKIP"
                                                value="{{ $sidangSemhas->ruangan ?? '' }}">
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Link Zoom (kalau daring)</label>
                                            <input type="text" name="link_zoom" class="ui-input" placeholder="https://zoom.us/j/..."
                                                value="{{ $sidangSemhas->link_zoom ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="ui-field !mb-0">
                                        <label class="ui-label">Abstrak Rencana</label>
                                        <textarea name="abstrak_rencana" class="ui-input" rows="3">{{ $p->abstrak_rencana }}</textarea>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-3">
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
                                                <option value="">— Pilih Dosen/Komisi Tesis/Kaprodi —</option>
                                                @foreach($calonPenguji as $d)
                                                    @if($d->id !== $p->pembimbing_1_id && $d->id !== $p->pembimbing_2_id)
                                                        <option value="{{ $d->id }}" @selected($ketuaIdSemhas === $d->id)>{{ $d->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Sekretaris Penguji</label>
                                            <select name="sekretaris_penguji_id" class="ui-input">
                                                <option value="">— Pilih Dosen/Komisi Tesis/Kaprodi —</option>
                                                @foreach($calonPenguji as $d)
                                                    @if($d->id !== $p->pembimbing_1_id && $d->id !== $p->pembimbing_2_id)
                                                        <option value="{{ $d->id }}" @selected($sekreIdSemhas === $d->id)>{{ $d->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @error('judul_tesis')
                                        <p class="text-[12px] text-rose-600">{{ $message }}</p>
                                    @enderror
                                    @error('bidang_fokus')
                                        <p class="text-[12px] text-rose-600">{{ $message }}</p>
                                    @enderror
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
        <div class="overflow-x-auto -mx-1" x-data="{ editId: null }">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Mahasiswa</th><th>Judul</th><th>Jadwal & Penguji</th><th>Status</th><th>Dokumen</th><th>Aksi</th>
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
                                    <div class="text-slate-500 text-[11px]">
                                        Ruang:
                                        @if(filled($sidangUjian->ruangan))
                                            {{ $sidangUjian->ruangan }}
                                        @elseif(filled($sidangUjian->link_zoom))
                                            Daring (<a href="{{ $sidangUjian->link_zoom }}" target="_blank" class="underline">Link Zoom</a>)
                                        @else
                                            TBA
                                        @endif
                                    </div>
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
                            <td class="text-[12px]">
                                Naskah Lengkap:
                                @if($pu && $pu->naskah_tesis_lengkap_url)
                                    <a href="{{ BerkasController::url($pu->naskah_tesis_lengkap_url) }}" target="_blank" class="text-primary-700 underline">Lihat</a>
                                @else
                                    <span class="text-slate-400">(belum diunggah)</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1.5">
                                    @if($pu)
                                        <button type="button" class="ui-btn ui-btn-sm ui-btn-outline"
                                            @click="editId = editId === '{{ $pu->id }}' ? null : '{{ $pu->id }}'">
                                            Edit
                                        </button>
                                    @endif
                                    @if($sidangUjian)
                                        <a href="{{ route('sidang.penilaian', $sidangUjian->id) }}" class="ui-btn ui-btn-sm ui-btn-primary">Rekap / Nilai</a>
                                    @else
                                        <span class="text-[12px] text-slate-400">Menunggu plotting</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if($pu)
                        <tr x-show="editId === '{{ $pu->id }}'" x-cloak>
                            <td colspan="6" class="bg-slate-50">
                                {{-- Diadopsi dari form Edit Data Sempro, dengan penyesuaian komposisi
                                dewan penguji: Ujian Tesis memakai peran Penguji Bidang Studi &
                                Penguji Bidang Pendidikan (bukan Ketua/Sekretaris Penguji), lihat
                                KomisiTesisController::createSidangWithPenguji(). --}}
                                <form action="{{ route('ujian.update.post', $pu->id) }}" method="POST" class="p-3 flex flex-col gap-3">
                                    @csrf
                                    @php
                                        $pengujiStudiId = optional(optional($sidangUjian)->pengujiSidangs)->firstWhere('peran_penguji', 'penguji_studi')->dosen_id ?? null;
                                        $pengujiPendidikanId = optional(optional($sidangUjian)->pengujiSidangs)->firstWhere('peran_penguji', 'penguji_pendidikan')->dosen_id ?? null;
                                    @endphp
                                    <p class="text-[13px] font-semibold text-primary-900">Edit Data Ujian Tesis — {{ $p->mahasiswa->name }}</p>
                                    <p class="text-[12px] text-slate-500 -mt-2">Judul proposal, bidang fokus, abstrak, tanggal sidang, dan penguji eksternal bisa diubah di sini. Pembimbing utama & pendamping tetap (ubah lewat menu Pengajuan kalau perlu).</p>
                                    <div class="ui-field !mb-0">
                                        <label class="ui-label">Naskah Tesis Lengkap</label>
                                        @if($pu->naskah_tesis_lengkap_url)
                                            <p class="text-[12px] text-slate-600 !mt-0">
                                                <a href="{{ BerkasController::url($pu->naskah_tesis_lengkap_url) }}" target="_blank" class="text-primary-700 underline font-semibold">Lihat Naskah</a>
                                                — isi link di bawah untuk mengganti.
                                            </p>
                                        @else
                                            <p class="text-[12px] text-rose-600 !mt-0">(belum diunggah)</p>
                                        @endif
                                        <input type="url" name="naskah_tesis_lengkap_url" class="ui-input"
                                            placeholder="https://drive.google.com/... atau link cloud lainnya"
                                            value="{{ \Illuminate\Support\Str::startsWith($pu->naskah_tesis_lengkap_url ?? '', ['http://', 'https://']) ? $pu->naskah_tesis_lengkap_url : '' }}">
                                        <p class="text-[11px] text-slate-400 !mt-1">Tempel link Google Drive/cloud lain yang bisa diakses publik. Kosongkan untuk tetap pakai naskah yang sudah ada.</p>
                                        @error('naskah_tesis_lengkap_url')
                                            <p class="text-[12px] text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="ui-field !mb-0">
                                        <label class="ui-label">Judul Tesis <span class="text-rose-600">*</span></label>
                                        <textarea name="judul_tesis" class="ui-input" rows="2" maxlength="500" required>{{ $p->judul_tesis }}</textarea>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-3">
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Bidang Fokus <span class="text-rose-600">*</span></label>
                                            <input type="text" name="bidang_fokus" class="ui-input" required value="{{ $p->bidang_fokus }}">
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Hari / Tanggal Sidang <span class="text-rose-600">*</span></label>
                                            <input type="datetime-local" name="jadwal_usulan_sidang" class="ui-input" required
                                                value="{{ \Carbon\Carbon::parse($pu->jadwal_usulan_sidang)->format('Y-m-d\TH:i') }}">
                                        </div>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-3">
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Ruangan (kosongkan kalau daring)</label>
                                            <input type="text" name="ruangan" class="ui-input" placeholder="Ruang Sidang FKIP"
                                                value="{{ $sidangUjian->ruangan ?? '' }}">
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Link Zoom (kalau daring)</label>
                                            <input type="text" name="link_zoom" class="ui-input" placeholder="https://zoom.us/j/..."
                                                value="{{ $sidangUjian->link_zoom ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="ui-field !mb-0">
                                        <label class="ui-label">Abstrak Rencana</label>
                                        <textarea name="abstrak_rencana" class="ui-input" rows="3">{{ $p->abstrak_rencana }}</textarea>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-3">
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Pembimbing Utama</label>
                                            <input type="text" class="ui-input bg-slate-100" value="{{ $p->pembimbing1->name ?? 'Belum ditetapkan' }}" disabled>
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Pembimbing Pendamping</label>
                                            <input type="text" class="ui-input bg-slate-100" value="{{ $p->pembimbing2->name ?? 'Belum ditetapkan' }}" disabled>
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Penguji Bidang Studi</label>
                                            <select name="penguji_studi_id" class="ui-input">
                                                <option value="">— Pilih Dosen/Komisi Tesis/Kaprodi —</option>
                                                @foreach($calonPenguji as $d)
                                                    @if($d->id !== $p->pembimbing_1_id && $d->id !== $p->pembimbing_2_id)
                                                        <option value="{{ $d->id }}" @selected($pengujiStudiId === $d->id)>{{ $d->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="ui-field !mb-0">
                                            <label class="ui-label">Penguji Bidang Pendidikan</label>
                                            <select name="penguji_pendidikan_id" class="ui-input">
                                                <option value="">— Pilih Dosen/Komisi Tesis/Kaprodi —</option>
                                                @foreach($calonPenguji as $d)
                                                    @if($d->id !== $p->pembimbing_1_id && $d->id !== $p->pembimbing_2_id)
                                                        <option value="{{ $d->id }}" @selected($pengujiPendidikanId === $d->id)>{{ $d->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @error('judul_tesis')
                                        <p class="text-[12px] text-rose-600">{{ $message }}</p>
                                    @enderror
                                    @error('bidang_fokus')
                                        <p class="text-[12px] text-rose-600">{{ $message }}</p>
                                    @enderror
                                    @error('jadwal_usulan_sidang')
                                        <p class="text-[12px] text-rose-600">{{ $message }}</p>
                                    @enderror
                                    @error('penguji_studi_id')
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
                        @endif
                    @empty
                    <tr><td colspan="6" class="text-center text-slate-500 py-6">Belum ada mahasiswa Ujian Tesis.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
    @endif

</div>

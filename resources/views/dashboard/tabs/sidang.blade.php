<div class="flex flex-col gap-4">

    @php
        $role = Auth::user()->role ?? null;
        // Kebocoran data yang diperbaiki: tabel jadwal & dewan penguji di
        // bawah dulu menampilkan SEMUA sidang (semua mahasiswa) ke siapa
        // pun yang login. Sekarang di-filter dulu sesuai lingkup akses.
        $sidangUntukTabel = match(true) {
            $role === 'mahasiswa' => $myPengajuan?->aktivitasSidangs ?? collect(),
            $role === 'dosen' => ($myTugasPenguji ?? collect())->pluck('sidang')->filter()->unique('id'),
            default => $sidangs, // pengendali akademik: lihat semua
        };
        $fokusTahap = $fokusTahap ?? null;
        if ($fokusTahap) {
            $sidangUntukTabel = collect($sidangUntukTabel)->where('tahap_sidang', $fokusTahap);
        }
    @endphp

    @if(Auth::check() && Auth::user()->hasRole('komisi_tesis'))
    <x-ui.card title="Daftarkan Langsung (Mahasiswa Lama)"
        subtitle="Khusus mahasiswa lama yang Sempro/Semhas/Ujian Tesis-nya tidak melalui alur pendaftaran mandiri di sistem ini. Setelah didaftarkan di sini, mahasiswa akan otomatis muncul pada form Plotting Jadwal & Dewan Penguji di bawah.">
        <form id="form-daftar-langsung" method="POST">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="ui-field">
                    <label class="ui-label">Mahasiswa</label>
                    <select id="daftar-langsung-mahasiswa" class="ui-input" onchange="updateDaftarLangsungAction()" required>
                        <option value="">-- Pilih Mahasiswa --</option>
                        @foreach($pengajuans as $pe)
                            <option value="{{ $pe->id }}">{{ $pe->mahasiswa->identifier ?? '' }} — {{ $pe->mahasiswa->name ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ui-field">
                    <label class="ui-label">Jenis Sidang</label>
                    <select id="daftar-langsung-tahap" class="ui-input" onchange="updateDaftarLangsungAction()" required>
                        <option value="">-- Pilih Jenis Sidang --</option>
                        <option value="sempro">Seminar Proposal (Sempro)</option>
                        <option value="semhas">Seminar Hasil (Semhas)</option>
                        <option value="ujian">Ujian Tesis</option>
                    </select>
                </div>
            </div>
            <div class="ui-field">
                <label class="ui-label">Tanggal/Jam Sidang (sesuai riwayat, boleh tanggal yang sudah lewat)</label>
                <input type="datetime-local" name="jadwal_usulan_sidang" class="ui-input" required>
            </div>
            <div class="ui-field">
                <label class="ui-label">Catatan (opsional)</label>
                <input type="text" name="catatan" class="ui-input" placeholder="Contoh: migrasi data mahasiswa lama sebelum sistem ini berjalan">
            </div>
            <p class="text-[11.5px] text-slate-500 mb-3">Mahasiswa yang dipilih wajib sudah punya Pembimbing 1 & 2. Dokumen pendaftaran tidak diminta di sini — anggap sudah lengkap secara administratif.</p>
            <button type="submit" class="ui-btn ui-btn-primary">Daftarkan Langsung</button>
        </form>
    </x-ui.card>
    @endif

    @if(Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi']))
    @php
        $eligiblePlotting = [];
        foreach ($pengajuans as $pe) {
            if ((!$fokusTahap || $fokusTahap === 'sempro')
                && $pe->status_tahap === 'tahap_2_sempro'
                && $pe->pendaftaranSempro?->status_verifikasi_admin === 'verified'
                && !$pe->aktivitasSidangs->firstWhere('tahap_sidang', 'sempro')) {
                $eligiblePlotting[] = ['pengajuan' => $pe, 'tahap' => 'sempro', 'label' => $pe->mahasiswa->name . ' — Sempro'];
            }
            if ((!$fokusTahap || $fokusTahap === 'semhas')
                && $pe->status_tahap === 'tahap_3_semhas'
                && $pe->pendaftaranSemhas?->status_verifikasi_admin === 'verified'
                && !$pe->aktivitasSidangs->firstWhere('tahap_sidang', 'semhas')) {
                $eligiblePlotting[] = ['pengajuan' => $pe, 'tahap' => 'semhas', 'label' => $pe->mahasiswa->name . ' — Semhas'];
            }
        }
    @endphp
    @if(count($eligiblePlotting) > 0 || $errors->any())
    <x-ui.card title="Plotting Jadwal & Dewan Penguji Baru"
        subtitle="Isi jadwal sidang dan 4 penguji untuk mahasiswa yang pendaftarannya sudah disetujui tetapi belum dijadwalkan.">

        @if($errors->any())
            <x-ui.alert type="error">
                @foreach($errors->all() as $error) {{ $error }}<br> @endforeach
            </x-ui.alert>
        @endif

        @if(count($eligiblePlotting) === 0)
            <p class="text-slate-500 text-[13.5px]">Tidak ada mahasiswa yang menunggu plotting.</p>
        @else
        <form id="form-plotting" method="POST">
            @csrf
            <div class="ui-field">
                <label class="ui-label">Pilih Mahasiswa & Tahap Sidang</label>
                <select id="plotting-select" class="ui-input" onchange="updatePlottingAction(this)">
                    <option value="">-- Pilih --</option>
                    @foreach($eligiblePlotting as $ep)
                        <option value="{{ $ep['tahap'] }}"
                            data-pengajuan-id="{{ $ep['pengajuan']->id }}"
                            data-pembimbing1="{{ $ep['pengajuan']->pembimbing_1_id }}"
                            data-pembimbing2="{{ $ep['pengajuan']->pembimbing_2_id }}">{{ $ep['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3.5">
                <div class="ui-field">
                    <label class="ui-label">Waktu Mulai</label>
                    <input type="datetime-local" name="waktu_mulai" class="ui-input" required>
                </div>
                <div class="ui-field">
                    <label class="ui-label">Waktu Selesai</label>
                    <input type="datetime-local" name="waktu_selesai" class="ui-input" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3.5">
                <div class="ui-field">
                    <label class="ui-label">Ruangan (kosongkan kalau daring)</label>
                    <input type="text" name="ruangan" class="ui-input" placeholder="Ruang Sidang FKIP">
                </div>
                <div class="ui-field">
                    <label class="ui-label">Link Zoom (kalau daring)</label>
                    <input type="text" name="link_zoom" class="ui-input" placeholder="https://zoom.us/j/...">
                </div>
            </div>
            <div class="ui-field">
                <label class="ui-label">Komisi Tesis Penanggung Jawab</label>
                <select name="komisi_tesis_id" class="ui-input" required>
                    @foreach($dosens as $d)
                        @if($d->role === 'komisi_tesis' || $d->is_komisi_tesis)
                            <option value="{{ $d->id }}" {{ $komisi && $komisi->id === $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endif
                    @endforeach
                    @if($komisi && !$dosens->contains('id', $komisi->id))
                        <option value="{{ $komisi->id }}" selected>{{ $komisi->name }}</option>
                    @endif
                </select>
            </div>

            <div class="text-[12px] font-bold uppercase tracking-wider text-slate-500 mt-5 mb-3 pb-1.5 border-b border-slate-100">Dewan Penguji &mdash; 4 Dosen</div>

            <div class="grid grid-cols-2 gap-3.5">
                <div class="ui-field">
                    <label class="ui-label">Ketua Penguji</label>
                    <select name="penguji[0][dosen_id]" class="ui-input" required>
                        <option value="">-- Pilih Dosen --</option>
                        @foreach($dosens as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </select>
                    <input type="hidden" name="penguji[0][peran_penguji]" value="ketua_penguji">
                </div>
                <div class="ui-field">
                    <label class="ui-label">Sekretaris Penguji</label>
                    <select name="penguji[1][dosen_id]" class="ui-input" required>
                        <option value="">-- Pilih Dosen --</option>
                        @foreach($dosens as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </select>
                    <input type="hidden" name="penguji[1][peran_penguji]" value="sekretaris_penguji">
                </div>
                <div class="ui-field">
                    <label class="ui-label">Anggota 1 (Pembimbing 1)</label>
                    <select id="plotting-anggota1" name="penguji[2][dosen_id]" class="ui-input" required>
                        <option value="">-- Pilih Dosen --</option>
                        @foreach($dosens as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </select>
                    <input type="hidden" name="penguji[2][peran_penguji]" value="pembimbing_1">
                </div>
                <div class="ui-field">
                    <label class="ui-label">Anggota 2 (Pembimbing 2)</label>
                    <select id="plotting-anggota2" name="penguji[3][dosen_id]" class="ui-input" required>
                        <option value="">-- Pilih Dosen --</option>
                        @foreach($dosens as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </select>
                    <input type="hidden" name="penguji[3][peran_penguji]" value="pembimbing_2">
                </div>
            </div>

            <button type="submit" class="ui-btn ui-btn-success mt-2">Simpan Plotting</button>
        </form>
        @endif
    </x-ui.card>
    @endif
    @endif

    @php
        $judulDaftarSidang = match ($fokusTahap ?? null) {
            'sempro' => 'Daftar mahasiswa yang sudah menempuh Seminar Proposal',
            'semhas' => 'Daftar mahasiswa yang sudah menempuh Seminar Hasil',
            'ujian' => 'Daftar mahasiswa yang sudah menempuh Ujian Tesis',
            default => 'Daftar sidang yang sudah diplotting',
        };
    @endphp
    @unless($fokusTahap)
    <x-ui.card :title="$judulDaftarSidang">
        <div class="overflow-x-auto -mx-1">
            <table class="ui-table">
                <thead>
                    <tr><th>Tahap Sidang</th><th>Mahasiswa</th><th>Jadwal & Ruangan</th><th>Dewan Penguji Terplotting</th><th>Kalender</th></tr>
                </thead>
                <tbody>
                    @forelse($sidangUntukTabel as $s)
                    <tr>
                        <td><strong>{{ strtoupper($s->tahap_sidang) }}</strong></td>
                        <td>{{ $s->pengajuanTesis->mahasiswa->name ?? '-' }}</td>
                        <td>{{ $s->waktu_mulai }} s.d {{ $s->waktu_selesai }}<br><span class="text-slate-500 text-[11px]">Ruang: {{ $s->ruangan ?? 'Zoom Cloud' }}</span></td>
                        <td>
                            @foreach($s->pengujiSidangs as $ps)
                                &bull; {{ $ps->dosen->name }} (<em>{{ $ps->peran_penguji }}</em>)<br>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('sidang.kalenderIcs', $s->id) }}" class="ui-btn ui-btn-sm ui-btn-outline">.ics</a>
                            @if(Auth::check() && Auth::user()->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi']))
                                <a href="{{ route('dokumen.bundle', $s->id) }}" target="_blank" class="ui-btn ui-btn-sm ui-btn-outline">Cetak Bundle</a>
                                <a href="{{ route('sidang.penilaian', $s->id) }}" class="ui-btn ui-btn-sm ui-btn-primary">Rekap / Nilai</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-slate-500 py-6">Belum ada plotting jadwal sidang yang aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
    @endunless
</div>

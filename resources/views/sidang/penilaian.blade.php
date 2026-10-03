<x-layouts.app title="Nilai Sidang — SIM-TESIS">

    @php
        $s = $sidang;
        $role = Auth::user()->role ?? null;
        $pakai4Dimensi = $s->tahap_sidang === 'ujian';
        $judulTahap = match ($s->tahap_sidang) {
            'sempro' => 'Seminar Proposal',
            'semhas' => 'Seminar Hasil',
            'ujian' => 'Ujian Tesis',
            default => strtoupper($s->tahap_sidang),
        };
        $semuaSudahNilai = $s->pengujiSidangs->isNotEmpty()
            && $s->pengujiSidangs->every(fn ($p) => $p->nilai_total_angka !== null);
        $rata = $s->pengujiSidangs->avg('nilai_total_angka');
        $rekap = $s->manajemenNilai;
    @endphp

    <div class="max-w-4xl mx-auto w-full flex flex-col gap-4">
        <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-primary-800 text-[13px] font-semibold">&larr; Kembali ke Dashboard</a>

        <x-ui.hero
            :title="($isPengendali ? 'Rekap Nilai — ' : 'Input Nilai — ') . $judulTahap"
            :subtitle="$isPengendali
                ? 'Komisi Tesis & Admin hanya menetapkan rekap dan keputusan sidang. Rincian I1–I10 diisi oleh masing-masing penguji.'
                : ($pakai4Dimensi ? 'Isi rubrik 4 dimensi untuk sidang ini.' : 'Isi rubrik 10 indikator (FPT-TI-03) pada baris Anda.')"
        />

        <x-ui.card title="Data Sidang">
            <x-ui.info-row label="Mahasiswa">{{ $s->pengajuanTesis->mahasiswa->name ?? '-' }} ({{ $s->pengajuanTesis->mahasiswa->identifier ?? '' }})</x-ui.info-row>
            <x-ui.info-row label="Judul">{{ $s->pengajuanTesis->judul_tesis ?? '-' }}</x-ui.info-row>
            <x-ui.info-row label="Jadwal">{{ optional($s->waktu_mulai)->format('d/m/Y H:i') }} – {{ optional($s->waktu_selesai)->format('H:i') }}</x-ui.info-row>
            <x-ui.info-row label="Ruang">
                @if(filled($s->ruangan))
                    {{ $s->ruangan }}
                @elseif(filled($s->link_zoom))
                    Daring — <a href="{{ $s->link_zoom }}" target="_blank" class="text-primary-700 underline">{{ $s->link_zoom }}</a>
                @else
                    TBA
                @endif
            </x-ui.info-row>
        </x-ui.card>

        @if(session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if($errors->any())
            <x-ui.alert type="error">
                @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
            </x-ui.alert>
        @endif

        {{-- Dua kartu di bawah TIDAK saling eksklusif — sengaja dipisah jadi
        2 blok @if terpisah (bukan @if/@else) supaya orang yang berperan
        GANDA (mis. Komisi Tesis yang juga tercatat sebagai Pembimbing/
        penguji eksternal di sidang ini) tetap melihat form isi nilai
        miliknya sendiri, bukan cuma tampilan rekap. --}}
        @if($isPengendali)
            <x-ui.card title="Ringkasan nilai penguji" subtitle="Detail per indikator hanya dapat diubah oleh dosen penguji yang bersangkutan.">
                <div class="overflow-x-auto -mx-1">
                    <table class="ui-table">
                        <thead>
                            <tr>
                                <th>Penguji</th>
                                <th>Peran</th>
                                <th>Status</th>
                                <th>Nilai total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($s->pengujiSidangs as $ps)
                            <tr>
                                <td class="font-semibold">{{ $ps->dosen->name ?? '-' }}</td>
                                <td class="text-[12.5px] text-slate-500">{{ str_replace('_', ' ', $ps->peran_penguji) }}</td>
                                <td>
                                    <x-ui.badge :color="$ps->nilai_total_angka !== null ? 'green' : 'yellow'">
                                        {{ $ps->nilai_total_angka !== null ? 'Sudah dinilai' : 'Belum dinilai' }}
                                    </x-ui.badge>
                                </td>
                                <td>{{ $ps->nilai_total_angka !== null ? number_format($ps->nilai_total_angka, 2) : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-[12.5px] text-slate-500 mt-3">
                    Rata-rata saat ini:
                    <strong>{{ $rata !== null ? number_format($rata, 2) : '—' }}</strong>
                    @unless($semuaSudahNilai)
                        — rekap belum bisa disimpan sebelum semua penguji mengisi nilai.
                    @endunless
                </p>
            </x-ui.card>

            <x-ui.card title="Rekapitulasi & Keputusan Sidang" subtitle="Diisi Komisi Tesis / Admin setelah seluruh penguji menyimpan nilai.">
                @unless($semuaSudahNilai)
                    <x-ui.alert type="warning">
                        Form rekap belum aktif karena masih ada penguji yang belum menyimpan nilai.
                        <ul class="list-disc pl-4 mt-2">
                            @foreach($s->pengujiSidangs->whereNull('nilai_total_angka') as $ps)
                                <li>{{ $ps->dosen->name ?? 'Penguji' }} ({{ str_replace('_', ' ', $ps->peran_penguji) }})</li>
                            @endforeach
                        </ul>
                    </x-ui.alert>
                @endunless

                <form action="{{ route('sidang.rekapKomisi', $s->id) }}" method="POST" class="flex flex-col gap-3 {{ $semuaSudahNilai ? '' : 'opacity-50 pointer-events-none' }}">
                    @csrf
                    <input type="hidden" name="komisi_tesis_validator_id" value="{{ Auth::id() }}">
                    <div class="grid md:grid-cols-2 gap-3">
                        <div class="ui-field !mb-0">
                            <label class="ui-label">Keputusan Sidang</label>
                            <select name="keputusan_sidang" class="ui-input" required {{ $semuaSudahNilai ? '' : 'disabled' }}>
                                @php $kini = $rekap->keputusan_sidang ?? ''; @endphp
                                <option value="lulus_tanpa_revisi" @selected($kini === 'lulus_tanpa_revisi')>Lulus Tanpa Revisi</option>
                                <option value="lulus_revisi_ringan" @selected($kini === 'lulus_revisi_ringan')>Lulus dengan Revisi Ringan</option>
                                <option value="lulus_revisi_berat" @selected($kini === 'lulus_revisi_berat')>Lulus dengan Revisi Berat</option>
                                <option value="ujian_ulang" @selected($kini === 'ujian_ulang')>Ujian Ulang</option>
                            </select>
                        </div>
                        <div class="ui-field !mb-0">
                            <label class="ui-label">Batas Waktu Revisi</label>
                            <input type="date" name="batas_waktu_revisi" class="ui-input"
                                   value="{{ optional($rekap?->batas_waktu_revisi)->toDateString() ?? now()->addDays(30)->toDateString() }}"
                                   {{ $semuaSudahNilai ? '' : 'disabled' }}>
                        </div>
                    </div>
                    @if($rekap)
                        <p class="text-[12.5px] text-slate-500">
                            Rekap tersimpan: grade <strong>{{ $rekap->grade_kelulusan }}</strong>,
                            rata-rata <strong>{{ number_format($rekap->nilai_rata_rata, 2) }}</strong>.
                            Menyimpan lagi akan menimpa keputusan lama.
                        </p>
                    @endif
                    <div>
                        @if($semuaSudahNilai)
                            <button type="submit" class="ui-btn ui-btn-primary">
                                {{ $rekap ? 'Perbarui Rekap' : 'Simpan Rekap' }}
                            </button>
                        @else
                            <button type="button" class="ui-btn ui-btn-muted" disabled>Simpan Rekap (menunggu nilai penguji)</button>
                        @endif
                    </div>
                </form>
            </x-ui.card>
        @endif

        @if($isDosenPenguji)
            @foreach($s->pengujiSidangs as $ps)
                @continue(Auth::id() !== $ps->dosen_id)
                @php $sudahAdaNilai = $ps->nilai_total_angka !== null; @endphp
                <x-ui.card title="{{ $ps->dosen->name ?? 'Penguji' }}" subtitle="{{ str_replace('_', ' ', $ps->peran_penguji) }}">
                    <div class="flex justify-end mb-3">
                        <x-ui.badge :color="$sudahAdaNilai ? 'green' : 'yellow'">
                            {{ $sudahAdaNilai ? 'Nilai: ' . number_format($ps->nilai_total_angka, 1) : 'Belum Dinilai' }}
                        </x-ui.badge>
                    </div>
                    <form action="{{ route('sidang.submitNilai', $s->id) }}" method="POST" class="flex flex-col gap-3">
                        @csrf
                        <input type="hidden" name="dosen_id" value="{{ $ps->dosen_id }}">
                        @if($pakai4Dimensi)
                            <div class="flex flex-wrap gap-2">
                                @foreach([
                                    'nilai_dimensi_1_naskah' => 'I Naskah',
                                    'nilai_dimensi_2_publikasi' => 'II Publikasi',
                                    'nilai_dimensi_3_presentasi' => 'III Presentasi',
                                    'nilai_dimensi_4_tanyajawab' => 'IV Tanya Jawab',
                                ] as $kolom => $label)
                                    <label class="flex flex-col gap-1 min-w-[120px] flex-1">
                                        <span class="text-[11px] font-bold text-slate-500">{{ $label }}</span>
                                        <input type="number" name="{{ $kolom }}" min="0" max="100" step="1" required placeholder="0"
                                               value="{{ $ps->{$kolom} ?? '' }}" class="ui-input text-center">
                                    </label>
                                @endforeach
                            </div>
                        @else
                            {{-- Tabel rubrik PERSIS form resmi FPT-TI-03: No | Aspek Penilaian |
                            Uraian | Nilai (0-100), dikelompokkan per aspek (bukan kotak "I1..I10"
                            yang tidak jelas artinya apa tanpa buka dokumen cetak). --}}
                            <div class="overflow-x-auto -mx-1">
                                <table class="ui-table">
                                    <thead>
                                        <tr>
                                            <th class="w-10">No</th>
                                            <th>Aspek Penilaian</th>
                                            <th>Uraian</th>
                                            <th class="w-28">Nilai (0-100)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(config('penilaian.aspek_indikator') as $aspek => $nomorIndikator)
                                            @foreach($nomorIndikator as $idx => $i)
                                                <tr>
                                                    <td class="text-center text-[12px]">{{ $idx + 1 }}</td>
                                                    @if($idx === 0)
                                                        <td class="font-semibold text-[12.5px]" rowspan="{{ count($nomorIndikator) }}">{{ $aspek }}</td>
                                                    @endif
                                                    <td class="text-[12.5px]">{{ config('penilaian.label_indikator.'.$i) }}</td>
                                                    <td>
                                                        <input type="number" name="nilai_indikator_{{ $i }}" min="0" max="100" step="1" required placeholder="0"
                                                               value="{{ $ps->{'nilai_indikator_'.$i} ?? '' }}" class="ui-input text-center">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        <div class="ui-field !mb-0">
                            <label class="ui-label">Catatan / saran revisi</label>
                            <textarea name="catatan_revisi" rows="2" class="ui-input" placeholder="Opsional">{{ $ps->catatan_revisi }}</textarea>
                        </div>
                        <div>
                            <button type="submit" class="ui-btn ui-btn-primary">
                                {{ $sudahAdaNilai ? 'Simpan Perubahan Nilai' : 'Simpan Nilai' }}
                            </button>
                        </div>
                    </form>
                </x-ui.card>
            @endforeach
        @endif
    </div>

</x-layouts.app>

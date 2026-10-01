<div class="flex flex-col gap-4">

    @php
        $role = Auth::user()->role ?? null;
        // Kebocoran data yang diperbaiki: kartu nilai sidang di bawah dulu
        // menampilkan nilai SEMUA mahasiswa (dari semua dosen penguji) ke
        // siapa pun yang login — termasuk detail skor per rubrik. Sekarang
        // di-filter dulu sesuai lingkup akses role.
        $sidangUntukPenilaian = match(true) {
            $role === 'mahasiswa' => $myPengajuan?->aktivitasSidangs ?? collect(),
            $role === 'dosen' => ($myTugasPenguji ?? collect())->pluck('sidang')->filter()->unique('id'),
            default => $sidangs, // pengendali akademik: lihat semua
        };
        $fokusTahap = $fokusTahap ?? null;
        if ($fokusTahap) {
            $sidangUntukPenilaian = collect($sidangUntukPenilaian)->where('tahap_sidang', $fokusTahap);
        }
        $judulPenilaian = match ($fokusTahap) {
            'sempro' => 'Penilaian Seminar Proposal',
            'semhas' => 'Penilaian Seminar Hasil',
            'ujian' => 'Penilaian Ujian Tesis',
            default => 'Penilaian Sidang',
        };
        $subPenilaian = match ($fokusTahap) {
            'ujian' => 'Rubrik 4 dimensi (FR-09).',
            'sempro', 'semhas' => 'Rubrik 10 indikator (FPT-TI-03).',
            default => 'Sempro & Semhas: rubrik 10 indikator (FPT-TI-03). Ujian Tesis: rubrik 4 dimensi (FR-09).',
        };
    @endphp

    <x-ui.card :title="$judulPenilaian" :subtitle="$subPenilaian">
        <div class="flex flex-col gap-4">
            @forelse($sidangUntukPenilaian as $s)
                @if($s->pengujiSidangs->isNotEmpty())
                @php $pakai4Dimensi = $s->tahap_sidang === 'ujian'; @endphp
                <div id="penilaian-{{ $s->id }}" class="rounded-xl border border-slate-200 p-4">
                    <div class="text-[13px] font-bold text-primary-900 mb-3">
                        {{ strtoupper($s->tahap_sidang) }} — {{ $s->pengajuanTesis->mahasiswa->name ?? '-' }}
                        <span class="text-slate-400 font-normal text-[11px]">({{ $pakai4Dimensi ? '4 Dimensi' : '10 Indikator' }})</span>
                    </div>
                    <div class="flex flex-col gap-3">
                        @if($role === 'mahasiswa')
                            {{-- Mahasiswa: status ringkas saja, tanpa skor detail
                                 per penguji (belum resmi diumumkan) & tanpa form input. --}}
                            @foreach($s->pengujiSidangs as $ps)
                            <div class="flex justify-between items-center border-t border-slate-100 pt-2 first:border-0 first:pt-0 text-[12.5px]">
                                <span class="font-semibold text-slate-700">{{ str_replace('_',' ', $ps->peran_penguji) }}</span>
                                <x-ui.badge :color="$ps->nilai_total_angka ? 'green' : 'yellow'">{{ $ps->nilai_total_angka ? 'Sudah Dinilai' : 'Belum Dinilai' }}</x-ui.badge>
                            </div>
                            @endforeach
                        @else
                        @foreach($s->pengujiSidangs as $ps)
                        <div class="border-t border-slate-100 pt-3 first:border-0 first:pt-0">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[12.5px] font-semibold text-slate-700">{{ $ps->dosen->name }} <span class="text-slate-400 font-normal">({{ str_replace('_',' ', $ps->peran_penguji) }})</span></span>
                                <span class="text-[12px] font-bold {{ $ps->nilai_total_angka ? 'text-emerald-700' : 'text-amber-600' }}">
                                    {{ $ps->nilai_total_angka ? 'Total: ' . number_format($ps->nilai_total_angka, 2) : 'Belum Dinilai' }}
                                </span>
                            </div>
                            <form action="{{ route('sidang.submitNilai', $s->id) }}" method="POST" class="flex flex-wrap gap-2 items-end">
                                @csrf
                                <input type="hidden" name="dosen_id" value="{{ $ps->dosen_id }}">
                                @if($pakai4Dimensi)
                                    @php
                                        $dimensiLabel = [
                                            'nilai_dimensi_1_naskah' => 'I. Naskah',
                                            'nilai_dimensi_2_publikasi' => 'II. Publikasi',
                                            'nilai_dimensi_3_presentasi' => 'III. Presentasi',
                                            'nilai_dimensi_4_tanyajawab' => 'IV. Tanya Jawab',
                                        ];
                                    @endphp
                                    @foreach($dimensiLabel as $kolom => $label)
                                        <label class="flex flex-col items-center gap-1">
                                            <span class="text-[9px] text-slate-400 font-bold">{{ $label }}</span>
                                            <input type="number" name="{{ $kolom }}" min="0" max="100"
                                                   value="{{ $ps->{$kolom} ?? 85 }}"
                                                   class="ui-input !w-16 !px-1.5 !py-1.5 text-center text-[11px]">
                                        </label>
                                    @endforeach
                                @else
                                    @for($i = 1; $i <= 10; $i++)
                                        <label class="flex flex-col items-center gap-1">
                                            <span class="text-[9px] text-slate-400 font-bold" title="{{ config("penilaian.label_indikator.$i") }}">I{{ $i }}</span>
                                            <input type="number" name="nilai_indikator_{{ $i }}" min="0" max="100"
                                                   value="{{ $ps->{"nilai_indikator_$i"} ?? 85 }}"
                                                   class="ui-input !w-14 !px-1.5 !py-1.5 text-center text-[11px]">
                                        </label>
                                    @endfor
                                @endif
                                <button type="submit" class="ui-btn ui-btn-sm ui-btn-primary">Simpan</button>
                            </form>
                        </div>
                        @endforeach
                        @endif
                    </div>
                </div>
                @endif
            @empty
                <p class="text-slate-500 text-[13.5px]">Belum ada sidang dengan dewan penguji terplotting.</p>
            @endforelse
        </div>
        <p class="text-[11px] text-slate-400 mt-3">
            Rubrik 10 indikator (Sempro/Semhas): {{ collect(config('penilaian.label_indikator'))->map(fn($l,$i) => "I{$i}={$l}")->implode(' · ') }}
        </p>
    </x-ui.card>

    @if(Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi']))
    <x-ui.card title="Rekapitulasi Nilai & Keputusan Sidang (FPT-TI-05/06)" subtitle="Hanya bisa direkap kalau SEMUA penguji pada sidang tsb sudah mengisi nilai. Setelah rekap, BAP &amp; matriks revisi bisa diakses mahasiswa.">
            <div class="flex flex-col gap-4">
                @php
                    $siapDirekap = collect($sidangs)->filter(function ($s) use ($fokusTahap) {
                        if ($fokusTahap && $s->tahap_sidang !== $fokusTahap) return false;
                        return $s->pengujiSidangs->isNotEmpty()
                            && $s->pengujiSidangs->every(fn ($p) => $p->nilai_total_angka !== null)
                            && !$s->manajemenNilai;
                    });
                @endphp
                @forelse($siapDirekap as $s)
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="text-[13px] font-bold text-primary-900 mb-2">
                            {{ strtoupper($s->tahap_sidang) }} — {{ $s->pengajuanTesis->mahasiswa->name ?? '-' }}
                            <span class="text-slate-400 font-normal text-[11.5px]">(rata-rata sementara: {{ number_format($s->pengujiSidangs->avg('nilai_total_angka'), 2) }})</span>
                        </div>
                        <form action="{{ route('sidang.rekapKomisi', $s->id) }}" method="POST" class="flex flex-wrap gap-2 items-end">
                            @csrf
                            <input type="hidden" name="komisi_tesis_validator_id" value="{{ Auth::id() }}">
                            <div class="ui-field !mb-0">
                                <label class="ui-label">Keputusan Sidang</label>
                                <select name="keputusan_sidang" class="ui-input" required>
                                    <option value="lulus_tanpa_revisi">Lulus Tanpa Revisi</option>
                                    <option value="lulus_revisi_ringan">Lulus dengan Revisi Ringan</option>
                                    <option value="lulus_revisi_berat">Lulus dengan Revisi Berat</option>
                                    <option value="ujian_ulang">Ujian Ulang</option>
                                </select>
                            </div>
                            <div class="ui-field !mb-0">
                                <label class="ui-label">Batas Waktu Revisi</label>
                                <input type="date" name="batas_waktu_revisi" class="ui-input" value="{{ now()->addDays(30)->toDateString() }}">
                            </div>
                            <button type="submit" class="ui-btn ui-btn-success">Simpan Rekap & Terbitkan BAP</button>
                        </form>
                    </div>
                @empty
                    <p class="text-slate-500 text-[13.5px]">Tidak ada sidang yang siap direkap saat ini (semua penguji harus mengisi nilai dulu).</p>
                @endforelse
            </div>
    </x-ui.card>
    @endif

</div>

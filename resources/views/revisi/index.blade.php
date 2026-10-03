<x-layouts.app title="Matriks Revisi — SIM-TESIS">

    @php
        $judulTahap = match ($sidang->tahap_sidang) {
            'sempro' => 'Seminar Proposal',
            'semhas' => 'Seminar Hasil',
            'ujian' => 'Ujian Tesis',
            default => strtoupper($sidang->tahap_sidang),
        };
    @endphp

    <div class="max-w-4xl mx-auto w-full flex flex-col gap-4">
        <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-primary-800 text-[13px] font-semibold">&larr; Kembali ke Dashboard</a>

        <x-ui.hero
            :title="'Matriks Revisi — ' . $judulTahap"
            subtitle="Perbaikan naskah pasca sidang wajib di-ACC seluruh dewan penguji sebelum disahkan Kaprodi dan status tahapan akademik berpindah."
        />

        <x-ui.card title="Data Sidang">
            <x-ui.info-row label="Mahasiswa">{{ $tesis->mahasiswa->name ?? '-' }} ({{ $tesis->mahasiswa->identifier ?? '' }})</x-ui.info-row>
            <x-ui.info-row label="Judul">{{ $tesis->judul_tesis ?? '-' }}</x-ui.info-row>
            <x-ui.info-row label="Keputusan Sidang">
                {{ $sidang->manajemenNilai ? str_replace('_', ' ', $sidang->manajemenNilai->keputusan_sidang) : '(belum direkap)' }}
            </x-ui.info-row>
            @if($sidang->manajemenNilai?->batas_waktu_revisi)
                <x-ui.info-row label="Batas Waktu Revisi">{{ \Carbon\Carbon::parse($sidang->manajemenNilai->batas_waktu_revisi)->translatedFormat('d F Y') }}</x-ui.info-row>
            @endif
        </x-ui.card>

        @if(session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if($errors->any())
            <x-ui.alert type="error">
                @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
            </x-ui.alert>
        @endif

        @if($blockReason)
            <x-ui.alert type="info">{{ $blockReason }}</x-ui.alert>
        @else

            @if($revisi?->pengesahan_kaprodi)
                <x-ui.alert type="success">
                    Revisi sudah disahkan Kaprodi pada {{ optional($revisi->disahkan_kaprodi_at)->translatedFormat('d F Y, H:i') }}. Status tahapan akademik mahasiswa sudah berpindah ke tahap berikutnya.
                </x-ui.alert>
            @endif

            {{-- Form pengajuan/pembaruan matriks — HANYA mahasiswa pemilik.
            Diisi ulang (resubmit) kalau salah satu penguji minta perbaikan
            lagi: seluruh baris otomatis balik ke status 'pending'. --}}
            @if($isMahasiswaPemilik && !($revisi?->pengesahan_kaprodi))
            <x-ui.card title="Ajukan / Perbarui Matriks Revisi" subtitle="Isi link naskah revisi final dan uraian perbaikan untuk setiap dewan penguji.">
                <form action="{{ route('revisi.submitMatriks', $sidang->id) }}" method="POST" class="flex flex-col gap-3">
                    @csrf
                    <div class="ui-field !mb-0">
                        <label class="ui-label">Link Naskah Revisi Final <span class="text-rose-600">*</span></label>
                        <input type="text" name="naskah_revisi_final_url" class="ui-input" required
                            placeholder="https://drive.google.com/... atau link cloud lainnya"
                            value="{{ old('naskah_revisi_final_url', $revisi->naskah_revisi_final_url ?? '') }}">
                    </div>
                    <div class="ui-field !mb-0">
                        <label class="ui-label">Link Bukti Luaran Final (artikel/publikasi, kalau ada)</label>
                        <input type="text" name="bukti_luaran_final_url" class="ui-input"
                            placeholder="https://..."
                            value="{{ old('bukti_luaran_final_url', $revisi->bukti_luaran_final_url ?? '') }}">
                    </div>

                    <div class="h-px bg-slate-200 my-1"></div>
                    <p class="text-[12.5px] font-semibold text-primary-900">Uraian Perbaikan per Dewan Penguji</p>

                    @foreach($baristMatriks as $i => $baris)
                        @php $rp = $baris['revisiPenguji']; @endphp
                        <div class="rounded-xl border border-slate-200 p-3 flex flex-col gap-2">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[13px] font-bold text-primary-900">{{ $baris['dosen']->name ?? '-' }} <span class="text-[11px] font-normal text-slate-500">({{ str_replace('_', ' ', $baris['peran_penguji']) }})</span></p>
                                @if($rp)
                                    <x-ui.badge :color="match($rp->status_acc){'acc'=>'green','perlu_perbaikan_lagi'=>'red',default=>'yellow'}">
                                        {{ match($rp->status_acc){'acc'=>'Sudah ACC','perlu_perbaikan_lagi'=>'Perlu Perbaikan Lagi',default=>'Menunggu ACC'} }}
                                    </x-ui.badge>
                                @endif
                            </div>
                            @if($rp?->status_acc === 'perlu_perbaikan_lagi' && $rp->feedback_penguji)
                                <p class="text-[12px] text-rose-700 bg-rose-50 border border-rose-200 rounded-lg px-2.5 py-1.5">Catatan: {{ $rp->feedback_penguji }}</p>
                            @endif
                            <input type="hidden" name="matriks[{{ $i }}][dosen_penguji_id]" value="{{ $baris['dosen']->id ?? '' }}">
                            <div class="ui-field !mb-0">
                                <label class="ui-label">Uraian Hasil Perbaikan <span class="text-rose-600">*</span></label>
                                <textarea name="matriks[{{ $i }}][uraian_hasil_perbaikan]" class="ui-input" rows="2" required>{{ old("matriks.$i.uraian_hasil_perbaikan", $rp->uraian_hasil_perbaikan ?? '') }}</textarea>
                            </div>
                            <div class="ui-field !mb-0">
                                <label class="ui-label">Link Bukti Halaman Perbaikan <span class="text-rose-600">*</span></label>
                                <input type="text" name="matriks[{{ $i }}][bukti_halaman_perbaikan]" class="ui-input" required
                                    placeholder="https://drive.google.com/... (screenshot/scan halaman yang direvisi)"
                                    value="{{ old("matriks.$i.bukti_halaman_perbaikan", $rp->bukti_halaman_perbaikan ?? '') }}">
                            </div>
                        </div>
                    @endforeach

                    <div>
                        <button type="submit" class="ui-btn ui-btn-primary">{{ $revisi ? 'Perbarui Matriks Revisi' : 'Ajukan Matriks Revisi' }}</button>
                    </div>
                </form>
            </x-ui.card>
            @endif

            @if(!$revisi && !$isMahasiswaPemilik)
                <x-ui.alert type="info">Mahasiswa belum mengajukan matriks revisi untuk sidang ini.</x-ui.alert>
            @endif

            {{-- Status ringkas per penguji — selalu ditampilkan ke semua
            pihak yang boleh membuka halaman ini. --}}
            @if($revisi)
            <x-ui.card title="Status ACC Dewan Penguji">
                <div class="overflow-x-auto -mx-1">
                    <table class="ui-table">
                        <thead><tr><th>Penguji</th><th>Status</th><th>Catatan</th><th>Aksi</th></tr></thead>
                        <tbody>
                            @foreach($revisi->revisiPengujis as $rp)
                            <tr>
                                <td class="font-semibold">{{ $rp->dosenPenguji->name ?? '-' }}</td>
                                <td>
                                    <x-ui.badge :color="match($rp->status_acc){'acc'=>'green','perlu_perbaikan_lagi'=>'red',default=>'yellow'}">
                                        {{ match($rp->status_acc){'acc'=>'Sudah ACC','perlu_perbaikan_lagi'=>'Perlu Perbaikan Lagi',default=>'Menunggu ACC'} }}
                                    </x-ui.badge>
                                </td>
                                <td class="text-[12.5px] text-slate-600">{{ $rp->feedback_penguji ?? '—' }}</td>
                                <td>
                                    @php
                                        $bolehOverride = $isPengendali || ($isPenguji && Auth::id() === $rp->dosen_penguji_id);
                                    @endphp
                                    @if($bolehOverride && $rp->status_acc === 'pending')
                                        <div x-data="{ open: false }" class="flex flex-col gap-1.5">
                                            <div class="flex gap-1.5">
                                                <form action="{{ route('revisi.accPenguji', $rp->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="status_acc" value="acc">
                                                    <button type="submit" class="ui-btn ui-btn-sm ui-btn-success">ACC</button>
                                                </form>
                                                <button type="button" class="ui-btn ui-btn-sm ui-btn-outline" @click="open = !open">Minta Perbaikan</button>
                                            </div>
                                            <form x-show="open" x-cloak action="{{ route('revisi.accPenguji', $rp->id) }}" method="POST" class="flex flex-col gap-1.5">
                                                @csrf
                                                <input type="hidden" name="status_acc" value="perlu_perbaikan_lagi">
                                                <textarea name="feedback_penguji" class="ui-input" rows="2" placeholder="Catatan perbaikan yang masih diperlukan (wajib diisi)" required></textarea>
                                                <button type="submit" class="ui-btn ui-btn-sm ui-btn-danger self-start">Kirim Catatan Perbaikan</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-[12px] text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
            @endif

            {{-- Pengesahan Kaprodi — hanya muncul setelah seluruh penguji
            ACC, hanya untuk akun Kaprodi (Policy::pengesahan). --}}
            @if($revisi && $revisi->status_approval_semua && !$revisi->pengesahan_kaprodi && Auth::user()->hasRole('kaprodi'))
            <x-ui.card title="Pengesahan Kaprodi" subtitle="Seluruh dewan penguji sudah memberikan ACC — sahkan untuk memindahkan status tahapan akademik mahasiswa.">
                <form action="{{ route('revisi.pengesahanKaprodi', $revisi->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="ui-btn ui-btn-primary">Sahkan Revisi & Lanjutkan Tahap</button>
                </form>
            </x-ui.card>
            @elseif($revisi && !$revisi->status_approval_semua)
                <x-ui.alert type="info">Pengesahan Kaprodi baru bisa dilakukan setelah seluruh dewan penguji memberikan ACC.</x-ui.alert>
            @endif

        @endif
    </div>

</x-layouts.app>

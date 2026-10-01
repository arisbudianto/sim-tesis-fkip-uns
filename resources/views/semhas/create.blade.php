<x-layouts.app title="Pendaftaran Seminar Hasil — SIM-TESIS FKIP UNS">

    <div class="max-w-2xl mx-auto w-full flex flex-col gap-4">

        <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-primary-800 text-[13px] font-semibold">&larr; Kembali ke Dashboard</a>

        <x-ui.hero title="Pendaftaran Seminar Hasil (Semhas)" subtitle="FR-05 — Wajib diajukan minimal H-14 sebelum tanggal sidang." />

        <x-ui.card>
            <x-ui.info-row label="Nama Mahasiswa">{{ $tesis->mahasiswa->name }}</x-ui.info-row>
            <x-ui.info-row label="NIM">{{ $tesis->mahasiswa->identifier }}</x-ui.info-row>
            <x-ui.info-row label="Judul Tesis">{{ $tesis->judul_tesis }}</x-ui.info-row>
            <x-ui.info-row label="Pembimbing 1">{{ $tesis->pembimbing1->name ?? '(belum ditetapkan)' }}</x-ui.info-row>
            <x-ui.info-row label="Pembimbing 2">{{ $tesis->pembimbing2->name ?? '(belum ditetapkan)' }}</x-ui.info-row>
            <x-ui.info-row label="Persetujuan Revisi Sempro">
                {{-- Read-only murni — TIDAK ADA input form untuk field ini di
                     mana pun, ditarik otomatis dari data Sempro yang sudah
                     disahkan Kaprodi (lihat getStatusRevisiSempro()). --}}
                @if($revisiSempro['disahkan'])
                    <x-ui.badge color="green">{{ $revisiSempro['label'] }}</x-ui.badge>
                    <span class="text-[11px] text-slate-500 block mt-0.5">{{ optional($revisiSempro['tanggal'])->translatedFormat('d F Y') }}</span>
                @else
                    <x-ui.badge color="red">{{ $revisiSempro['label'] }}</x-ui.badge>
                @endif
            </x-ui.info-row>
            @if($tesis->pendaftaranSemhas)
                <x-ui.info-row label="Approval Naskah — Pembimbing 1">
                    <x-ui.badge color="{{ $tesis->pendaftaranSemhas->approval_pembimbing_1 ? 'green' : 'yellow' }}">
                        {{ $tesis->pendaftaranSemhas->approval_pembimbing_1 ? 'Disetujui' : 'Menunggu Persetujuan' }}
                    </x-ui.badge>
                </x-ui.info-row>
                <x-ui.info-row label="Approval Naskah — Pembimbing 2">
                    <x-ui.badge color="{{ $tesis->pendaftaranSemhas->approval_pembimbing_2 ? 'green' : 'yellow' }}">
                        {{ $tesis->pendaftaranSemhas->approval_pembimbing_2 ? 'Disetujui' : 'Menunggu Persetujuan' }}
                    </x-ui.badge>
                </x-ui.info-row>
            @endif
        </x-ui.card>

        @if($blockReason)
            <x-ui.alert type="error">&#9888; {{ $blockReason }}</x-ui.alert>
        @else
            @if($errors->any())
                <x-ui.alert type="error">
                    @foreach($errors->all() as $error) {{ $error }}<br> @endforeach
                </x-ui.alert>
            @endif

            <form action="{{ route('semhas.store', $tesis->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <x-ui.card>
                    <div class="ui-field">
                        <label for="jadwal_usulan_sidang" class="ui-label">Tanggal Usulan Sidang <span class="text-rose-600">*</span></label>
                        <input type="date" id="jadwal_usulan_sidang" name="jadwal_usulan_sidang" class="ui-input" required>
                        <p class="text-[11.5px] text-slate-500 mt-1">Wajib minimal 14 hari dari hari ini ({{ now()->addDays(14)->translatedFormat('d F Y') }} atau setelahnya).</p>
                    </div>

                    <div class="ui-field">
                        <label for="form_fpt_sh_01" class="ui-label">Permohonan Seminar Hasil Riset dan Karya Publikasi &ndash; sudah ditandatangani Pembimbing Utama (PDF, maks. 1MB) <span class="text-rose-600">*</span></label>
                        <input type="file" id="form_fpt_sh_01" name="form_fpt_sh_01" class="ui-input" accept=".pdf" required>
                        <p class="text-[11.5px] text-slate-500 mt-1">Berkas ini yang akan ditinjau Admin Prodi/Komisi Tesis/Kaprodi sebelum menyetujui pendaftaran Anda.</p>
                    </div>

                    <div class="ui-field">
                        <label for="naskah_bab_1_5" class="ui-label">Naskah Bab 1&ndash;5 (PDF, maks. 35MB) <span class="text-rose-600">*</span></label>
                        <input type="file" id="naskah_bab_1_5" name="naskah_bab_1_5" class="ui-input" accept=".pdf" required>
                    </div>

                    <div class="ui-field">
                        <label for="draf_artikel_ilmiah" class="ui-label">Draf Artikel Ilmiah &mdash; minimal 2 berkas (PDF, maks. 20MB/berkas) <span class="text-rose-600">*</span></label>
                        <input type="file" id="draf_artikel_ilmiah" name="draf_artikel_ilmiah[]" class="ui-input" accept=".pdf" multiple required>
                        <p class="text-[11.5px] text-slate-500 mt-1">Pilih minimal 2 file sekaligus (tahan Ctrl/Cmd saat memilih).</p>
                    </div>

                    <div class="ui-field">
                        <label for="bukti_status_under_review" class="ui-label">Bukti Status Under Review Jurnal/Prosiding (PDF/JPG/PNG, maks. 10MB) <span class="text-rose-600">*</span></label>
                        <input type="file" id="bukti_status_under_review" name="bukti_status_under_review" class="ui-input" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>

                    <div class="ui-field !mb-0">
                        <label for="bukti_spp" class="ui-label">Bukti Pembayaran SPP (PDF/JPG/PNG, maks. 10MB) <span class="text-rose-600">*</span></label>
                        <input type="file" id="bukti_spp" name="bukti_spp" class="ui-input" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                </x-ui.card>

                <button type="submit" class="ui-btn ui-btn-accent w-full justify-center py-3 text-[13.5px]">Ajukan Pendaftaran Semhas</button>
            </form>
        @endif

    </div>

</x-layouts.app>

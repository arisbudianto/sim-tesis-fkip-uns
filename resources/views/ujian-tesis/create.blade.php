<x-layouts.app title="Pendaftaran Ujian Tesis — SIM-TESIS FKIP UNS">

    <div class="max-w-2xl mx-auto w-full flex flex-col gap-4">

        <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-primary-800 text-[13px] font-semibold">&larr; Kembali ke Dashboard</a>

        <x-ui.hero title="Pendaftaran Ujian Tesis" subtitle="Wajib diajukan minimal H-14, dengan 8 dokumen prasyarat administrasi." />

        <x-ui.card>
            <x-ui.info-row label="Nama Mahasiswa">{{ $tesis->mahasiswa->name }}</x-ui.info-row>
            <x-ui.info-row label="NIM">{{ $tesis->mahasiswa->identifier }}</x-ui.info-row>
            <x-ui.info-row label="Judul Tesis">{{ $tesis->judul_tesis }}</x-ui.info-row>
            <x-ui.info-row label="Pembimbing 1">{{ $tesis->pembimbing1->name ?? '(belum ditetapkan)' }}</x-ui.info-row>
            <x-ui.info-row label="Pembimbing 2">{{ $tesis->pembimbing2->name ?? '(belum ditetapkan)' }}</x-ui.info-row>
            <x-ui.info-row label="Syarat #2: Bukti Lulus Semhas & Publikasi">
                {{-- Read-only murni — TIDAK ADA input form untuk field ini,
                     ditarik otomatis dari data Semhas yang sudah disahkan
                     Kaprodi (lihat getStatusLulusSemhas() di controller). --}}
                @if($statusSemhas['lulus'])
                    <x-ui.badge color="green">{{ $statusSemhas['label'] }}</x-ui.badge>
                    <span class="text-[11px] text-slate-500 block mt-0.5">{{ optional($statusSemhas['tanggal'])->translatedFormat('d F Y') }}</span>
                @else
                    <x-ui.badge color="red">{{ $statusSemhas['label'] }}</x-ui.badge>
                @endif
            </x-ui.info-row>
            @if($tesis->pendaftaranUjian)
                <x-ui.info-row label="Persetujuan Tertulis — Pembimbing 1">
                    <x-ui.badge color="{{ $tesis->pendaftaranUjian->acc_tertulis_pembimbing_1 ? 'green' : 'yellow' }}">
                        {{ $tesis->pendaftaranUjian->acc_tertulis_pembimbing_1 ? 'Disetujui' : 'Menunggu Persetujuan' }}
                    </x-ui.badge>
                </x-ui.info-row>
                <x-ui.info-row label="Persetujuan Tertulis — Pembimbing 2">
                    <x-ui.badge color="{{ $tesis->pendaftaranUjian->acc_tertulis_pembimbing_2 ? 'green' : 'yellow' }}">
                        {{ $tesis->pendaftaranUjian->acc_tertulis_pembimbing_2 ? 'Disetujui' : 'Menunggu Persetujuan' }}
                    </x-ui.badge>
                </x-ui.info-row>
            @endif
        </x-ui.card>

        @if($blockReason)
            <x-ui.alert type="error">&#9888; {{ $blockReason }}</x-ui.alert>
        @elseif(!$statusSemhas['lulus'])
            <x-ui.alert type="error">&#9888; Syarat #2 belum terpenuhi: revisi Semhas Anda belum disahkan Kaprodi. Pendaftaran Ujian Tesis belum bisa diajukan.</x-ui.alert>
        @else
            @if($errors->any())
                <x-ui.alert type="error">
                    @foreach($errors->all() as $error) {{ $error }}<br> @endforeach
                </x-ui.alert>
            @endif

            <form action="{{ route('ujian.store', $tesis->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <x-ui.card>
                    <div class="ui-field">
                        <label for="jadwal_usulan_sidang" class="ui-label">Tanggal Usulan Sidang <span class="text-rose-600">*</span></label>
                        <input type="date" id="jadwal_usulan_sidang" name="jadwal_usulan_sidang" class="ui-input" required
                               value="{{ old('jadwal_usulan_sidang', now()->addDays(15)->format('Y-m-d')) }}">
                        <p class="text-[11.5px] text-slate-500 mt-1">Wajib minimal 14 hari dari hari ini ({{ now()->addDays(14)->translatedFormat('d F Y') }} atau setelahnya).</p>
                    </div>

                    <div class="ui-field">
                        <label for="naskah_tesis_lengkap" class="ui-label">1. Naskah Tesis Lengkap (PDF, maks. 50MB) <span class="text-rose-600">*</span></label>
                        <input type="file" id="naskah_tesis_lengkap" name="naskah_tesis_lengkap" class="ui-input" accept=".pdf" required>
                    </div>

                    <div class="ui-field">
                        <label for="artikel_jurnal" class="ui-label">3. Bukti Artikel Jurnal — Sinta 1/2 / Internasional (PDF, maks. 20MB) <span class="text-rose-600">*</span></label>
                        <input type="file" id="artikel_jurnal" name="artikel_jurnal" class="ui-input" accept=".pdf" required>
                    </div>

                    <div class="ui-field">
                        <label for="prosiding_seminar" class="ui-label">4. Bukti Seminar Internasional/Prosiding & Sertifikat (PDF, maks. 20MB) <span class="text-rose-600">*</span></label>
                        <input type="file" id="prosiding_seminar" name="prosiding_seminar" class="ui-input" accept=".pdf" required>
                    </div>

                    <div class="ui-field">
                        <label class="ui-label">5. Bukti Penguasaan Bahasa Inggris <span class="text-rose-600">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="jenis_skor_bahasa" class="ui-input" required>
                                <option value="TOEFL">TOEFL (minimal 475)</option>
                                <option value="EAP">EAP (minimal 65)</option>
                            </select>
                            <input type="number" name="skor_bahasa" class="ui-input" placeholder="Skor" required min="0" max="1000" value="{{ old('skor_bahasa', 500) }}">
                        </div>
                        <input type="file" id="sertifikat_bahasa" name="sertifikat_bahasa" class="ui-input mt-2" accept=".pdf,.jpg,.jpeg,.png" required>
                        <p class="text-[11.5px] text-slate-500 mt-1">Hard constraint: skor TOEFL wajib &ge; 475, ATAU skor EAP wajib &ge; 65 — bergantung jenis tes yang dipilih.</p>
                    </div>

                    <div class="ui-field">
                        <label for="bukti_spp_terakhir" class="ui-label">6. Bukti Pembayaran SPP Terakhir (PDF/JPG/PNG, maks. 10MB) <span class="text-rose-600">*</span></label>
                        <input type="file" id="bukti_spp_terakhir" name="bukti_spp_terakhir" class="ui-input" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>

                    <div class="ui-field">
                        <label for="khs_kumulatif" class="ui-label">7. Kartu Hasil Studi / KHS Kumulatif (PDF, maks. 10MB) <span class="text-rose-600">*</span></label>
                        <input type="file" id="khs_kumulatif" name="khs_kumulatif" class="ui-input" accept=".pdf" required>
                        <p class="text-[11.5px] text-slate-500 mt-1">Pastikan seluruh SKS mata kuliah wajib sudah terpenuhi (sinkron SIAKAD belum tersedia — verifikasi manual oleh Admin Prodi).</p>
                    </div>

                    <div class="ui-field !mb-0">
                        <label for="surat_bebas_plagiasi" class="ui-label">8. Surat Bebas Plagiasi (PDF, maks. 10MB) <span class="text-rose-600">*</span></label>
                        <input type="file" id="surat_bebas_plagiasi" name="surat_bebas_plagiasi" class="ui-input" accept=".pdf" required>
                        <div class="mt-2">
                            <label class="ui-label">Similarity Score (%) <span class="text-rose-600">*</span></label>
                            <input type="number" step="0.01" name="similarity_score" class="ui-input" required min="0" max="100" value="{{ old('similarity_score', 18.5) }}">
                            <p class="text-[11.5px] text-slate-500 mt-1">Hard constraint: wajib &le; 25%.</p>
                        </div>
                    </div>
                </x-ui.card>

                <button type="submit" class="ui-btn ui-btn-accent w-full justify-center py-3 text-[13.5px]">Ajukan Pendaftaran Ujian Tesis</button>
            </form>
        @endif

    </div>

</x-layouts.app>

<x-layouts.app title="Pendaftaran Seminar Proposal — SIM-TESIS FKIP UNS">

    <div class="max-w-2xl mx-auto w-full flex flex-col gap-4" x-data="{
        step: 1,
        totalSteps: 3,
        jadwal: '',
        minDate: '{{ now()->addDays(14)->format('Y-m-d') }}',
        get sisaHari() {
            if (!this.jadwal) return null;
            const d = new Date(this.jadwal + 'T00:00:00');
            const now = new Date(); now.setHours(0,0,0,0);
            return Math.round((d - now) / 86400000);
        },
        get h14Ok() { return this.sisaHari !== null && this.sisaHari >= 14; }
    }">

        <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-primary-800 text-[13px] font-semibold">&larr; Kembali ke Dashboard</a>

        <x-ui.hero title="Pendaftaran Seminar Proposal (Sempro)" subtitle="Wajib diajukan minimal H-14 sebelum tanggal sidang." />

        <x-ui.card>
            <x-ui.info-row label="Nama Mahasiswa">{{ $tesis->mahasiswa->name }}</x-ui.info-row>
            <x-ui.info-row label="NIM">{{ $tesis->mahasiswa->identifier }}</x-ui.info-row>
            <x-ui.info-row label="Judul Tesis">{{ $tesis->judul_tesis }}</x-ui.info-row>
            <x-ui.info-row label="Pembimbing 1">{{ $tesis->pembimbing1->name ?? '(belum ditetapkan)' }}</x-ui.info-row>
            <x-ui.info-row label="Pembimbing 2">{{ $tesis->pembimbing2->name ?? '(belum ditetapkan)' }}</x-ui.info-row>
        </x-ui.card>

        @if($blockReason)
            <x-ui.alert type="error">&#9888; {{ $blockReason }}</x-ui.alert>
        @else
            @if($errors->any())
                <x-ui.alert type="error">
                    @foreach($errors->all() as $error) {{ $error }}<br> @endforeach
                </x-ui.alert>
            @endif

            {{-- Progress bar --}}
            <div class="flex items-center gap-2 px-1">
                <template x-for="s in totalSteps" :key="s">
                    <div class="flex-1 h-1.5 rounded-full transition-colors" :class="s <= step ? 'bg-primary-600' : 'bg-slate-200'"></div>
                </template>
            </div>
            <p class="text-[12px] text-slate-500 -mt-2">Langkah <span x-text="step"></span> dari <span x-text="totalSteps"></span></p>

            <form action="{{ route('sempro.store', $tesis->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Step 1: Jadwal & H-14 --}}
                <div x-show="step === 1" x-cloak>
                    <x-ui.card title="1. Jadwal Usulan Sidang">
                        <div class="ui-field">
                            <label for="jadwal_usulan_sidang" class="ui-label">Tanggal Usulan Sidang <span class="text-rose-600">*</span></label>
                            <input type="date" id="jadwal_usulan_sidang" name="jadwal_usulan_sidang" class="ui-input"
                                   x-model="jadwal" :min="minDate" required>
                            <p class="text-[11.5px] text-slate-500 mt-1">
                                Minimal H-14: <strong>{{ now()->addDays(14)->translatedFormat('d F Y') }}</strong> atau setelahnya.
                            </p>
                            <template x-if="jadwal">
                                <p class="text-[12px] mt-2 font-semibold"
                                   :class="h14Ok ? 'text-emerald-600' : 'text-rose-600'">
                                    <span x-text="h14Ok ? '✓ Memenuhi H-14 (' + sisaHari + ' hari lagi)' : '✗ Kurang dari 14 hari — pilih tanggal lain'"></span>
                                </p>
                            </template>
                        </div>
                    </x-ui.card>
                    <button type="button" class="ui-btn ui-btn-accent w-full justify-center py-3 mt-3"
                            :disabled="!h14Ok" @click="step = 2">Lanjut: Upload Dokumen →</button>
                </div>

                {{-- Step 2: Upload dokumen --}}
                <div x-show="step === 2" x-cloak>
                    <x-ui.card title="2. Upload Dokumen Wajib">
                        <div class="ui-field">
                            <label for="form_fpt_ti_01" class="ui-label">Form FPT-TI-01 (PDF, maks. 1MB) <span class="text-rose-600">*</span></label>
                            <input type="file" id="form_fpt_ti_01" name="form_fpt_ti_01" class="ui-input" accept=".pdf" required>
                        </div>
                        <div class="ui-field">
                            <label for="naskah_proposal" class="ui-label">Naskah Proposal (PDF, maks. 35MB) <span class="text-rose-600">*</span></label>
                            <input type="file" id="naskah_proposal" name="naskah_proposal" class="ui-input" accept=".pdf" required>
                        </div>
                        <div class="ui-field">
                            <label for="bukti_spp" class="ui-label">Bukti SPP (PDF/JPG/PNG, maks. 10MB) <span class="text-rose-600">*</span></label>
                            <input type="file" id="bukti_spp" name="bukti_spp" class="ui-input" accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                        <div class="ui-field !mb-0">
                            <label for="khs" class="ui-label">KHS (PDF/JPG/PNG, maks. 10MB) <span class="text-rose-600">*</span></label>
                            <input type="file" id="khs" name="khs" class="ui-input" accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                    </x-ui.card>
                    <div class="flex gap-2 mt-3">
                        <button type="button" class="ui-btn ui-btn-ghost flex-1 justify-center py-3" @click="step = 1">← Kembali</button>
                        <button type="button" class="ui-btn ui-btn-accent flex-1 justify-center py-3" @click="step = 3">Lanjut: Review →</button>
                    </div>
                </div>

                {{-- Step 3: Review & submit --}}
                <div x-show="step === 3" x-cloak>
                    <x-ui.card title="3. Review & Kirim">
                        <p class="text-[13px] text-slate-600 mb-3">Pastikan tanggal sidang dan seluruh berkas sudah benar sebelum mengajukan.</p>
                        <ul class="text-[13px] space-y-1.5 text-slate-700 list-disc list-inside">
                            <li>Tanggal usulan: <strong x-text="jadwal || '-'"></strong>
                                <span x-show="h14Ok" class="text-emerald-600 text-[12px]">(H-14 terpenuhi)</span>
                            </li>
                            <li>Form FPT-TI-01, Naskah Proposal, Bukti SPP, dan KHS sudah dipilih.</li>
                            <li>Setelah diajukan, Admin Prodi akan memverifikasi berkas Anda.</li>
                        </ul>
                    </x-ui.card>
                    <div class="flex gap-2 mt-3">
                        <button type="button" class="ui-btn ui-btn-ghost flex-1 justify-center py-3" @click="step = 2">← Kembali</button>
                        <button type="submit" class="ui-btn ui-btn-accent flex-1 justify-center py-3">Ajukan Pendaftaran Sempro</button>
                    </div>
                </div>
            </form>
        @endif

    </div>

</x-layouts.app>

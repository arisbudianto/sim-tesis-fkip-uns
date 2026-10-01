<x-layouts.app title="Edit Pengajuan Tesis — SIM-TESIS FKIP UNS">

    <div class="max-w-2xl mx-auto w-full flex flex-col gap-4">

        <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-primary-800 text-[13px] font-semibold">&larr; Kembali ke Dashboard</a>

        <x-ui.hero title="Edit Pengajuan Tesis" :subtitle="($pengajuan->mahasiswa->name ?? '-') . ' · ' . ($pengajuan->mahasiswa->identifier ?? '-')" />

        @if($errors->any())
            <x-ui.alert type="error">
                @foreach($errors->all() as $error) {{ $error }}<br> @endforeach
            </x-ui.alert>
        @endif

        <form action="{{ route('pengajuan.update', $pengajuan->id) }}" method="POST">
            @csrf
            @method('PUT')

            <x-ui.card>
                <div class="text-[12px] font-bold uppercase tracking-wider text-slate-500 mb-4 pb-2 border-b border-slate-100">Judul &amp; Fokus Tesis</div>

                <div class="ui-field">
                    <label for="judul_tesis" class="ui-label">Judul Tesis <span class="text-rose-600">*</span></label>
                    <input type="text" id="judul_tesis" name="judul_tesis" class="ui-input" value="{{ old('judul_tesis', $pengajuan->judul_tesis) }}" required>
                </div>

                <div class="ui-field">
                    <label for="bidang_fokus" class="ui-label">Bidang Fokus <span class="text-rose-600">*</span></label>
                    <input type="text" id="bidang_fokus" name="bidang_fokus" class="ui-input" value="{{ old('bidang_fokus', $pengajuan->bidang_fokus) }}" required>
                </div>

                <div class="ui-field !mb-0">
                    <label for="abstrak_rencana" class="ui-label">Abstrak Rencana</label>
                    <textarea id="abstrak_rencana" name="abstrak_rencana" class="ui-input" rows="4">{{ old('abstrak_rencana', $pengajuan->abstrak_rencana) }}</textarea>
                </div>

                <div class="text-[12px] font-bold uppercase tracking-wider text-slate-500 mt-6 mb-4 pb-2 border-b border-slate-100">Pembimbing 1 &amp; 2</div>

                @if($canEditPembimbing)
                    <div class="ui-field">
                        <label for="pembimbing_1_id" class="ui-label">Pembimbing 1 (Spesialis Studi)</label>
                        <select id="pembimbing_1_id" name="pembimbing_1_id" class="ui-input">
                            <option value="">-- Belum Dipilih --</option>
                            @foreach($dosens as $d)
                                <option value="{{ $d->id }}" @selected(old('pembimbing_1_id', $pengajuan->pembimbing_1_id) === $d->id)>
                                    {{ $d->name }} (Kuota Maks: {{ $d->kuota_bimbingan_maks }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ui-field">
                        <label for="pembimbing_2_id" class="ui-label">Pembimbing 2 (Spesialis Kependidikan)</label>
                        <select id="pembimbing_2_id" name="pembimbing_2_id" class="ui-input">
                            <option value="">-- Belum Dipilih --</option>
                            @foreach($dosens as $d)
                                <option value="{{ $d->id }}" @selected(old('pembimbing_2_id', $pengajuan->pembimbing_2_id) === $d->id)>
                                    {{ $d->name }} (Bidang: {{ $d->bidang_keahlian ?? 'Kependidikan' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ui-field">
                        <label for="nomor_sk_pembimbing" class="ui-label">Nomor SK Dekan</label>
                        <input type="text" id="nomor_sk_pembimbing" name="nomor_sk_pembimbing" class="ui-input"
                               value="{{ old('nomor_sk_pembimbing', $pengajuan->nomor_sk_pembimbing ?? 'SK/'.date('Y').'/FKIP-UNS') }}">
                    </div>

                    <div class="ui-field !mb-0">
                        <label for="tanggal_sk_pembimbing" class="ui-label">Tanggal Penetapan SK</label>
                        <input type="date" id="tanggal_sk_pembimbing" name="tanggal_sk_pembimbing" class="ui-input"
                               value="{{ old('tanggal_sk_pembimbing', optional($pengajuan->tanggal_sk_pembimbing)->format('Y-m-d') ?? date('Y-m-d')) }}">
                        <p class="text-[11.5px] text-slate-500 mt-1">Isi Pembimbing 1, Pembimbing 2, Nomor SK, dan Tanggal SK sekaligus kalau ingin menetapkan/mengubah pembimbing. Kosongkan semua kalau belum mau diisi.</p>
                    </div>
                @else
                    {{-- Read-only untuk mahasiswa: cuma bisa lihat, tidak bisa mengedit.
                         Penetapan pembimbing adalah wewenang Komisi Tesis / Kaprodi / Admin Prodi. --}}
                    <x-ui.info-row label="Pembimbing 1">
                        @if($pengajuan->pembimbing1)
                            <span class="text-emerald-700 font-semibold">{{ $pengajuan->pembimbing1->name }}</span>
                        @else
                            <x-ui.badge color="yellow">Belum Ditentukan</x-ui.badge>
                        @endif
                    </x-ui.info-row>
                    <x-ui.info-row label="Pembimbing 2">
                        @if($pengajuan->pembimbing2)
                            <span class="text-emerald-700 font-semibold">{{ $pengajuan->pembimbing2->name }}</span>
                        @else
                            <x-ui.badge color="yellow">Belum Ditentukan</x-ui.badge>
                        @endif
                    </x-ui.info-row>
                    @if($pengajuan->nomor_sk_pembimbing)
                    <x-ui.info-row label="Nomor SK Dekan">{{ $pengajuan->nomor_sk_pembimbing }}</x-ui.info-row>
                    @endif
                    <p class="text-[11.5px] text-slate-500 mt-3">Pembimbing ditetapkan oleh Komisi Tesis / Kaprodi, mahasiswa tidak dapat mengubahnya di sini.</p>
                @endif
            </x-ui.card>

            <button type="submit" class="ui-btn ui-btn-accent w-full justify-center py-3 text-[13.5px]">Simpan Perubahan</button>
        </form>

    </div>

</x-layouts.app>

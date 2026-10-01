<div class="grid md:grid-cols-2 gap-4 items-start">

    @php $role = Auth::user()->role ?? null; @endphp

    @if($role === 'mahasiswa')
        {{-- ═══════════════════════════════════════════════════════════
             MAHASISWA: Ajukan judul + usulan 2 pembimbing + upload FPT-TI-00
             ═══════════════════════════════════════════════════════════ --}}
        @if(!$myPengajuan)
        <x-ui.card title="Ajukan Judul Tesis (FR-01)">
            <form action="{{ route('pengajuan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="ui-field">
                    <label class="ui-label">Judul Tesis <span class="text-rose-600">*</span></label>
                    <textarea name="judul_tesis" class="ui-input" rows="3" required placeholder="Masukkan usulan judul penelitian tesis...">{{ old('judul_tesis') }}</textarea>
                </div>
                <div class="ui-field">
                    <label class="ui-label">Bidang Fokus Keahlian <span class="text-rose-600">*</span></label>
                    <input type="text" name="bidang_fokus" class="ui-input" required placeholder="Contoh: Media Pembelajaran Vokasi" value="{{ old('bidang_fokus') }}">
                </div>

                <div class="ui-field">
                    <label class="ui-label">Usulan Pembimbing 1 (Spesialis Bidang Studi) <span class="text-rose-600">*</span></label>
                    <select name="usulan_pembimbing_1_id" class="ui-input" required>
                        <option value="">-- Pilih Dosen --</option>
                        @foreach($dosens as $d)
                            <option value="{{ $d->id }}" @selected(old('usulan_pembimbing_1_id') == $d->id)>
                                {{ $d->name }} @if($d->bidang_keahlian)(Bidang: {{ $d->bidang_keahlian }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ui-field">
                    <label class="ui-label">Usulan Pembimbing 2 (Spesialis Kependidikan) <span class="text-rose-600">*</span></label>
                    <select name="usulan_pembimbing_2_id" class="ui-input" required>
                        <option value="">-- Pilih Dosen --</option>
                        @foreach($dosens as $d)
                            <option value="{{ $d->id }}" @selected(old('usulan_pembimbing_2_id') == $d->id)>
                                {{ $d->name }} @if($d->bidang_keahlian)(Bidang: {{ $d->bidang_keahlian }})@endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11.5px] text-slate-500 mt-1">Usulan ini dapat disetujui atau ditolak oleh Komisi Tesis.</p>
                </div>

                <div class="ui-field">
                    <label class="ui-label">Formulir FPT-TI-00 (Permohonan Proposal dan Pembimbing) — PDF <span class="text-rose-600">*</span></label>
                    <input type="file" name="form_fpt_ti_00" class="ui-input" accept=".pdf,application/pdf" required>
                    <p class="text-[11.5px] text-slate-500 mt-1">Unggah hasil scan/PDF formulir resmi FPT-TI-00 yang sudah diisi. Maks. 5 MB.</p>
                </div>

                <button type="submit" class="ui-btn ui-btn-primary">Submit Usulan Judul & Calon Pembimbing</button>
            </form>
        </x-ui.card>
        @else
        <x-ui.card title="Judul Tesis Saya">
            <x-ui.info-row label="Judul">{{ $myPengajuan->judul_tesis }}</x-ui.info-row>
            <x-ui.info-row label="Bidang Fokus">{{ $myPengajuan->bidang_fokus }}</x-ui.info-row>
            @if($myPengajuan->form_fpt_ti_00_url)
            <x-ui.info-row label="Form FPT-TI-00">
                <a href="{{ \App\Http\Controllers\BerkasController::url($myPengajuan->form_fpt_ti_00_url) }}" target="_blank" class="text-primary-600 hover:underline text-[13px]">Unduh / Lihat PDF</a>
            </x-ui.info-row>
            @endif
        </x-ui.card>
        @endif

        <x-ui.card title="Status Usulan & Pembimbing">
            @if($myPengajuan)
                <x-ui.info-row label="Status Usulan">
                    @php $st = $myPengajuan->status_usulan_pembimbing; @endphp
                    @if($st === 'pending')
                        <x-ui.badge color="yellow">Menunggu Keputusan Komisi Tesis</x-ui.badge>
                    @elseif($st === 'approved')
                        <x-ui.badge color="green">Disetujui</x-ui.badge>
                    @elseif($st === 'rejected')
                        <x-ui.badge color="red">Ditolak</x-ui.badge>
                    @else
                        <x-ui.badge color="yellow">Belum Ada Usulan</x-ui.badge>
                    @endif
                </x-ui.info-row>

                <x-ui.info-row label="Usulan Pembimbing 1">
                    {{ $myPengajuan->usulanPembimbing1->name ?? '—' }}
                </x-ui.info-row>
                <x-ui.info-row label="Usulan Pembimbing 2">
                    {{ $myPengajuan->usulanPembimbing2->name ?? '—' }}
                </x-ui.info-row>

                @if($st === 'rejected' && $myPengajuan->catatan_komisi_usulan)
                    <div class="rounded-lg bg-rose-50 border border-rose-200 p-3 my-2 text-[13px] text-rose-800">
                        <strong>Catatan Komisi:</strong> {{ $myPengajuan->catatan_komisi_usulan }}
                    </div>
                @endif

                <hr class="my-3 border-slate-100">

                <x-ui.info-row label="Pembimbing 1 (Resmi)">
                    @if($myPengajuan->pembimbing1)
                        <span class="text-emerald-700 font-semibold">{{ $myPengajuan->pembimbing1->name }}</span>
                    @else
                        <x-ui.badge color="yellow">Belum Ditentukan</x-ui.badge>
                    @endif
                </x-ui.info-row>
                <x-ui.info-row label="Pembimbing 2 (Resmi)">
                    @if($myPengajuan->pembimbing2)
                        <span class="text-emerald-700 font-semibold">{{ $myPengajuan->pembimbing2->name }}</span>
                    @else
                        <x-ui.badge color="yellow">Belum Ditentukan</x-ui.badge>
                    @endif
                </x-ui.info-row>

                {{-- Form resubmit jika ditolak --}}
                @if($st === 'rejected')
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <p class="text-[13px] font-semibold text-slate-700 mb-2">Ajukan Ulang Usulan Pembimbing</p>
                    <form action="{{ route('pengajuan.resubmitUsulan', $myPengajuan->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                        @csrf
                        <div class="ui-field !mb-1">
                            <label class="ui-label">Usulan Pembimbing 1</label>
                            <select name="usulan_pembimbing_1_id" class="ui-input" required>
                                @foreach($dosens as $d)
                                    <option value="{{ $d->id }}" @selected($myPengajuan->usulan_pembimbing_1_id == $d->id)>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ui-field !mb-1">
                            <label class="ui-label">Usulan Pembimbing 2</label>
                            <select name="usulan_pembimbing_2_id" class="ui-input" required>
                                @foreach($dosens as $d)
                                    <option value="{{ $d->id }}" @selected($myPengajuan->usulan_pembimbing_2_id == $d->id)>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ui-field !mb-1">
                            <label class="ui-label">Form FPT-TI-00 (PDF, opsional jika tidak diubah)</label>
                            <input type="file" name="form_fpt_ti_00" class="ui-input" accept=".pdf,application/pdf">
                        </div>
                        <button type="submit" class="ui-btn ui-btn-primary">Ajukan Ulang</button>
                    </form>
                </div>
                @endif
            @else
                <p class="text-slate-500 text-[13.5px]">Ajukan judul tesis dulu untuk melihat status pembimbing.</p>
            @endif
        </x-ui.card>

    @elseif($role === 'dosen')
        <x-ui.card title="Mahasiswa Bimbingan (Pembimbing)">
            @php
                $bimbingan = ($pengajuans ?? collect())->filter(fn($p) =>
                    $p->pembimbing_1_id === Auth::id() || $p->pembimbing_2_id === Auth::id()
                );
            @endphp
            @forelse($bimbingan as $p)
                <div class="py-2 border-b border-slate-100 last:border-0">
                    <div class="text-[13px] font-semibold">{{ $p->mahasiswa->name ?? '-' }}</div>
                    <div class="text-[12px] text-slate-500">{{ Str::limit($p->judul_tesis, 60) }}</div>
                    <x-ui.status-badge :status="$p->status_tahap" />
                </div>
            @empty
                <p class="text-slate-500 text-[13.5px]">Belum ada mahasiswa bimbingan.</p>
            @endforelse
        </x-ui.card>

    @endif
</div>

@if(in_array($role, ['komisi_tesis', 'kaprodi', 'admin_prodi']))
<div class="flex flex-col gap-4 mt-4">
    @if(in_array($role, ['komisi_tesis', 'admin_prodi']))
        @include('dashboard.tabs._master-data')
    @endif

    <x-ui.card title="Komisi Tesis: Alokasi Pembimbing 1 & 2 (FR-01)">

        <div class="overflow-x-auto mb-5 -mx-1">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Judul Tesis</th>
                        <th>Usulan Mhs (1 / 2)</th>
                        <th>Status Usulan</th>
                        <th>Pembimbing Resmi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                    <tr>
                        <td>
                            <strong>{{ $p->mahasiswa->identifier ?? '-' }}</strong><br>
                            {{ $p->mahasiswa->name ?? '-' }}
                        </td>
                        <td>
                            {{ Str::limit($p->judul_tesis, 40) }}
                            @if($p->form_fpt_ti_00_url)
                                <br><a href="{{ \App\Http\Controllers\BerkasController::url($p->form_fpt_ti_00_url) }}" target="_blank" class="text-[11px] text-primary-600 hover:underline">📄 FPT-TI-00</a>
                            @endif
                        </td>
                        <td class="text-[12px]">
                            <div>{{ $p->usulanPembimbing1->name ?? '—' }}</div>
                            <div class="text-slate-500">{{ $p->usulanPembimbing2->name ?? '—' }}</div>
                        </td>
                        <td>
                            @if($p->status_usulan_pembimbing === 'pending')
                                <x-ui.badge color="yellow">Pending</x-ui.badge>
                            @elseif($p->status_usulan_pembimbing === 'approved')
                                <x-ui.badge color="green">Disetujui</x-ui.badge>
                            @elseif($p->status_usulan_pembimbing === 'rejected')
                                <x-ui.badge color="red">Ditolak</x-ui.badge>
                            @else
                                <span class="text-slate-400 text-[12px]">—</span>
                            @endif
                        </td>
                        <td class="text-[12px]">
                            @if($p->pembimbing1)
                                <x-ui.badge color="green">{{ Str::limit($p->pembimbing1->name, 20) }}</x-ui.badge>
                            @else
                                <x-ui.badge color="yellow">Belum</x-ui.badge>
                            @endif
                            <br>
                            @if($p->pembimbing2)
                                <x-ui.badge color="green">{{ Str::limit($p->pembimbing2->name, 20) }}</x-ui.badge>
                            @else
                                <x-ui.badge color="yellow">Belum</x-ui.badge>
                            @endif
                        </td>
                        <td>
                            <div class="flex flex-col items-start gap-1.5">
                                <button type="button" class="ui-btn ui-btn-sm ui-btn-primary"
                                    onclick="selectPengajuanForAlokasi('{{ $p->id }}', '{{ $p->usulan_pembimbing_1_id }}', '{{ $p->usulan_pembimbing_2_id }}')">
                                    {{ ($p->pembimbing1 && $p->pembimbing2) ? 'Ubah' : 'Setujui / Alokasikan' }}
                                </button>
                                @if($p->status_usulan_pembimbing === 'pending' && !($p->pembimbing1 && $p->pembimbing2))
                                <button type="button" class="ui-btn ui-btn-sm ui-btn-outline text-rose-600 border-rose-300"
                                    onclick="showTolakForm('{{ $p->id }}')">
                                    Tolak Usulan
                                </button>
                                @endif
                                @if($p->pembimbing1 && $p->pembimbing2)
                                    <a href="{{ route('dokumen.cetak', ['kode' => 'SK-PEMBIMBING', 'id' => $p->id]) }}" target="_blank" class="ui-btn ui-btn-sm ui-btn-outline">Unduh Draft SK</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-slate-500 py-6">Belum ada pengajuan tesis.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Form Tolak Usulan (disembunyikan default) --}}
        <div id="form-tolak-wrap" class="hidden mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4">
            <p class="text-[13px] font-semibold text-rose-800 mb-2">Tolak Usulan Pembimbing</p>
            <form id="form-tolak" method="POST">
                @csrf
                <div class="ui-field">
                    <label class="ui-label">Alasan penolakan (wajib, min. 10 karakter)</label>
                    <textarea name="catatan_komisi_usulan" class="ui-input" rows="3" required minlength="10" placeholder="Contoh: Bidang keahlian dosen tidak sesuai dengan topik penelitian mahasiswa."></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="ui-btn ui-btn-sm bg-rose-600 text-white hover:bg-rose-700">Konfirmasi Tolak</button>
                    <button type="button" class="ui-btn ui-btn-sm ui-btn-ghost" onclick="document.getElementById('form-tolak-wrap').classList.add('hidden')">Batal</button>
                </div>
            </form>
        </div>

        <form id="form-alokasi" method="POST">
            @csrf
            <div class="ui-field">
                <label class="ui-label">Pilih Judul Mahasiswa</label>
                <select id="select-pengajuan" class="ui-input" onchange="updateAlokasiAction(this.value)">
                    <option value="">-- Pilih Pengajuan Tesis (atau klik 'Alokasikan' di tabel atas) --</option>
                    @foreach($pengajuans as $p)
                        <option value="{{ $p->id }}"
                            data-usulan1="{{ $p->usulan_pembimbing_1_id }}"
                            data-usulan2="{{ $p->usulan_pembimbing_2_id }}">
                            {{ $p->mahasiswa->name }} - {{ Str::limit($p->judul_tesis, 40) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="ui-field">
                <label class="ui-label">Pembimbing 1 (Spesialis Studi)</label>
                <select name="pembimbing_1_id" id="select-pemb1" class="ui-input" required>
                    @foreach($dosens as $d)
                        <option value="{{ $d->id }}">{{ $d->name }} (Kuota Maks: {{ $d->kuota_bimbingan_maks }})</option>
                    @endforeach
                </select>
                <p class="text-[11.5px] text-slate-500 mt-1">Default diisi dari usulan mahasiswa; Anda boleh mengganti.</p>
            </div>
            <div class="ui-field">
                <label class="ui-label">Pembimbing 2 (Spesialis Kependidikan)</label>
                <select name="pembimbing_2_id" id="select-pemb2" class="ui-input" required>
                    @foreach($dosens as $d)
                        <option value="{{ $d->id }}">{{ $d->name }} (Bidang: {{ $d->bidang_keahlian ?? 'Kependidikan' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="ui-field">
                <label class="ui-label">Nomor SK Dekan</label>
                <input type="text" name="nomor_sk_pembimbing" class="ui-input" value="SK/{{ date('Y') }}/FKIP-UNS" required>
            </div>
            <div class="ui-field">
                <label class="ui-label">Tanggal Penetapan SK</label>
                <input type="date" name="tanggal_sk_pembimbing" class="ui-input" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="ui-field">
                <label class="ui-label">Catatan (opsional)</label>
                <input type="text" name="catatan_komisi_usulan" class="ui-input" placeholder="Mis. Usulan mahasiswa disetujui / diganti karena kuota">
            </div>
            <button type="submit" class="ui-btn ui-btn-success">Tetapkan 2 Pembimbing (Cek Kuota)</button>
        </form>
    </x-ui.card>
    </div>

    <script>
        function updateAlokasiAction(id) {
            const form = document.getElementById('form-alokasi');
            if (!id) { form.removeAttribute('action'); return; }
            form.action = '{{ url('/pengajuan') }}/' + id + '/alokasi-pembimbing';
            const opt = document.querySelector('#select-pengajuan option[value="' + id + '"]');
            if (opt) {
                const u1 = opt.getAttribute('data-usulan1');
                const u2 = opt.getAttribute('data-usulan2');
                if (u1) document.getElementById('select-pemb1').value = u1;
                if (u2) document.getElementById('select-pemb2').value = u2;
            }
        }
        function selectPengajuanForAlokasi(id, usulan1, usulan2) {
            document.getElementById('select-pengajuan').value = id;
            updateAlokasiAction(id);
            if (usulan1) document.getElementById('select-pemb1').value = usulan1;
            if (usulan2) document.getElementById('select-pemb2').value = usulan2;
            document.getElementById('form-alokasi').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        function showTolakForm(id) {
            const wrap = document.getElementById('form-tolak-wrap');
            const form = document.getElementById('form-tolak');
            form.action = '{{ url('/pengajuan') }}/' + id + '/tolak-usulan';
            wrap.classList.remove('hidden');
            wrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    </script>
</div>
@endif

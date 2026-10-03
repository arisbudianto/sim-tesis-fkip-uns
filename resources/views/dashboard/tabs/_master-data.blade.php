    @php
        $bolehReset = Auth::user()->hasAnyRole(['admin_prodi', 'komisi_tesis']);
        $bolehKelola = Auth::user()->hasAnyRole(['admin_prodi', 'kaprodi', 'komisi_tesis']);
        $peranStaf = ['dosen' => 'Dosen', 'komisi_tesis' => 'Komisi Tesis', 'admin_prodi' => 'Admin Prodi', 'kaprodi' => 'Kaprodi'];
    @endphp

    <x-ui.card title="Master Data Mahasiswa" subtitle="Kelola akun mahasiswa. Reset password default: user123.">
        @php
            $pengajuanByMhs = ($pengajuans ?? collect())->keyBy('mahasiswa_id');
        @endphp
        <div class="overflow-x-auto -mx-1" x-data="{ q: '', showAdd: false, editUser: null }">
            <div class="flex flex-col sm:flex-row gap-2.5 mb-3">
                <input type="search" x-model="q" class="ui-input flex-1" placeholder="Cari NIM atau nama...">
                @if($bolehKelola)
                <button type="button" @click="showAdd = true" class="ui-btn ui-btn-sm ui-btn-primary whitespace-nowrap">+ Tambah Mahasiswa</button>
                @endif
            </div>
            <table class="ui-table w-full table-fixed">
                <thead>
                    <tr>
                        <th class="w-[28%]">Mahasiswa</th>
                        <th class="w-[42%]">Judul / Tahap</th>
                        <th class="w-[30%]">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($mahasiswas ?? collect()) as $m)
                        @php $pj = $pengajuanByMhs->get($m->id); @endphp
                        <tr x-show="q === '' || '{{ strtolower($m->identifier.' '.$m->name) }}'.includes(q.toLowerCase())">
                            <td class="align-top">
                                <div class="font-semibold text-[13px] break-all">{{ $m->identifier }}</div>
                                <div class="text-[13px] leading-snug">{{ $m->name }}</div>
                                <div class="text-[11px] text-slate-500 break-all">{{ $m->email }}</div>
                            </td>
                            <td class="align-top">
                                @if($pj)
                                    <div class="text-[12.5px]">{{ \Illuminate\Support\Str::limit($pj->judul_tesis, 50) }}</div>
                                    <x-ui.status-badge :status="$pj->status_tahap" />
                                @else
                                    <span class="text-slate-400 text-[12.5px]">Belum mengajukan judul</span>
                                @endif
                            </td>
                            <td class="align-top">
                                <div class="flex flex-wrap gap-1.5">
                                    @if($bolehKelola)
                                    <button type="button" class="ui-btn ui-btn-sm ui-btn-outline"
                                        @click="editUser = { id: '{{ $m->id }}', name: @js($m->name), identifier: @js($m->identifier), email: @js($m->email) }">Edit</button>
                                    @endif
                                    @if($bolehReset)
                                    <form action="{{ route('pengguna.resetPassword', $m) }}" method="POST" onsubmit="return confirm('Reset password {{ $m->name }} ke user123?')">
                                        @csrf
                                        <button type="submit" class="ui-btn ui-btn-sm ui-btn-outline">Reset PW</button>
                                    </form>
                                    @endif
                                    @if($bolehKelola)
                                    <form action="{{ route('pengguna.destroy', $m) }}" method="POST" onsubmit="return confirm('Hapus akun {{ $m->name }}? Tindakan ini tidak bisa dibatalkan.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ui-btn ui-btn-sm ui-btn-danger">Hapus</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-slate-500 py-6">Belum ada akun mahasiswa.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Modal Tambah Mahasiswa --}}
            <div x-show="showAdd" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40" @click="showAdd = false"></div>
                <div class="relative bg-white rounded-2xl p-5 w-full max-w-md shadow-xl">
                    <h3 class="text-[15px] font-extrabold text-primary-900 mb-3">Tambah Akun Mahasiswa</h3>
                    <form method="POST" action="{{ route('pengguna.storeMahasiswa') }}">
                        @csrf
                        <div class="ui-field">
                            <label class="ui-label">Nama Lengkap</label>
                            <input type="text" name="name" class="ui-input" required>
                        </div>
                        <div class="ui-field">
                            <label class="ui-label">NIM</label>
                            <input type="text" name="identifier" class="ui-input" required>
                        </div>
                        <div class="ui-field">
                            <label class="ui-label">Email</label>
                            <input type="email" name="email" class="ui-input" required>
                        </div>
                        <p class="text-[11.5px] text-slate-500 mb-3">Password default setelah dibuat: <strong>user123</strong>.</p>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="showAdd = false" class="ui-btn ui-btn-sm ui-btn-outline">Batal</button>
                            <button type="submit" class="ui-btn ui-btn-sm ui-btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal Edit Mahasiswa --}}
            <div x-show="editUser" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40" @click="editUser = null"></div>
                <div class="relative bg-white rounded-2xl p-5 w-full max-w-md shadow-xl" x-show="editUser">
                    <h3 class="text-[15px] font-extrabold text-primary-900 mb-3">Edit Akun Mahasiswa</h3>
                    <template x-if="editUser">
                        <form method="POST" :action="'{{ url('/pengguna') }}/' + editUser.id">
                            @csrf @method('PUT')
                            <div class="ui-field">
                                <label class="ui-label">Nama Lengkap</label>
                                <input type="text" name="name" class="ui-input" x-model="editUser.name" required>
                            </div>
                            <div class="ui-field">
                                <label class="ui-label">NIM</label>
                                <input type="text" name="identifier" class="ui-input" x-model="editUser.identifier" required>
                            </div>
                            <div class="ui-field">
                                <label class="ui-label">Email</label>
                                <input type="email" name="email" class="ui-input" x-model="editUser.email" required>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="editUser = null" class="ui-btn ui-btn-sm ui-btn-outline">Batal</button>
                                <button type="submit" class="ui-btn ui-btn-sm ui-btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card title="Master Data Dosen & Staf" subtitle="Akun dosen, komisi, kaprodi, dan admin. Password default setelah reset: user123.">
        <div class="overflow-x-auto -mx-1" x-data="{ q2: '', showAddStaf: false, editStaf: null }">
            <div class="flex flex-col sm:flex-row gap-2.5 mb-3">
                <input type="search" x-model="q2" class="ui-input flex-1" placeholder="Cari NIP atau nama...">
                @if($bolehKelola)
                <button type="button" @click="showAddStaf = true" class="ui-btn ui-btn-sm ui-btn-primary whitespace-nowrap">+ Tambah Dosen/Staf</button>
                @endif
            </div>
            <table class="ui-table w-full table-fixed">
                <thead><tr><th class="w-[22%]">NIP</th><th class="w-[33%]">Nama</th><th class="w-[15%]">Peran</th><th class="w-[30%]">Aksi</th></tr></thead>
                <tbody>
                    @foreach(($stafs ?? $dosens ?? collect()) as $d)
                    <tr x-show="q2 === '' || '{{ strtolower(($d->identifier ?? '').' '.($d->name ?? '')) }}'.includes(q2.toLowerCase())">
                        <td class="font-semibold whitespace-nowrap">{{ $d->identifier }}</td>
                        <td>{{ $d->name }}</td>
                        <td class="text-[12px]">{{ $d->role }}</td>
                        <td class="align-top">
                            <div class="flex flex-wrap gap-1.5">
                                @if($bolehKelola)
                                <button type="button" class="ui-btn ui-btn-sm ui-btn-outline"
                                    @click="editStaf = { id: '{{ $d->id }}', name: @js($d->name), identifier: @js($d->identifier), email: @js($d->email), role: @js($d->role), bidang_keahlian: @js($d->bidang_keahlian), kuota_bimbingan_maks: @js($d->kuota_bimbingan_maks) }">Edit</button>
                                @endif
                                @if($bolehReset)
                                <form action="{{ route('pengguna.resetPassword', $d) }}" method="POST" onsubmit="return confirm('Reset password {{ $d->name }} ke user123?')">
                                    @csrf
                                    <button type="submit" class="ui-btn ui-btn-sm ui-btn-outline">Reset PW</button>
                                </form>
                                @endif
                                @if($bolehKelola)
                                <form action="{{ route('pengguna.destroy', $d) }}" method="POST" onsubmit="return confirm('Hapus akun {{ $d->name }}? Tindakan ini tidak bisa dibatalkan.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="ui-btn ui-btn-sm ui-btn-danger">Hapus</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Modal Tambah Dosen/Staf --}}
            <div x-show="showAddStaf" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40" @click="showAddStaf = false"></div>
                <div class="relative bg-white rounded-2xl p-5 w-full max-w-md shadow-xl">
                    <h3 class="text-[15px] font-extrabold text-primary-900 mb-3">Tambah Akun Dosen/Staf</h3>
                    <form method="POST" action="{{ route('pengguna.storeDosen') }}" x-data="{ role: 'dosen' }">
                        @csrf
                        <div class="ui-field">
                            <label class="ui-label">Nama Lengkap</label>
                            <input type="text" name="name" class="ui-input" required>
                        </div>
                        <div class="ui-field">
                            <label class="ui-label">NIP</label>
                            <input type="text" name="identifier" class="ui-input" required>
                        </div>
                        <div class="ui-field">
                            <label class="ui-label">Email</label>
                            <input type="email" name="email" class="ui-input" required>
                        </div>
                        <div class="ui-field">
                            <label class="ui-label">Peran</label>
                            <select name="role" class="ui-input" x-model="role">
                                @foreach($peranStaf as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3.5" x-show="role === 'dosen'">
                            <div class="ui-field">
                                <label class="ui-label">Bidang Keahlian</label>
                                <select name="bidang_keahlian" class="ui-input">
                                    <option value="studi">Bidang Studi</option>
                                    <option value="pendidikan">Bidang Pendidikan</option>
                                </select>
                            </div>
                            <div class="ui-field">
                                <label class="ui-label">Kuota Bimbingan Maks</label>
                                <input type="number" name="kuota_bimbingan_maks" class="ui-input" value="8" min="0" max="20">
                            </div>
                        </div>
                        <p class="text-[11.5px] text-slate-500 mb-3">Password default setelah dibuat: <strong>user123</strong>.</p>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="showAddStaf = false" class="ui-btn ui-btn-sm ui-btn-outline">Batal</button>
                            <button type="submit" class="ui-btn ui-btn-sm ui-btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal Edit Dosen/Staf --}}
            <div x-show="editStaf" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40" @click="editStaf = null"></div>
                <div class="relative bg-white rounded-2xl p-5 w-full max-w-md shadow-xl" x-show="editStaf">
                    <h3 class="text-[15px] font-extrabold text-primary-900 mb-3">Edit Akun Dosen/Staf</h3>
                    <template x-if="editStaf">
                        <form method="POST" :action="'{{ url('/pengguna') }}/' + editStaf.id">
                            @csrf @method('PUT')
                            <div class="ui-field">
                                <label class="ui-label">Nama Lengkap</label>
                                <input type="text" name="name" class="ui-input" x-model="editStaf.name" required>
                            </div>
                            <div class="ui-field">
                                <label class="ui-label">NIP</label>
                                <input type="text" name="identifier" class="ui-input" x-model="editStaf.identifier" required>
                            </div>
                            <div class="ui-field">
                                <label class="ui-label">Email</label>
                                <input type="email" name="email" class="ui-input" x-model="editStaf.email" required>
                            </div>
                            <div class="ui-field">
                                <label class="ui-label">Peran</label>
                                <select name="role" class="ui-input" x-model="editStaf.role">
                                    @foreach($peranStaf as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3.5" x-show="editStaf.role === 'dosen'">
                                <div class="ui-field">
                                    <label class="ui-label">Bidang Keahlian</label>
                                    <select name="bidang_keahlian" class="ui-input" x-model="editStaf.bidang_keahlian">
                                        <option value="studi">Bidang Studi</option>
                                        <option value="pendidikan">Bidang Pendidikan</option>
                                    </select>
                                </div>
                                <div class="ui-field">
                                    <label class="ui-label">Kuota Bimbingan Maks</label>
                                    <input type="number" name="kuota_bimbingan_maks" class="ui-input" x-model="editStaf.kuota_bimbingan_maks" min="0" max="20">
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="editStaf = null" class="ui-btn ui-btn-sm ui-btn-outline">Batal</button>
                                <button type="submit" class="ui-btn ui-btn-sm ui-btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </x-ui.card>

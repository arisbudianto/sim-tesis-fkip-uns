<x-layouts.app title="Profil Saya — SIM-TESIS">

    <x-ui.hero
        title="Profil Saya"
        subtitle="Perbarui data diri dan password akun Anda. NIM/NIP dan peran akun tidak dapat diubah sendiri — hubungi Komisi Tesis/Admin Prodi bila ada kekeliruan."
    />

    @if(session('success'))
        <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
    @endif
    @if($errors->any())
        <x-ui.alert type="error">
            @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
        </x-ui.alert>
    @endif

    <div class="grid lg:grid-cols-3 gap-5">

        {{-- Ringkasan akun --}}
        <x-ui.card class="lg:col-span-1 h-fit">
            <div class="flex flex-col items-center text-center gap-3 py-2">
                <span class="flex items-center justify-center h-16 w-16 rounded-full bg-slate-100 text-primary-800">
                    <x-ui.icon name="user-circle" class="h-9 w-9" />
                </span>
                <div>
                    <div class="text-[15px] font-extrabold text-primary-900">{{ $user->name }}</div>
                    <div class="text-[11.5px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">
                        {{ str_replace('_', ' ', $user->role) }}
                    </div>
                </div>
            </div>
            <div class="h-px bg-slate-200 my-1"></div>
            <x-ui.info-row label="NIM / NIP">{{ $user->identifier }}</x-ui.info-row>
            <x-ui.info-row label="Email">{{ $user->email }}</x-ui.info-row>
            @if($user->nomor_wa)
                <x-ui.info-row label="No. WhatsApp">{{ $user->nomor_wa }}</x-ui.info-row>
            @endif
        </x-ui.card>

        <div class="lg:col-span-2 flex flex-col gap-5">

            {{-- Data diri --}}
            <x-ui.card title="Data Diri" subtitle="Nama, email, dan kontak akan tampil di dokumen resmi serta notifikasi WhatsApp.">
                <form action="{{ route('profile.update') }}" method="POST" class="flex flex-col gap-3">
                    @csrf
                    @method('PUT')

                    <div class="ui-field !mb-0">
                        <label class="ui-label">Nama Lengkap {{ $user->role !== 'mahasiswa' ? '(beserta gelar)' : '' }}</label>
                        <input type="text" name="name" class="ui-input" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="grid md:grid-cols-2 gap-3">
                        <div class="ui-field !mb-0">
                            <label class="ui-label">NIM / NIP</label>
                            <input type="text" class="ui-input bg-slate-50 text-slate-400" value="{{ $user->identifier }}" disabled>
                        </div>
                        <div class="ui-field !mb-0">
                            <label class="ui-label">Email</label>
                            <input type="email" name="email" class="ui-input" value="{{ old('email', $user->email) }}" required>
                        </div>
                    </div>

                    <div class="ui-field !mb-0">
                        <label class="ui-label">Nomor WhatsApp Aktif</label>
                        <input type="text" name="nomor_wa" class="ui-input" value="{{ old('nomor_wa', $user->nomor_wa) }}" placeholder="08123456789">
                        <p class="text-[11.5px] text-slate-500 mt-1">Dipakai sistem untuk mengirim notifikasi undangan sidang & status pendaftaran lewat WhatsApp.</p>
                    </div>

                    @if($user->role === 'dosen')
                        <div class="ui-field !mb-0">
                            <label class="ui-label">Bidang Keahlian</label>
                            <select name="bidang_keahlian" class="ui-input">
                                <option value="studi" @selected(old('bidang_keahlian', $user->bidang_keahlian) === 'studi')>Spesialis Bidang Studi Kejuruan</option>
                                <option value="pendidikan" @selected(old('bidang_keahlian', $user->bidang_keahlian) === 'pendidikan')>Spesialis Metodologi & Kependidikan</option>
                            </select>
                        </div>
                    @endif

                    @if($user->role !== 'mahasiswa')
                        <div class="ui-field !mb-0">
                            <label class="ui-label">Pangkat / Golongan</label>
                            <input type="text" name="pangkat_golongan" class="ui-input" value="{{ old('pangkat_golongan', $user->pangkat_golongan) }}" placeholder="Contoh: Pembina / IV-a">
                            <p class="text-[11.5px] text-slate-500 mt-1">Dipakai sebagai sumber kolom "Pangkat Gol./Ruang" pada dokumen Surat Tugas resmi.</p>
                        </div>
                    @endif

                    <div class="pt-1">
                        <button type="submit" class="ui-btn ui-btn-primary">
                            <x-ui.icon name="pencil-square" class="h-4 w-4" />
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </x-ui.card>

            {{-- Ganti password --}}
            <x-ui.card title="Ganti Password" subtitle="Masukkan password lama untuk konfirmasi sebelum mengganti dengan yang baru.">
                <form action="{{ route('profile.updatePassword') }}" method="POST" class="flex flex-col gap-3">
                    @csrf
                    @method('PUT')

                    <div class="ui-field !mb-0">
                        <label class="ui-label">Password Lama</label>
                        <input type="password" name="current_password" class="ui-input" required placeholder="Masukkan password saat ini">
                    </div>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div class="ui-field !mb-0">
                            <label class="ui-label">Password Baru (Minimal 6 karakter)</label>
                            <input type="password" name="password" class="ui-input" required placeholder="Password baru">
                        </div>
                        <div class="ui-field !mb-0">
                            <label class="ui-label">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="ui-input" required placeholder="Ulangi password baru">
                        </div>
                    </div>
                    <div class="pt-1">
                        <button type="submit" class="ui-btn ui-btn-outline">
                            <x-ui.icon name="lock-closed" class="h-4 w-4" />
                            Ganti Password
                        </button>
                    </div>
                </form>
            </x-ui.card>

        </div>
    </div>

</x-layouts.app>

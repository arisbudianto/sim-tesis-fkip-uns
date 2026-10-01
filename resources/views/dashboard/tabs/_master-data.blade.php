    <x-ui.card title="Master Data Mahasiswa" subtitle="Kelola akun mahasiswa. Reset password default: user123.">
        @php
            $pengajuanByMhs = ($pengajuans ?? collect())->keyBy('mahasiswa_id');
            $bolehReset = Auth::user()->hasAnyRole(['admin_prodi', 'komisi_tesis']);
        @endphp
        <div class="overflow-x-auto -mx-1" x-data="{ q: '' }">
            <input type="search" x-model="q" class="ui-input mb-3" placeholder="Cari NIM atau nama...">
            <table class="ui-table w-full table-fixed">
                <thead>
                    <tr>
                        <th class="w-[28%]">Mahasiswa</th>
                        <th class="w-[52%]">Judul / Tahap</th>
                        <th class="w-[20%]">Aksi</th>
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
                            <td>
                                @if($bolehReset)
                                <form action="{{ route('pengguna.resetPassword', $m) }}" method="POST" onsubmit="return confirm('Reset password {{ $m->name }} ke user123?')">
                                    @csrf
                                    <button type="submit" class="ui-btn ui-btn-sm ui-btn-outline">Reset PW</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-slate-500 py-6">Belum ada akun mahasiswa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <x-ui.card title="Master Data Dosen & Staf" subtitle="Akun dosen, komisi, kaprodi, dan admin. Password default setelah reset: user123.">
        <div class="overflow-x-auto -mx-1" x-data="{ q2: '' }">
            <input type="search" x-model="q2" class="ui-input mb-3" placeholder="Cari NIP atau nama...">
            <table class="ui-table w-full table-fixed">
                <thead><tr><th class="w-[28%]">NIP</th><th class="w-[42%]">Nama</th><th class="w-[15%]">Peran</th><th class="w-[15%]">Aksi</th></tr></thead>
                <tbody>
                    @foreach(($stafs ?? $dosens ?? collect()) as $d)
                    <tr x-show="q2 === '' || '{{ strtolower(($d->identifier ?? '').' '.($d->name ?? '')) }}'.includes(q2.toLowerCase())">
                        <td class="font-semibold whitespace-nowrap">{{ $d->identifier }}</td>
                        <td>{{ $d->name }}</td>
                        <td class="text-[12px]">{{ $d->role }}</td>
                        <td>
                            @if($bolehReset)
                            <form action="{{ route('pengguna.resetPassword', $d) }}" method="POST" onsubmit="return confirm('Reset password {{ $d->name }} ke user123?')">
                                @csrf
                                <button type="submit" class="ui-btn ui-btn-sm ui-btn-outline">Reset PW</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>


<div class="ui-card !p-0 overflow-hidden">
    <div class="p-5 flex items-center gap-4 border-b border-slate-200">
        <div class="flex-1">
            <h2 class="text-[16px] font-extrabold tracking-tight text-primary-900">Daftar Pengajuan Tesis Berjalan</h2>
            <p class="text-[12px] text-slate-500 mt-0.5">Menampilkan {{ $pengajuans->count() }} pengajuan tesis.</p>
        </div>
    </div>

    <table class="ui-table">
        <thead>
            <tr>
                <th>NIM &amp; Mahasiswa</th>
                <th>Judul Tesis &amp; Bidang Fokus</th>
                <th>Tim Pembimbing (1 &amp; 2)</th>
                <th>Status Tahap</th>
                <th class="text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengajuans as $i => $p)
            <tr>
                <td>
                    <div class="flex items-start gap-2.5">
                        <x-ui.avatar :name="$p->mahasiswa->name ?? '-'" :seed="$i" />
                        <div>
                            <div class="text-[12.5px] font-bold text-primary-900 leading-tight">{{ $p->mahasiswa->name ?? '-' }}</div>
                            <div class="mt-0.5 text-[10.5px] font-semibold text-slate-500 font-mono">{{ $p->mahasiswa->identifier ?? '-' }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="text-[12px] font-semibold text-slate-800 leading-snug">{{ $p->judul_tesis }}</div>
                    <div class="mt-1.5 inline-block rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">{{ $p->bidang_fokus }}</div>
                </td>
                <td>
                    <div class="flex flex-col gap-1 text-[11px] text-slate-700 leading-snug">
                        <div class="flex gap-1.5"><span class="text-slate-400 font-bold">1.</span><span class="font-semibold">{{ $p->pembimbing1->name ?? 'Belum Dialokasikan' }}</span></div>
                        <div class="flex gap-1.5"><span class="text-slate-400 font-bold">2.</span><span class="font-semibold">{{ $p->pembimbing2->name ?? 'Belum Dialokasikan' }}</span></div>
                    </div>
                </td>
                <td>
                    <x-ui.status-badge :status="$p->status_tahap" />
                    <x-ui.progress-pips :status="$p->status_tahap" />
                </td>
                <td class="text-right">
                    @if(Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi']))
                        @if($p->status_tahap === 'tahap_1_bimbingan')
                            <div class="flex flex-col items-end gap-1.5">
                                <a href="{{ route('pengajuan.edit', $p->id) }}" class="ui-btn ui-btn-sm ui-btn-primary">Edit</a>
                                <form action="{{ route('pengajuan.destroy', $p->id) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus pengajuan tesis {{ $p->mahasiswa->name ?? '' }}? Tindakan ini tidak bisa dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ui-btn ui-btn-sm ui-btn-danger">Hapus</button>
                                </form>
                            </div>
                        @else
                            <span class="ui-btn-muted">Terkunci (&gt; Tahap 1)</span>
                        @endif
                    @else
                        <span class="ui-btn-muted">&mdash;</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-slate-500 py-8">Belum ada data pengajuan tesis. Silakan isi form pada tab FR-01.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

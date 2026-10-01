@php
    $filterStatus = $filterStatus ?? null;
    $judul = $judul ?? 'Daftar Mahasiswa';
    $role = Auth::user()->role ?? null;
    $list = ($pengajuans ?? collect())->when($filterStatus, fn ($c) => $c->where('status_tahap', $filterStatus));

    // Mahasiswa hanya melihat miliknya sendiri
    if ($role === 'mahasiswa') {
        $list = $list->filter(fn ($p) => $p->mahasiswa_id === Auth::id());
    }
    // Dosen: bimbingan atau penguji terkait
    if ($role === 'dosen') {
        $list = $list->filter(fn ($p) =>
            $p->pembimbing_1_id === Auth::id() || $p->pembimbing_2_id === Auth::id()
        );
    }
@endphp

<x-ui.card :title="$judul" :subtitle="$list->count() . ' mahasiswa'">
    <div class="overflow-x-auto -mx-1">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Mahasiswa</th>
                    <th>Judul Tesis</th>
                    <th>Pembimbing</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list as $p)
                <tr>
                    <td>
                        <strong>{{ $p->mahasiswa->name ?? '-' }}</strong><br>
                        <span class="text-[11px] text-slate-500">{{ $p->mahasiswa->identifier ?? '' }}</span>
                    </td>
                    <td class="text-[13px]">{{ \Illuminate\Support\Str::limit($p->judul_tesis, 55) }}</td>
                    <td class="text-[12px]">
                        <div>{{ $p->pembimbing1->name ?? '—' }}</div>
                        <div class="text-slate-500">{{ $p->pembimbing2->name ?? '—' }}</div>
                    </td>
                    <td><x-ui.status-badge :status="$p->status_tahap" /></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-slate-500 py-8">
                        <div class="flex flex-col items-center gap-1">
                            <span class="text-[14px] font-semibold text-slate-600">Belum ada data pada tahap ini</span>
                            <span class="text-[12px]">Mahasiswa yang mencapai tahap ini akan muncul di sini.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

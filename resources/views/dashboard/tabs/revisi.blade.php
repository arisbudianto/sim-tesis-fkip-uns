<x-ui.card title="Yudisium & Revisi Final" subtitle="Mahasiswa yang sudah menyelesaikan ujian tesis.">
    @php
        $lulus = ($pengajuans ?? collect())->where('status_tahap', 'selesai_yudisium');
    @endphp
    <div class="overflow-x-auto -mx-1">
        <table class="ui-table">
            <thead><tr><th>Mahasiswa</th><th>Judul</th><th>Pembimbing</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($lulus as $p)
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
                <tr><td colspan="4" class="text-center text-slate-500 py-8">Belum ada mahasiswa yudisium.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

<x-layouts.app title="Daftar Pengajuan Tesis — SIM-TESIS FKIP UNS">

    <div class="max-w-4xl mx-auto w-full flex flex-col gap-4">

        <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-primary-800 text-[13px] font-semibold">&larr; Kembali ke Dashboard</a>

        <x-ui.hero title="Daftar Pengajuan Tesis" subtitle="Seluruh pengajuan judul &amp; status penetapan pembimbing (FR-01)." />

        <x-ui.card>
            <div class="overflow-x-auto -mx-1">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Judul Tesis</th>
                            <th>Pembimbing 1</th>
                            <th>Pembimbing 2</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuans as $p)
                        <tr>
                            <td><strong>{{ $p->mahasiswa->identifier ?? '-' }}</strong><br>{{ $p->mahasiswa->name ?? '-' }}</td>
                            <td>{{ Str::limit($p->judul_tesis, 50) }}</td>
                            <td>{{ $p->pembimbing1->name ?? '-' }}</td>
                            <td>{{ $p->pembimbing2->name ?? '-' }}</td>
                            <td><x-ui.status-badge :status="$p->status_tahap" /></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-slate-500 py-6">Belum ada pengajuan tesis.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

    </div>

</x-layouts.app>

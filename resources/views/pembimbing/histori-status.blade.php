<x-layouts.app title="Histori Status — SIM-TESIS FKIP UNS">

    <div class="max-w-2xl mx-auto w-full flex flex-col gap-4">

        <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-primary-800 text-[13px] font-semibold">&larr; Kembali ke Dashboard</a>

        <x-ui.hero title="Histori Transisi Status" :subtitle="($pengajuan->mahasiswa->name ?? '-') . ' · ' . ($pengajuan->mahasiswa->identifier ?? '-')" />

        <x-ui.card title="Status Saat Ini">
            <x-ui.status-badge :status="$pengajuan->status_tahap" />
        </x-ui.card>

        <x-ui.card title="Riwayat Transisi (State Transition Log)" subtitle="Diurutkan dari yang terbaru. Baris berwarna oranye adalah rollback/koreksi manual oleh Komisi Tesis.">
            <div class="overflow-x-auto -mx-1">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Dari</th>
                            <th>Ke</th>
                            <th>Aktor</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuan->stateTransitionLogs as $log)
                        <tr class="{{ $log->is_override ? 'bg-amber-50/60' : '' }}">
                            <td class="whitespace-nowrap">{{ $log->created_at->translatedFormat('d M Y, H:i') }}</td>
                            <td><x-ui.status-badge :status="$log->from_state" /></td>
                            <td><x-ui.status-badge :status="$log->to_state" /></td>
                            <td>
                                {{ $log->actor_name ?? '(sistem)' }}
                                @if($log->actor_role)
                                    <div class="text-[10.5px] text-slate-500">{{ strtoupper($log->actor_role) }}</div>
                                @endif
                            </td>
                            <td>
                                @if($log->is_override)
                                    <x-ui.badge color="yellow">Rollback/Override</x-ui.badge>
                                    <div class="text-[11.5px] text-slate-600 mt-1">{{ $log->override_reason }}</div>
                                @else
                                    <span class="text-[11.5px] text-slate-500">Transisi normal</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-slate-500 py-6">Belum ada histori transisi tercatat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

    </div>

</x-layouts.app>

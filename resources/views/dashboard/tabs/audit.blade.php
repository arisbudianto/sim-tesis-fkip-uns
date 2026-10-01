<x-ui.card title="Audit & Transisi Tahap" subtitle="20 catatan terakhir. Hanya terlihat oleh Komisi Tesis, Kaprodi, dan Admin Prodi.">
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <h4 class="text-[13px] font-extrabold text-primary-900 mb-2">Log aksi</h4>
            <div class="overflow-x-auto -mx-1 max-h-72 overflow-y-auto">
                <table class="ui-table">
                    <thead><tr><th>Waktu</th><th>Aktor</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse(($auditLogs ?? collect()) as $log)
                        <tr>
                            <td class="text-[11px] whitespace-nowrap">{{ optional($log->created_at)->format('d/m H:i') }}</td>
                            <td class="text-[12px]">{{ $log->actor_name }}<br><span class="text-slate-400">{{ $log->actor_role }}</span></td>
                            <td class="text-[12px]">{{ $log->action }}<br><span class="text-slate-500">{{ \Illuminate\Support\Str::limit($log->description, 80) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-slate-400 py-4">Belum ada audit log.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>
            <h4 class="text-[13px] font-extrabold text-primary-900 mb-2">Transisi tahap</h4>
            <div class="overflow-x-auto -mx-1 max-h-72 overflow-y-auto">
                <table class="ui-table">
                    <thead><tr><th>Waktu</th><th>Dari → Ke</th><th>Aktor</th></tr></thead>
                    <tbody>
                        @forelse(($stateLogs ?? collect()) as $log)
                        <tr>
                            <td class="text-[11px] whitespace-nowrap">{{ optional($log->created_at)->format('d/m H:i') }}</td>
                            <td class="text-[12px]">{{ $log->from_state }} → {{ $log->to_state }}
                                @if($log->is_override)<x-ui.badge color="yellow">override</x-ui.badge>@endif
                            </td>
                            <td class="text-[12px]">{{ $log->actor_name }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-slate-400 py-4">Belum ada transisi tercatat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-ui.card>

<x-layouts.app title="Kuota Bimbingan Dosen — SIM-TESIS FKIP UNS">

    <div class="max-w-3xl mx-auto w-full flex flex-col gap-4">

        <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-primary-800 text-[13px] font-semibold">&larr; Kembali ke Dashboard</a>

        <x-ui.hero title="Kepakaran &amp; Sisa Kuota Bimbingan Dosen" subtitle="Modul 5 — dipakai Komisi Tesis sebelum menetapkan Pembimbing 1 &amp; 2. Kuota maksimum default 8 mahasiswa aktif per dosen." />

        <x-ui.card>
            <div class="overflow-x-auto -mx-1">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th>Nama Dosen</th>
                            <th>NIP</th>
                            <th>Bidang Keahlian</th>
                            <th>Kuota Terpakai / Maksimum</th>
                            <th>Sisa Kuota</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dosens as $d)
                        <tr>
                            <td><strong>{{ $d['name'] }}</strong></td>
                            <td>{{ $d['identifier'] }}</td>
                            <td>{{ $d['bidang_keahlian'] ?? '-' }}</td>
                            <td>{{ $d['kuota_terpakai'] }} / {{ $d['kuota_maksimum'] }}</td>
                            <td>
                                @if($d['sisa_kuota'] > 0)
                                    <x-ui.badge color="green">{{ $d['sisa_kuota'] }} slot tersedia</x-ui.badge>
                                @else
                                    <x-ui.badge color="red">Penuh</x-ui.badge>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-slate-500 py-6">Belum ada dosen terdaftar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

    </div>

</x-layouts.app>

@php
    $role = Auth::user()->role ?? null;
    $bolehBlast = in_array($role, ['komisi_tesis', 'kaprodi', 'admin_prodi'], true);
    $sidangList = ($sidangs ?? collect())->sortByDesc('waktu_mulai');
    $waSiap = filled(config('whatsapp.url')) && filled(config('whatsapp.token'));
@endphp

@if($bolehBlast)
<x-ui.card title="Notifikasi WA Blast" subtitle="Kirim undangan menguji ke dewan penguji dan/atau pengumuman jadwal ke mahasiswa melalui WhatsApp.">
    @unless($waSiap)
        <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-[12.5px] text-amber-900">
            Gateway WhatsApp belum dikonfigurasi. Untuk Wablas isi di <code>.env</code>:
            <code>WHATSAPP_API_URL=https://deu.wablas.com/api/send-message</code>,
            <code>WHATSAPP_API_TOKEN</code> (Device → Settings),
            <code>WHATSAPP_API_SECRET</code> (secret key device), lalu <code>php artisan config:clear</code>.
        </div>
    @endunless
    @if(session('wa_blast_hasil'))
        <div class="mb-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-[12.5px]">
            <div class="font-semibold text-primary-900 mb-1">Hasil pengiriman terakhir</div>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach(session('wa_blast_hasil') as $h)
                    <li>
                        {{ $h['nama'] }} ({{ $h['peran'] }})
                        — {{ $h['nomor'] ?: 'nomor WA kosong' }}
                        — <strong>{{ $h['status'] }}</strong>
                        @if(!empty($h['error'])) <span class="text-rose-600">{{ $h['error'] }}</span> @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('notifikasi.waBlast') }}" method="POST" class="grid md:grid-cols-3 gap-3 items-end">
        @csrf
        <div class="ui-field !mb-0 md:col-span-2">
            <label class="ui-label">Sidang tujuan</label>
            <select name="sidang_id" class="ui-input" required>
                <option value="">— Pilih mahasiswa / tahap —</option>
                @forelse($sidangList as $s)
                    <option value="{{ $s->id }}">
                        {{ $s->pengajuanTesis->mahasiswa->name ?? 'Mahasiswa' }}
                        — {{ strtoupper($s->tahap_sidang) }}
                        — {{ optional($s->waktu_mulai)->translatedFormat('d M Y H:i') ?? 'jadwal belum diisi' }}
                    </option>
                @empty
                    <option value="" disabled>Belum ada sidang terplot</option>
                @endforelse
            </select>
        </div>
        <div class="ui-field !mb-0">
            <label class="ui-label">Penerima</label>
            <select name="target" class="ui-input" required>
                <option value="semua">Penguji + Mahasiswa</option>
                <option value="penguji">Dewan Penguji saja</option>
                <option value="mahasiswa">Mahasiswa saja</option>
            </select>
        </div>
        <div class="md:col-span-3 flex flex-wrap gap-2">
            <button type="submit" class="ui-btn ui-btn-primary">Kirim WA Blast</button>
        </div>
    </form>
</x-ui.card>
@endif

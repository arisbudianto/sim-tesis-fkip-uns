@php
    $role = Auth::user()->role ?? null;
    $bolehBlast = in_array($role, ['komisi_tesis', 'kaprodi', 'admin_prodi'], true);
    $sidangList = ($sidangs ?? collect())->sortByDesc('waktu_mulai');
    $kanalAktif = \App\Domain\Notifikasi\Services\WhatsAppNotifierService::channelAktif();
    $pakaiEmail = in_array('email', $kanalAktif, true);
    $pakaiWa = in_array('whatsapp', $kanalAktif, true);
    $waSiap = filled(config('whatsapp.url')) && filled(config('whatsapp.token'));
    $emailSiap = !in_array(config('mail.default'), ['log', 'array'], true);
    $labelKanal = collect([$pakaiEmail ? 'Email' : null, $pakaiWa ? 'WhatsApp' : null])->filter()->implode(' + ');
@endphp

@if($bolehBlast)
<x-ui.card :title="'Kirim Notifikasi (' . $labelKanal . ')'" :subtitle="'Kirim undangan menguji ke dewan penguji dan/atau pengumuman jadwal ke mahasiswa melalui ' . $labelKanal . '.'">
    @if($pakaiWa && !$waSiap)
        <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-[12.5px] text-amber-900">
            Gateway WhatsApp belum dikonfigurasi. Untuk Wablas isi di <code>.env</code>:
            <code>WHATSAPP_API_URL=https://deu.wablas.com/api/send-message</code>,
            <code>WHATSAPP_API_TOKEN</code> (Device → Settings),
            <code>WHATSAPP_API_SECRET</code> (secret key device), lalu <code>php artisan config:clear</code>.
        </div>
    @endif
    @if($pakaiEmail && !$emailSiap)
        <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-[12.5px] text-amber-900">
            Pengiriman email belum dikonfigurasi (<code>MAIL_MAILER</code> masih <code>{{ config('mail.default') }}</code>). Isi di <code>.env</code>:
            <code>MAIL_MAILER=smtp</code>, <code>MAIL_HOST</code>, <code>MAIL_PORT</code>,
            <code>MAIL_USERNAME</code>, <code>MAIL_PASSWORD</code>, <code>MAIL_FROM_ADDRESS</code>,
            lalu <code>php artisan config:clear</code>.
        </div>
    @endif
    @if(session('wa_blast_hasil'))
        <div class="mb-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-[12.5px]">
            <div class="font-semibold text-primary-900 mb-1">Hasil pengiriman terakhir</div>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach(session('wa_blast_hasil') as $h)
                    <li>
                        {{ $h['nama'] }} ({{ $h['peran'] }})
                        — <span class="font-semibold">{{ $h['kanal'] ?? '' }}</span>: {{ ($h['tujuan'] ?? '') ?: 'tujuan kosong' }}
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
            <button type="submit" class="ui-btn ui-btn-primary">Kirim Notifikasi</button>
        </div>
    </form>
</x-ui.card>
@endif

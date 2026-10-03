@extends('pdf.layout')

@php
    // Desain surat ini sengaja dibuat meniru persis format undangan resmi
    // FKIP UNS yang dipakai sehari-hari (kop kuning-biru, blok Nomor/
    // Lampiran/Hal sejajar titik dua, tanggal di kanan atas, QR langsung di
    // bawah "Ketua Program Studi") — bukan lagi format generik dokumen
    // sistem. Lihat $kopAsset/$tampilkanJudul/$qrDitaruhDiKonten yang
    // diatur dari DocumentGeneratorService khusus untuk kode UNDANGAN-*.
    $sidang = $record; // AktivitasSidang (tahap_sidang = sempro ATAU semhas)
    $tesis = $sidang->pengajuanTesis;
    $pengujiList = $sidang->pengujiSidangs;

    $nomorUrutTesis = $sidang->tahap_sidang === 'semhas' ? '2' : '1';
    $labelAcara = $sidang->tahap_sidang === 'semhas'
        ? "Ujian Tesis {$nomorUrutTesis} (Seminar Hasil)"
        : "Ujian Tesis {$nomorUrutTesis} (Seminar dan Ujian Proposal)";

    // Ekstrak Zoom Meeting ID & Passcode dari link_zoom (format umum:
    // https://zoom.us/j/{meeting_id}?pwd={passcode}) — kalau link tidak
    // mengikuti pola ini, tampilkan link mentahnya saja sebagai fallback.
    $zoomId = null;
    $zoomPasscode = null;
    if ($sidang->link_zoom) {
        if (preg_match('/\/j\/(\d+)/', $sidang->link_zoom, $m)) $zoomId = $m[1];
        if (preg_match('/[?&]pwd=([^&]+)/', $sidang->link_zoom, $m)) $zoomPasscode = $m[1];
    }
@endphp

@section('konten')
@foreach($pengujiList as $i => $tujuan)
<div @if($i > 0) style="page-break-before: always; padding-top: 20px;" @endif>

<table style="border:none; width:100%;">
<tr>
    <td style="width:62%; border:none; vertical-align:top; padding:0;">
        <table style="border:none; width:100%;">
        <tr><td style="width:66px; border:none; padding:1px 0;">Nomor</td><td style="width:12px; border:none; padding:1px 0;">:</td><td style="border:none; padding:1px 0;">{{ $nomorDokumen ?? '................................' }}</td></tr>
        <tr><td style="border:none; padding:1px 0;">Lampiran</td><td style="border:none; padding:1px 0;">:</td><td style="border:none; padding:1px 0;">1 Bendel</td></tr>
        <tr><td style="border:none; padding:1px 0; vertical-align:top;">Hal</td><td style="border:none; padding:1px 0; vertical-align:top;">:</td><td style="border:none; padding:1px 0;">Undangan Menguji {{ $labelAcara }}<br>Mahasiswa a.n {{ $tesis->mahasiswa->name }}</td></tr>
        </table>
    </td>
    <td style="width:38%; border:none; text-align:right; vertical-align:top; padding:0;">
        {{ $dicetakAt->translatedFormat('d F Y') }}
    </td>
</tr>
</table>

<p style="margin-top:14px;">
    Yth. {{ $tujuan->dosen->name ?? '..........................' }}<br>
    S2 Pendidikan Guru Vokasi<br>
    Fakultas Keguruan dan Ilmu Pendidikan<br>
    Universitas Sebelas Maret<br>
    Surakarta
</p>

<p>Mengharap kehadiran Bapak/Ibu Dosen pada :</p>
<table class="content-table" style="width:auto; margin-left:20px; border:none;">
<tr><td style="width:100px; border:none; padding:1px 0; vertical-align:top;">Hari/tanggal</td><td style="width:12px; border:none; padding:1px 0; vertical-align:top;">:</td><td style="border:none; padding:1px 0;">{{ $sidang->waktu_mulai->translatedFormat('l, d F Y') }}</td></tr>
<tr><td style="border:none; padding:1px 0;">Waktu</td><td style="border:none; padding:1px 0;">:</td><td style="border:none; padding:1px 0;">{{ str_replace(':', '.', $sidang->waktu_mulai->format('H:i')) }} &ndash; {{ str_replace(':', '.', $sidang->waktu_selesai->format('H:i')) }} WIB</td></tr>
<tr><td style="border:none; padding:1px 0;">Tempat</td><td style="border:none; padding:1px 0;">:</td><td style="border:none; padding:1px 0; font-style:italic;">{{ $sidang->link_zoom ? 'Online' : ($sidang->ruangan ?? '-') }}</td></tr>
@if($sidang->link_zoom)
<tr><td style="border:none; padding:1px 0;">Media</td><td style="border:none; padding:1px 0;">:</td><td style="border:none; padding:1px 0; font-style:italic;">Zoom Meeting</td></tr>
@if($zoomId)
<tr><td style="border:none; padding:1px 0;"></td><td style="border:none; padding:1px 0;"></td><td style="border:none; padding:1px 0;">Meeting ID {{ $zoomId }}</td></tr>
@endif
@if($zoomPasscode)
<tr><td style="border:none; padding:1px 0;"></td><td style="border:none; padding:1px 0;"></td><td style="border:none; padding:1px 0;">Passcode {{ $zoomPasscode }}</td></tr>
@endif
@endif
<tr><td style="border:none; padding:6px 0 1px 0; vertical-align:top;">Agenda</td><td style="border:none; padding:6px 0 1px 0; vertical-align:top;">:</td><td style="border:none; padding:6px 0 1px 0;">
    Menguji {{ $labelAcara }} mahasiswa :
    <table class="content-table" style="width:auto; margin-top:4px; border:none;">
    <tr><td style="width:90px; border:none; padding:1px 0;">Nama</td><td style="width:12px; border:none; padding:1px 0;">:</td><td style="border:none; padding:1px 0;">{{ $tesis->mahasiswa->name }}</td></tr>
    <tr><td style="border:none; padding:1px 0;">NIM</td><td style="border:none; padding:1px 0;">:</td><td style="border:none; padding:1px 0;">{{ $tesis->mahasiswa->identifier }}</td></tr>
    <tr><td style="border:none; padding:1px 0; vertical-align:top;">Judul Tesis {{ $nomorUrutTesis }}</td><td style="border:none; padding:1px 0; vertical-align:top;">:</td><td style="border:none; padding:1px 0;">{{ $tesis->judul_tesis }}</td></tr>
    </table>
</td></tr>
</table>

<p style="margin-top:10px;">Dengan susunan Tim Penguji sebagai berikut :</p>
<table class="content-table" style="width:auto; margin-left:20px; border:none;">
@foreach($pengujiList as $j => $p)
<tr><td style="width:20px; border:none; padding:1px 0;">{{ $j + 1 }}.</td><td style="border:none; padding:1px 0;">{{ $p->dosen->name ?? '-' }}</td></tr>
@endforeach
</table>

<p style="margin-top:16px;">Atas perhatian dan kehadirannya, diucapkan terima kasih</p>

<table style="margin-top:16px; border:none;">
<tr>
    <td style="width:55%; border:none;">&nbsp;</td>
    <td style="width:45%; vertical-align:top; border:none;">
        Ketua Program Studi<br>
        @if($qrBase64)
            <img src="data:image/png;base64,{{ $qrBase64 }}" alt="QR Verifikasi" style="width:68px; height:68px; margin:6px 0 4px 0;">
        @else
            <br><br><br>
        @endif
        <br>
        Abdul Haris Setiawan, S.Pd., M.Pd., Ph.D.<br>
        NIP. 198003242005011002
    </td>
</tr>
</table>

</div>
@endforeach
@endsection

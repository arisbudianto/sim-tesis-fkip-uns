@extends('pdf.layout')

@php
    $sidang = $record; // AktivitasSidang (tahap_sidang = sempro ATAU semhas)
    $tesis = $sidang->pengajuanTesis;
    $pengujiList = $sidang->pengujiSidangs;

    $labelAcara = $sidang->tahap_sidang === 'semhas'
        ? 'Ujian Tesis 2 (Seminar Hasil)'
        : 'Ujian Tesis 1 (Seminar dan Ujian Proposal)';

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

<table>
<tr>
    <td style="width:70%; border:none;">
        Nomor : {{ $nomorDokumen ?? '................................' }}<br>
        Lampiran : 1 Bendel<br>
        Hal : Undangan Menguji {{ $labelAcara }}<br>
        Mahasiswa a.n {{ $tesis->mahasiswa->name }}
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
<table class="content-table" style="width:auto; margin-left:20px;">
<tr><td style="width:110px; border:none;">Hari/tanggal</td><td style="border:none;">: {{ $sidang->waktu_mulai->translatedFormat('l, d F Y') }}</td></tr>
<tr><td style="border:none;">Waktu</td><td style="border:none;">: {{ $sidang->waktu_mulai->format('H:i') }} &ndash; {{ $sidang->waktu_selesai->format('H:i') }} WIB</td></tr>
<tr><td style="border:none;">Tempat</td><td style="border:none;">: {{ $sidang->ruangan ?? '-' }}</td></tr>
@if($sidang->link_zoom)
<tr><td style="border:none;">Media</td><td style="border:none;">: Zoom Meeting</td></tr>
<tr><td style="border:none;">Link</td><td style="border:none;">: {{ $sidang->link_zoom }}</td></tr>
@if($zoomId)
<tr><td style="border:none;">Meeting ID</td><td style="border:none;">: {{ $zoomId }}</td></tr>
@endif
@if($zoomPasscode)
<tr><td style="border:none;">Passcode</td><td style="border:none;">: {{ $zoomPasscode }}</td></tr>
@endif
@endif
</table>

<p style="margin-top:10px;">Agenda : Menguji {{ $labelAcara }} mahasiswa :</p>
<table class="content-table" style="width:auto; margin-left:20px;">
<tr><td style="width:110px; border:none;">Nama</td><td style="border:none;">: {{ $tesis->mahasiswa->name }}</td></tr>
<tr><td style="border:none;">NIM</td><td style="border:none;">: {{ $tesis->mahasiswa->identifier }}</td></tr>
<tr><td style="border:none; vertical-align:top;">Judul Tesis</td><td style="border:none;">: {{ $tesis->judul_tesis }}</td></tr>
</table>

<p style="margin-top:10px;">Dengan susunan Tim Penguji sebagai berikut :</p>
<table class="content-table" style="width:auto; margin-left:20px;">
@foreach($pengujiList as $j => $p)
<tr><td style="width:20px; border:none;">{{ $j + 1 }}.</td><td style="border:none;">{{ $p->dosen->name ?? '-' }}</td></tr>
@endforeach
</table>

<p style="margin-top:16px;">Atas perhatian dan kehadirannya, diucapkan terima kasih</p>

<table style="margin-top:16px;">
<tr>
    <td style="width:55%;">&nbsp;</td>
    <td style="width:45%; vertical-align:top;">
        Ketua Program Studi<br><br><br><br>
        <span style="border-top:1px solid #333; display:inline-block; padding-top:3px;">Abdul Haris Setiawan, S.Pd., M.Pd., Ph.D.</span><br>
        NIP. 198003242005011002
    </td>
</tr>
</table>

</div>
@endforeach
@endsection

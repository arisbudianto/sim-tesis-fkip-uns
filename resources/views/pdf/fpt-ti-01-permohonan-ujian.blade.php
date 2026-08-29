@extends('pdf.layout')

@php
    $sidang = $record; // AktivitasSidang
    $tesis = $sidang->pengajuanTesis;
    $labelTahap = match($sidang->tahap_sidang) {
        'sempro' => 'Ujian Tesis 1 (Seminar Proposal)',
        'semhas' => 'Ujian Tesis 2 (Seminar Hasil)',
        'ujian'  => 'Ujian Tesis Akhir',
        default  => $sidang->tahap_sidang,
    };
@endphp

@section('konten')
<div class="form-subtitle">Permohonan {{ $labelTahap }}</div>

<fieldset-title>Data Mahasiswa &amp; Judul Tesis</fieldset-title>
<table class="content-table">
<tr><th style="width:30%">Nama Lengkap</th><td>{{ $tesis->mahasiswa->name }}</td></tr>
<tr><th>NIM</th><td>{{ $tesis->mahasiswa->identifier }}</td></tr>
<tr><th>Judul Tesis</th><td>{{ $tesis->judul_tesis }}</td></tr>
<tr><th>Bidang Fokus</th><td>{{ $tesis->bidang_fokus }}</td></tr>
</table>

<fieldset-title>Jadwal Sidang</fieldset-title>
<table class="content-table">
<tr><th style="width:30%">Tanggal &amp; Waktu</th><td>{{ $sidang->waktu_mulai->translatedFormat('l, d F Y') }} &middot; {{ $sidang->waktu_mulai->format('H:i') }}&ndash;{{ $sidang->waktu_selesai->format('H:i') }} WIB</td></tr>
<tr><th>Ruangan</th><td>{{ $sidang->ruangan ?? '-' }}</td></tr>
<tr><th>Link Zoom</th><td>{{ $sidang->link_zoom ?? '-' }}</td></tr>
</table>

<fieldset-title>Persetujuan Digital Pembimbing</fieldset-title>
<table class="content-table">
<tr><th style="width:30%">Pembimbing 1</th><td>{{ $tesis->pembimbing1->name ?? '-' }}</td></tr>
<tr><th>Pembimbing 2</th><td>{{ $tesis->pembimbing2->name ?? '-' }}</td></tr>
</table>

<table class="signature-table">
<tr>
    <td style="width:33%"><div class="line">{{ $tesis->mahasiswa->name }}<br>Mahasiswa Pengaju</div></td>
    <td style="width:33%"><div class="line">{{ $tesis->pembimbing1->name ?? '-' }}<br>Pembimbing 1</div></td>
    <td style="width:33%"><div class="line">{{ $tesis->pembimbing2->name ?? '-' }}<br>Pembimbing 2</div></td>
</tr>
</table>
@endsection

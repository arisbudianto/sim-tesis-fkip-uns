@extends('pdf.layout')

@php
    $nilai = $record; // ManajemenNilaiSidang
    $sidang = $nilai->sidang;
    $tesis = $sidang->pengajuanTesis;
@endphp

@section('konten')
<div class="form-subtitle">Berita Acara &mdash; Tahap {{ ucfirst($sidang->tahap_sidang) }}</div>

<p>
Pada hari ini, {{ $sidang->waktu_mulai->translatedFormat('l, d F Y') }}, pukul {{ $sidang->waktu_mulai->format('H:i') }}&ndash;{{ $sidang->waktu_selesai->format('H:i') }} WIB,
bertempat di {{ $sidang->ruangan ?? $sidang->link_zoom ?? '-' }}, telah dilaksanakan sidang
<b>{{ ucfirst($sidang->tahap_sidang) }}</b> atas nama mahasiswa berikut:
</p>

<table class="content-table">
<tr><th style="width:30%">Nama / NIM</th><td>{{ $tesis->mahasiswa->name }} / {{ $tesis->mahasiswa->identifier }}</td></tr>
<tr><th>Judul Tesis</th><td>{{ $tesis->judul_tesis }}</td></tr>
<tr><th>Keputusan Sidang</th><td><b>{{ str_replace('_', ' ', ucfirst($nilai->keputusan_sidang)) }}</b> &mdash; Grade {{ $nilai->grade_kelulusan }}</td></tr>
</table>

<fieldset-title>Dewan Penguji Hadir</fieldset-title>
<table class="content-table">
<tr><th>Nama</th><th>Peran</th><th>Kehadiran</th></tr>
@foreach($sidang->pengujiSidangs as $pi)
<tr>
    <td>{{ $pi->dosen->name }}</td>
    <td>{{ str_replace('_', ' ', ucfirst($pi->peran_penguji)) }}</td>
    <td>{{ $pi->presensi_kehadiran ? 'Hadir' : 'Tidak Hadir' }}</td>
</tr>
@endforeach
</table>

<p>Demikian Berita Acara ini dibuat untuk digunakan sebagaimana mestinya.</p>

<table class="signature-table">
<tr><td style="width:100%"><div class="line">{{ $sidang->komisiTesis->name ?? '-' }}<br>Komisi Tesis</div></td></tr>
</table>
@endsection

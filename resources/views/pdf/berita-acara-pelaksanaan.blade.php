@extends('pdf.layout')

@php
    $nilai = $record; // ManajemenNilaiSidang
    $sidang = $nilai->sidang;
    $tesis = $sidang->pengajuanTesis;
@endphp

@section('konten')
<p>
Berita Acara Pelaksanaan (BAP) ini menyatakan bahwa telah dilaksanakan sidang <b>{{ ucfirst($sidang->tahap_sidang) }}</b>
pada {{ $sidang->waktu_mulai->translatedFormat('l, d F Y') }} pukul {{ $sidang->waktu_mulai->format('H:i') }} WIB
bertempat di {{ $sidang->ruangan ?? $sidang->link_zoom ?? '-' }}, dengan hasil sebagai berikut:
</p>

<table class="content-table">
<tr><th style="width:30%">Nama / NIM Mahasiswa</th><td>{{ $tesis->mahasiswa->name }} / {{ $tesis->mahasiswa->identifier }}</td></tr>
<tr><th>Judul Tesis</th><td>{{ $tesis->judul_tesis }}</td></tr>
<tr><th>Nilai Rata-rata</th><td><b>{{ $nilai->nilai_rata_rata }}</b></td></tr>
<tr><th>Grade Kelulusan</th><td><b>{{ $nilai->grade_kelulusan }}</b></td></tr>
<tr><th>Keputusan Sidang</th><td>{{ str_replace('_', ' ', ucfirst($nilai->keputusan_sidang)) }}</td></tr>
@if($nilai->batas_waktu_revisi)
<tr><th>Batas Waktu Revisi</th><td>{{ $nilai->batas_waktu_revisi->translatedFormat('d F Y') }}</td></tr>
@endif
</table>

<fieldset-title>Dewan Penguji</fieldset-title>
<table class="content-table">
<tr><th>Nama</th><th>Peran</th><th>Nilai Total</th></tr>
@foreach($sidang->pengujiSidangs as $pi)
<tr>
    <td>{{ $pi->dosen->name }}</td>
    <td>{{ str_replace('_', ' ', ucfirst($pi->peran_penguji)) }}</td>
    <td>{{ $pi->nilai_total_angka ?? '-' }}</td>
</tr>
@endforeach
</table>

<p>Berita Acara ini sah tanpa memerlukan tanda tangan basah, dibuktikan lewat stempel QR TTE di bawah.</p>

<table class="signature-table">
<tr><td style="width:100%"><div class="line">{{ $nilai->validator->name ?? '-' }}<br>Komisi Tesis (Validator BAP)</div></td></tr>
</table>
@endsection

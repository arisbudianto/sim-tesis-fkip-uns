@extends('pdf.layout')

@php
    $sidang = $record; // AktivitasSidang
    $tesis = $sidang->pengajuanTesis;
@endphp

@section('konten')
<div class="form-subtitle">Daftar Hadir Dewan Penguji &mdash; Tahap {{ ucfirst($sidang->tahap_sidang) }}</div>

<table class="content-table">
<tr><th style="width:30%">Nama / NIM Mahasiswa</th><td>{{ $tesis->mahasiswa->name }} / {{ $tesis->mahasiswa->identifier }}</td></tr>
<tr><th>Tanggal &amp; Waktu Sidang</th><td>{{ $sidang->waktu_mulai->translatedFormat('d F Y, H:i') }} WIB</td></tr>
</table>

<table class="content-table">
<tr><th style="width:8%">No</th><th>Nama Penguji</th><th>Peran</th><th style="width:20%">Tanda Tangan</th></tr>
@foreach($sidang->pengujiSidangs as $i => $pi)
<tr>
    <td>{{ $i + 1 }}</td>
    <td>{{ $pi->dosen->name }}</td>
    <td>{{ str_replace('_', ' ', ucfirst($pi->peran_penguji)) }}</td>
    <td>&nbsp;</td>
</tr>
@endforeach
</table>
@endsection

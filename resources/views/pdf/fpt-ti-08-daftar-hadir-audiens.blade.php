@extends('pdf.layout')

@php
    $sidang = $record; // AktivitasSidang
    $tesis = $sidang->pengajuanTesis;
@endphp

@section('konten')
<div class="form-subtitle">Daftar Hadir Audiens &mdash; Tahap {{ ucfirst($sidang->tahap_sidang) }}</div>

<table class="content-table">
<tr><th style="width:30%">Nama / NIM Mahasiswa</th><td>{{ $tesis->mahasiswa->name }} / {{ $tesis->mahasiswa->identifier }}</td></tr>
<tr><th>Tanggal &amp; Waktu Sidang</th><td>{{ $sidang->waktu_mulai->translatedFormat('d F Y, H:i') }} WIB</td></tr>
<tr><th>Ruangan / Zoom</th><td>{{ $sidang->ruangan ?? $sidang->link_zoom ?? '-' }}</td></tr>
</table>

<table class="content-table">
<tr><th style="width:8%">No</th><th>Nama</th><th>Instansi / Program Studi</th><th style="width:20%">Tanda Tangan</th></tr>
@for($i = 1; $i <= 12; $i++)
<tr><td>{{ $i }}</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
@endfor
</table>
@endsection

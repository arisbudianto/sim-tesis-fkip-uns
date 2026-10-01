@extends('pdf.layout')

@php
    $sidang = $record; // AktivitasSidang
    $tesis = $sidang->pengajuanTesis;
@endphp

@section('konten')
<div class="form-subtitle">Verifikasi Persyaratan Administrasi &mdash; Tahap {{ ucfirst($sidang->tahap_sidang) }}</div>

<table class="content-table">
<tr><th style="width:30%">Nama / NIM</th><td>{{ $tesis->mahasiswa->name }} / {{ $tesis->mahasiswa->identifier }}</td></tr>
<tr><th>Judul Tesis</th><td>{{ $tesis->judul_tesis }}</td></tr>
</table>

<fieldset-title>Checklist Kelengkapan</fieldset-title>
<table class="content-table">
<tr><th style="width:8%">No</th><th>Item Persyaratan</th><th style="width:15%">Status</th></tr>
<tr><td>1</td><td>Naskah lengkap disetujui pembimbing</td><td>&#9744;</td></tr>
<tr><td>2</td><td>Bukti pembayaran SPP semester berjalan</td><td>&#9744;</td></tr>
<tr><td>3</td><td>Transkrip nilai sementara</td><td>&#9744;</td></tr>
<tr><td>4</td><td>Formulir persetujuan pembimbing (FPT-TI-01)</td><td>&#9744;</td></tr>
</table>

<table class="signature-table">
<tr>
    <td style="width:50%"><div class="line">Admin Program Studi</div></td>
    <td style="width:50%"><div class="line">Komisi Tesis &mdash; {{ $sidang->komisiTesis->name ?? '-' }}</div></td>
</tr>
</table>
@endsection

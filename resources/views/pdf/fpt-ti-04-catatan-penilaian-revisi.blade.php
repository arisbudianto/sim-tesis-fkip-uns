@extends('pdf.layout')

@php
    $revisi = $record; // RevisiDokumen
    $sidang = $revisi->sidang;
    $tesis = $sidang->pengajuanTesis;
@endphp

@section('konten')
<div class="form-subtitle">Catatan Penilaian &amp; Revisi &mdash; Tahap {{ ucfirst($sidang->tahap_sidang) }}</div>

<table class="content-table">
<tr><th style="width:30%">Nama / NIM</th><td>{{ $tesis->mahasiswa->name }} / {{ $tesis->mahasiswa->identifier }}</td></tr>
<tr><th>Judul Tesis</th><td>{{ $tesis->judul_tesis }}</td></tr>
</table>

<fieldset-title>Catatan Revisi per Penguji</fieldset-title>
<table class="content-table">
<tr><th>Penguji</th><th>Catatan Revisi</th></tr>
@foreach($sidang->pengujiSidangs as $pi)
<tr>
    <td style="width:25%">{{ $pi->dosen->name }}</td>
    <td>{{ $pi->catatan_revisi ?? '(tidak ada catatan revisi)' }}</td>
</tr>
@endforeach
</table>

<fieldset-title>Naskah Revisi Final</fieldset-title>
<table class="content-table">
<tr><th style="width:30%">Tautan Naskah Revisi</th><td>{{ $revisi->naskah_revisi_final_url }}</td></tr>
<tr><th>Bukti Luaran</th><td>{{ $revisi->bukti_luaran_final_url ?? '-' }}</td></tr>
<tr><th>Status ACC Seluruh Penguji</th><td>{{ $revisi->status_approval_semua ? 'Sudah ACC semua' : 'Belum lengkap' }}</td></tr>
</table>
@endsection

@extends('pdf.layout')

@php
    $nilai = $record; // ManajemenNilaiSidang
    $sidang = $nilai->sidang;
    $tesis = $sidang->pengajuanTesis;
@endphp

@section('konten')
<div class="form-subtitle">Rekapitulasi Nilai Sidang &mdash; Tahap {{ ucfirst($sidang->tahap_sidang) }}</div>

<table class="content-table">
<tr><th style="width:30%">Nama / NIM</th><td>{{ $tesis->mahasiswa->name }} / {{ $tesis->mahasiswa->identifier }}</td></tr>
<tr><th>Judul Tesis</th><td>{{ $tesis->judul_tesis }}</td></tr>
<tr><th>Tanggal Sidang</th><td>{{ $sidang->waktu_mulai->translatedFormat('d F Y') }}</td></tr>
</table>

<fieldset-title>Hasil Rekapitulasi</fieldset-title>
<table class="content-table">
<tr><th style="width:30%">Nilai Rata-rata</th><td><b>{{ $nilai->nilai_rata_rata }}</b></td></tr>
<tr><th>Grade Kelulusan</th><td><b>{{ $nilai->grade_kelulusan }}</b></td></tr>
<tr><th>Keputusan Sidang</th><td>{{ str_replace('_', ' ', ucfirst($nilai->keputusan_sidang)) }}</td></tr>
<tr><th>Batas Waktu Revisi</th><td>{{ $nilai->batas_waktu_revisi?->translatedFormat('d F Y') ?? '-' }}</td></tr>
<tr><th>Divalidasi oleh</th><td>{{ $nilai->validator->name ?? '-' }} (Komisi Tesis)</td></tr>
</table>

<table class="signature-table">
<tr><td style="width:100%"><div class="line">{{ $nilai->validator->name ?? '-' }}<br>Komisi Tesis (Validator)</div></td></tr>
</table>
@endsection

@extends('pdf.layout')

@php
    $sidang = $record; // AktivitasSidang
    $tesis = $sidang->pengajuanTesis;
@endphp

@section('konten')
<div class="form-subtitle">Lembar Penilaian Individu Dewan Penguji &mdash; Tahap {{ ucfirst($sidang->tahap_sidang) }}</div>

<table class="content-table">
<tr><th style="width:30%">Nama / NIM</th><td>{{ $tesis->mahasiswa->name }} / {{ $tesis->mahasiswa->identifier }}</td></tr>
<tr><th>Judul Tesis</th><td>{{ $tesis->judul_tesis }}</td></tr>
</table>

<fieldset-title>Rekap Nilai per Penguji (Rubrik 4 Dimensi)</fieldset-title>
<table class="content-table">
<tr>
    <th>Penguji</th><th>Peran</th>
    <th>Naskah</th><th>Publikasi</th><th>Presentasi</th><th>Tanya Jawab</th><th>Total</th>
</tr>
@foreach($sidang->pengujiSidangs as $pi)
<tr>
    <td>{{ $pi->dosen->name }}</td>
    <td>{{ str_replace('_', ' ', ucfirst($pi->peran_penguji)) }}</td>
    <td>{{ $pi->nilai_dimensi_1_naskah ?? '-' }}</td>
    <td>{{ $pi->nilai_dimensi_2_publikasi ?? '-' }}</td>
    <td>{{ $pi->nilai_dimensi_3_presentasi ?? '-' }}</td>
    <td>{{ $pi->nilai_dimensi_4_tanyajawab ?? '-' }}</td>
    <td><b>{{ $pi->nilai_total_angka ?? '-' }}</b></td>
</tr>
@endforeach
</table>

<table class="signature-table">
<tr>
@foreach($sidang->pengujiSidangs as $pi)
    <td style="width:{{ 100 / max($sidang->pengujiSidangs->count(),1) }}%"><div class="line">{{ $pi->dosen->name }}</div></td>
@endforeach
</tr>
</table>
@endsection

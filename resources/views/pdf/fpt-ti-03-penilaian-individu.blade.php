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

<fieldset-title>Rekap Nilai per Penguji (Rubrik 10 Indikator)</fieldset-title>
<table class="content-table">
<tr>
    <th>Penguji</th><th>Peran</th>
    @for($i = 1; $i <= 10; $i++)<th title="{{ config("penilaian.label_indikator.$i") }}">I{{ $i }}</th>@endfor
    <th>Total</th>
</tr>
@foreach($sidang->pengujiSidangs as $pi)
<tr>
    <td>{{ $pi->dosen->name }}</td>
    <td>{{ str_replace('_', ' ', ucfirst($pi->peran_penguji)) }}</td>
    @for($i = 1; $i <= 10; $i++)<td>{{ $pi->{"nilai_indikator_$i"} ?? '-' }}</td>@endfor
    <td><b>{{ $pi->nilai_total_angka ?? '-' }}</b></td>
</tr>
@endforeach
</table>
<p style="font-size:8.5px; color:#666; margin-top:4px;">
    Keterangan indikator:
    @foreach(config('penilaian.label_indikator') as $i => $label)
        I{{ $i }}={{ $label }}@if($i < 10); @endif
    @endforeach
</p>

<table class="signature-table">
<tr>
@foreach($sidang->pengujiSidangs as $pi)
    <td style="width:{{ 100 / max($sidang->pengujiSidangs->count(),1) }}%"><div class="line">{{ $pi->dosen->name }}</div></td>
@endforeach
</tr>
</table>
@endsection

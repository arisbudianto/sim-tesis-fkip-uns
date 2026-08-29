@extends('pdf.layout')

@php
    $revisi = $record; // RevisiDokumen
    $sidang = $revisi->sidang;
    $tesis = $sidang->pengajuanTesis;
@endphp

@section('konten')
<div class="form-subtitle">Persetujuan Revisi Tesis &mdash; Tahap {{ ucfirst($sidang->tahap_sidang) }}</div>

<table class="content-table">
<tr><th style="width:30%">Nama / NIM</th><td>{{ $tesis->mahasiswa->name }} / {{ $tesis->mahasiswa->identifier }}</td></tr>
<tr><th>Judul Tesis</th><td>{{ $tesis->judul_tesis }}</td></tr>
</table>

<fieldset-title>Status Persetujuan per Penguji</fieldset-title>
<table class="content-table">
<tr><th>Penguji</th><th>Status ACC</th><th>Feedback</th></tr>
@foreach($revisi->revisiPengujis as $rp)
<tr>
    <td>{{ $rp->dosenPenguji->name ?? '-' }}</td>
    <td>{{ str_replace('_', ' ', ucfirst($rp->status_acc)) }}</td>
    <td>{{ $rp->feedback_penguji ?? '-' }}</td>
</tr>
@endforeach
</table>

<fieldset-title>Pengesahan Kaprodi</fieldset-title>
<table class="content-table">
<tr><th style="width:30%">Status</th><td>{{ $revisi->pengesahan_kaprodi ? 'DISAHKAN' : 'Menunggu pengesahan' }}</td></tr>
<tr><th>Tanggal Pengesahan</th><td>{{ $revisi->disahkan_kaprodi_at?->translatedFormat('d F Y, H:i') ?? '-' }}</td></tr>
</table>

<table class="signature-table">
<tr><td style="width:100%"><div class="line">Ketua Program Studi</div></td></tr>
</table>
@endsection

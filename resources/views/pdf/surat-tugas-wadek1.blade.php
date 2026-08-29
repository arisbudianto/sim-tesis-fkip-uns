@extends('pdf.layout')

@php
    $sidang = $record; // AktivitasSidang
    $tesis = $sidang->pengajuanTesis;
    $labelTahap = match($sidang->tahap_sidang) {
        'sempro' => 'Seminar Proposal',
        'semhas' => 'Seminar Hasil',
        'ujian'  => 'Ujian Tesis',
        default  => $sidang->tahap_sidang,
    };
@endphp

@section('konten')
<p style="text-align:center; margin: 4px 0 16px 0;">Nomor: {{ $nomorDokumen ?? '(belum diterbitkan)' }}</p>

<p>Wakil Dekan I Bidang Akademik Fakultas Keguruan dan Ilmu Pendidikan Universitas Sebelas Maret, dengan ini menugaskan
dosen-dosen berikut sebagai Dewan Penguji pada pelaksanaan <b>{{ $labelTahap }}</b> mahasiswa Program Studi Magister
Pendidikan Guru Vokasi:</p>

<table class="content-table">
<tr><th style="width:30%">Nama / NIM Mahasiswa</th><td>{{ $tesis->mahasiswa->name }} / {{ $tesis->mahasiswa->identifier }}</td></tr>
<tr><th>Judul Tesis</th><td>{{ $tesis->judul_tesis }}</td></tr>
<tr><th>Hari, Tanggal</th><td>{{ $sidang->waktu_mulai->translatedFormat('l, d F Y') }}</td></tr>
<tr><th>Waktu</th><td>{{ $sidang->waktu_mulai->format('H:i') }}&ndash;{{ $sidang->waktu_selesai->format('H:i') }} WIB</td></tr>
<tr><th>Tempat</th><td>{{ $sidang->ruangan ?? $sidang->link_zoom ?? '-' }}</td></tr>
</table>

<fieldset-title>Susunan Dewan Penguji</fieldset-title>
<table class="content-table">
<tr><th style="width:8%">No</th><th>Nama Dosen</th><th>Peran</th></tr>
@foreach($sidang->pengujiSidangs as $i => $pi)
<tr>
    <td>{{ $i + 1 }}</td>
    <td>{{ $pi->dosen->name }}</td>
    <td>{{ str_replace('_', ' ', ucfirst($pi->peran_penguji)) }}</td>
</tr>
@endforeach
</table>

<p>Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.</p>

<table class="signature-table">
<tr><td style="width:100%"><div class="line">Wakil Dekan I FKIP UNS</div></td></tr>
</table>
@endsection

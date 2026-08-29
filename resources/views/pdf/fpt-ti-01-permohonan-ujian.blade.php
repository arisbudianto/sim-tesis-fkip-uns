@extends('pdf.layout')

@php
    $sempro = $record; // PendaftaranSempro
    $tesis = $sempro->pengajuanTesis;
@endphp

@section('konten')
<p style="text-align:right;">Kepada Yth Kepala Program Studi S2 Pendidikan Guru Vokasi</p>
<p style="text-align:right; margin-top:-8px;">Pascasarjana Fakultas Keguruan dan Ilmu Pendidikan</p>
<p style="text-align:right; margin-top:-8px;">Universitas Sebelas Maret</p>
<p style="text-align:right; margin-top:-8px;">Surakarta</p>

<p style="margin-top:20px;">Proposal Tesis 1 (Seminar dan Ujian Proposal) dengan Judul :</p>
<p style="font-weight:bold; margin: 4px 0 16px 0;">{{ $tesis->judul_tesis }}</p>

<p>Disusun oleh :</p>
<table class="content-table" style="width:auto; margin-left:20px;">
<tr><td style="width:120px; border:none;">Nama</td><td style="border:none;">: {{ $tesis->mahasiswa->name }}</td></tr>
<tr><td style="border:none;">NIM</td><td style="border:none;">: {{ $tesis->mahasiswa->identifier }}</td></tr>
<tr><td style="border:none;">Program Studi</td><td style="border:none;">: S2 Pendidikan Guru Vokasi</td></tr>
</table>

<p style="margin-top:16px;">telah memenuhi syarat untuk dilanjutkan ke tahap seminar proposal tesis.</p>

<p>Berdasarkan kesepakatan dengan Tim pembimbing, seminar kami usulkan pada :</p>
<table class="content-table" style="width:auto; margin-left:20px;">
<tr><td style="width:120px; border:none;">Hari, tanggal</td><td style="border:none;">: {{ $sempro->jadwal_usulan_sidang->translatedFormat('l, d F Y') }}</td></tr>
<tr><td style="border:none;">Pukul</td><td style="border:none;">: {{ $sempro->jadwal_usulan_sidang->format('H:i') }} WIB</td></tr>
<tr><td style="border:none;">Tempat</td><td style="border:none;">: Ruang Sidang FKIP / Zoom Meeting (menunggu plotting Komisi Tesis)</td></tr>
</table>

<p style="margin-top:16px;">Demikian permohonan kami sampaikan. Atas perhatian dan kebijaksanaanya, kami ucapkan terima kasih.</p>

<table style="margin-top:30px;">
<tr>
    <td style="width:33%; vertical-align:top;">
        Mengetahui<br>Pembimbing Utama<br><br><br><br>
        <span style="border-top:1px solid #333; display:inline-block; padding-top:3px;">{{ $tesis->pembimbing1->name ?? '..........................' }}</span><br>
        NIP. {{ $tesis->pembimbing1->identifier ?? '..........................' }}
    </td>
    <td style="width:34%; vertical-align:top;">
        <br>Pembimbing Pendamping<br><br><br><br>
        <span style="border-top:1px solid #333; display:inline-block; padding-top:3px;">{{ $tesis->pembimbing2->name ?? '..........................' }}</span><br>
        NIP. {{ $tesis->pembimbing2->identifier ?? '..........................' }}
    </td>
    <td style="width:33%; vertical-align:top;">
        Surakarta, {{ $dicetakAt->translatedFormat('d F Y') }}<br>Mahasiswa,<br><br><br><br>
        <span style="border-top:1px solid #333; display:inline-block; padding-top:3px;">{{ $tesis->mahasiswa->name }}</span><br>
        NIM. {{ $tesis->mahasiswa->identifier }}
    </td>
</tr>
</table>
@endsection

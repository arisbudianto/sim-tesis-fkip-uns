@extends('pdf.layout')

@php
    $pengajuan = $record; // PengajuanTesis
@endphp

@section('konten')
<p style="text-align:center; font-weight:bold; text-decoration:underline; font-size:12.5px;">SURAT KEPUTUSAN</p>
<p style="text-align:center; margin-top:-6px;">Nomor : {{ $nomorDokumen ?? '................................ (DRAFT — belum resmi)' }}</p>

<p style="margin-top:16px;">TENTANG</p>
<p style="font-weight:bold;">PENETAPAN DOSEN PEMBIMBING TESIS PROGRAM STUDI S2 PENDIDIKAN GURU VOKASI</p>

<p style="margin-top:14px;">
    Dekan Fakultas Keguruan dan Ilmu Pendidikan Universitas Sebelas Maret, menetapkan Dosen Pembimbing
    Tesis untuk mahasiswa Program Studi Magister Pendidikan Guru Vokasi sebagai berikut:
</p>

<table class="content-table" style="margin-top:10px;">
<tr><th style="width:22%;">Nama Mahasiswa</th><td>{{ $pengajuan->mahasiswa->name ?? '-' }}</td></tr>
<tr><th>NIM</th><td>{{ $pengajuan->mahasiswa->identifier ?? '-' }}</td></tr>
<tr><th>Judul Tesis</th><td>{{ $pengajuan->judul_tesis }}</td></tr>
<tr><th>Bidang Fokus</th><td>{{ $pengajuan->bidang_fokus }}</td></tr>
</table>

<table class="content-table" style="margin-top:14px;">
<tr>
    <th style="width:6%;">No.</th>
    <th>Nama dan NIP Dosen</th>
    <th style="width:26%;">Peran</th>
</tr>
<tr>
    <td>1.</td>
    <td>{{ $pengajuan->pembimbing1->name ?? '-' }}<br>NIP. {{ $pengajuan->pembimbing1->identifier ?? '-' }}</td>
    <td>Pembimbing 1 (Spesialis Bidang Ilmu)</td>
</tr>
<tr>
    <td>2.</td>
    <td>{{ $pengajuan->pembimbing2->name ?? '-' }}<br>NIP. {{ $pengajuan->pembimbing2->identifier ?? '-' }}</td>
    <td>Pembimbing 2 (Spesialis Metodologi &amp; Kependidikan)</td>
</tr>
</table>

<p style="margin-top:16px;">
    Dosen Pembimbing sebagaimana tersebut di atas bertugas membimbing penyusunan tesis mahasiswa
    yang bersangkutan mulai dari penyusunan proposal, seminar hasil, hingga ujian tesis, sesuai
    ketentuan akademik yang berlaku di Program Studi S2 Pendidikan Guru Vokasi FKIP UNS.
</p>

<table style="margin-top:20px;">
<tr>
    <td style="width:55%;">&nbsp;</td>
    <td style="width:45%; vertical-align:top;">
        Surakarta, {{ ($pengajuan->tanggal_sk_pembimbing ?? $dicetakAt)->translatedFormat('d F Y') }}<br>
        a.n Dekan<br>
        Wakil Dekan Bidang Akademik dan Penelitian
        <div style="height:88px;"></div>
        <span style="border-top:1px solid #333; display:inline-block; padding-top:3px;">Prof. Dr.paed. Nurma Yunita Indriyanti, S.Pd., M.Si., M.Sc.</span><br>
        NIP. 198306262006042002
    </td>
</tr>
</table>

<p style="margin-top:20px; font-size:10px;">
    Tembusan :<br>
    1. Yth. Dekan<br>
    2. Yth. Ketua Program Studi<br>
    3. Ybs. Dosen Pembimbing 1 &amp; 2<br>
    4. Ybs. Mahasiswa<br>
    5. Arsip
</p>

@if(!$nomorDokumen)
<p style="margin-top:12px; font-size:10px; color:#b91c1c; font-weight:bold;">
    *** DOKUMEN INI ADALAH DRAFT — nomor SK resmi belum diterbitkan. Ajukan penomoran resmi ke
    Admin Program Studi sebelum digunakan sebagai dasar administratif. ***
</p>
@endif
@endsection

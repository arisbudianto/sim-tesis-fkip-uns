<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{{ $kodeDokumen }} - {{ $judul }}</title>
<style>
    /* dompdf tidak mendukung CSS grid/flexbox — layout pakai <table> */
    @page { margin: 20px 25px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a1a; }
    table { border-collapse: collapse; width: 100%; }
    .kop-table td { vertical-align: middle; }
    .kop-logo { width: 90px; text-align: center; }
    .kop-logo img { width: 82px; }
    .kop-text { padding-left: 12px; }
    .kop-text .unit-name { font-size: 12px; font-weight: bold; margin: 0 0 1px 0; }
    .kop-text .prodi-name { font-size: 11px; font-weight: bold; margin: 0 0 3px 0; }
    .kop-text p { margin: 0; font-size: 9px; line-height: 1.35; color: #333; }
    .kop-line { border-bottom: 2.5px solid #0e6ba8; margin: 6px 0 10px 0; }
    .doc-meta { font-size: 9px; color: #555; margin-bottom: 10px; }
    .doc-meta span { margin-right: 14px; }
    .form-title { text-align: center; font-size: 13px; font-weight: bold; text-decoration: underline; margin: 6px 0 2px 0; }
    .form-subtitle { text-align: center; font-size: 10px; color: #555; margin: 0 0 14px 0; }
    .content-table td, .content-table th { border: 1px solid #999; padding: 5px 7px; font-size: 10.5px; }
    .content-table th { background: #eef2f7; text-align: left; }
    fieldset-title { font-weight: bold; font-size: 11px; background: #f4f6f9; padding: 4px 6px; display: block; margin-top: 10px; border-left: 3px solid #3b82f6; }
    .signature-table td { text-align: center; padding-top: 40px; font-size: 10px; vertical-align: bottom; }
    .signature-table .line { border-top: 1px solid #333; margin: 0 15px; padding-top: 3px; }
    .footer-table { position: fixed; bottom: -10px; left: 0; right: 0; font-size: 8.5px; color: #666; }
    .qr-box { text-align: center; }
    .qr-box img { width: 78px; height: 78px; }
    .qr-caption { font-size: 7px; color: #666; word-break: break-all; width: 90px; }
</style>
</head>
<body>

@php
    $logoPath = public_path('assets/logo-uns.png');
    $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
@endphp

<table class="kop-table">
<tr>
    <td class="kop-logo">
        @if($logoBase64)
            <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo UNS">
        @endif
    </td>
    <td class="kop-text">
        <p class="unit-name">Fakultas Keguruan dan Ilmu Pendidikan</p>
        <p class="prodi-name">Program Studi Magister Pendidikan Guru Vokasi</p>
        <p>Kampus V UNS Pabelan, Jl. A. Yani No. 200 A, Pabelan, Kartasura, Sukoharjo, Jawa Tengah, Indonesia 57161</p>
        <p>Telepon: (0271) 669124</p>
        <p>fkip@fkip.uns.ac.id | fkip.uns.ac.id</p>
    </td>
</tr>
</table>
<div class="kop-line"></div>

<div class="doc-meta">
    <span><b>Kode Dokumen:</b> {{ $kodeDokumen }}</span>
    @if($nomorDokumen)
        <span><b>Nomor:</b> {{ $nomorDokumen }}</span>
    @endif
    <span><b>Dicetak:</b> {{ $dicetakAt->translatedFormat('d F Y, H:i') }} WIB</span>
    <span><b>Sistem:</b> SIM-TESIS FKIP UNS</span>
</div>

<div class="form-title">{{ strtoupper($judul) }}</div>

{{-- konten spesifik tiap dokumen --}}
@yield('konten')

<table style="margin-top: 18px;">
<tr>
    <td style="width: 78%; vertical-align: bottom;">
        &nbsp;
    </td>
    <td style="width: 22%;" class="qr-box">
        <img src="data:image/png;base64,{{ $qrBase64 }}" alt="QR Verifikasi">
        <div class="qr-caption">Scan untuk verifikasi keaslian dokumen (TTE)<br>Hash: {{ substr($hashVerifikasi, 0, 16) }}&hellip;</div>
    </td>
</tr>
</table>

</body>
</html>

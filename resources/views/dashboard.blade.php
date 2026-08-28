<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM-TESIS FKIP UNS - 4 Tahap Siklus Lengkap</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .header { background: #1e3a8a; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 4px solid #3b82f6; }
        .card h3 { margin: 0 0 10px 0; font-size: 14px; color: #64748b; text-transform: uppercase; }
        .card .count { font-size: 28px; font-weight: bold; color: #1e293b; }
        .table-container { background: white; padding: 20px; border-radius: 8px; margin-top: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; color: #475569; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-green { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">SIM-TESIS MAGISTER FKIP UNS</h1>
        <p style="margin: 5px 0 0 0;">Digitalisasi Terpadu 4 Tahap Akademik: Penetapan Pembimbing, Sempro, Semhas, Ujian Tesis & Yudisium</p>
    </div>

    <div class="grid">
        <div class="card">
            <h3>Tahap 1: Bimbingan</h3>
            <div class="count">{{ $stats['tahap_1_bimbingan'] ?? 0 }}</div>
        </div>
        <div class="card">
            <h3>Tahap 2: Sempro</h3>
            <div class="count">{{ $stats['tahap_2_sempro'] ?? 0 }}</div>
        </div>
        <div class="card">
            <h3>Tahap 3: Semhas</h3>
            <div class="count">{{ $stats['tahap_3_semhas'] ?? 0 }}</div>
        </div>
        <div class="card">
            <h3>Tahap 4: Ujian Tesis</h3>
            <div class="count">{{ $stats['tahap_4_ujian'] ?? 0 }}</div>
        </div>
        <div class="card" style="border-left-color: #10b981;">
            <h3>Lulus / Yudisium</h3>
            <div class="count">{{ $stats['selesai_yudisium'] ?? 0 }}</div>
        </div>
    </div>

    <div class="table-container">
        <h2>Matriks Fungsional Sistem (10 Functional Requirements)</h2>
        <table>
            <thead>
                <tr>
                    <th>Kode FR</th>
                    <th>Cakupan Fungsional</th>
                    <th>Modul / Tabel</th>
                    <th>Status Lifecycle</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>FR-01</td><td>Pengajuan Topik & Cek Kuota Pembimbing 1 & 2</td><td><code>pengajuan_tesis</code></td><td><span class="badge badge-blue">Tahap 1</span></td></tr>
                <tr><td>FR-02</td><td>Logbook Bimbingan Digital & Stempel QR TTE</td><td><code>logbook_bimbingans</code></td><td><span class="badge badge-blue">Tahap 1</span></td></tr>
                <tr><td>FR-03</td><td>Pendaftaran Sempro H-14 & Berkas FPT-TI-01 & 02</td><td><code>pendaftaran_sempros</code></td><td><span class="badge badge-blue">Tahap 2</span></td></tr>
                <tr><td>FR-04</td><td>Plotting Jadwal & Penguji Sempro (Anti-Bentrok)</td><td><code>aktivitas_sidangs</code></td><td><span class="badge badge-blue">Tahap 2</span></td></tr>
                <tr><td>FR-05</td><td>Pendaftaran Semhas (H-14, 2 Draf & 1 Under Review)</td><td><code>pendaftaran_semhas</code></td><td><span class="badge badge-blue">Tahap 3</span></td></tr>
                <tr><td>FR-06</td><td>Plotting Jadwal & Rekap Telaah Hasil Semhas</td><td><code>aktivitas_sidangs</code></td><td><span class="badge badge-blue">Tahap 3</span></td></tr>
                <tr><td>FR-07</td><td>Pendaftaran Ujian Tesis & 9 Berkas Administrasi</td><td><code>pendaftaran_ujians</code></td><td><span class="badge badge-blue">Tahap 4</span></td></tr>
                <tr><td>FR-08</td><td>Plotting 4 Dewan Penguji Ujian (2 Pemb, 1 Studi, 1 Pend)</td><td><code>penguji_sidangs</code></td><td><span class="badge badge-blue">Tahap 4</span></td></tr>
                <tr><td>FR-09</td><td>Rubrik 4 Dimensi Nilai, Konversi Grade & BAP</td><td><code>manajemen_nilai_sidangs</code></td><td><span class="badge badge-blue">Tahap 4</span></td></tr>
                <tr><td>FR-10</td><td>Matriks Revisi 4 Penguji, ACC Kaprodi & Yudisium</td><td><code>revisi_dokumens</code></td><td><span class="badge badge-green">Yudisium</span></td></tr>
            </tbody>
        </table>
    </div>
</body>
</html>

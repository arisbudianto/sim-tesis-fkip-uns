<?php

namespace App\Domain\Notifikasi\Services;

use App\Domain\Sidang\Models\AktivitasSidang;
use App\Models\User;
use Carbon\Carbon;

/**
 * Perakit data placeholder notifikasi sidang (undangan menguji ke dosen dan
 * jadwal ke mahasiswa), dipakai bersama oleh plotting jadwal
 * (KomisiTesisController) dan pengiriman ulang manual (WaBlastController)
 * supaya isi pesan di kedua jalur SELALU sama. Sebelumnya masing-masing
 * merakit data sendiri dan sudah mulai menyimpang (mis. fallback lokasi).
 *
 * Isi pesan sendiri tetap berasal dari template di tabel notifikasi_templates
 * (lihat NotifikasiTemplateSeeder), kelas ini hanya mengisi placeholdernya.
 */
class SidangNotifikasiData
{
    /** Urutan tampil dewan penguji di daftar "Yth." */
    private const URUTAN_PERAN = [
        'ketua_penguji', 'sekretaris_penguji', 'pembimbing_1', 'pembimbing_2',
        'penguji_studi', 'penguji_pendidikan',
    ];

    public static function untukUndangan(AktivitasSidang $sidang, User $dosen): array
    {
        $sidang->loadMissing('pengujiSidangs.dosen', 'pengajuanTesis.mahasiswa');
        $tahap = $sidang->tahap_sidang;
        $mahasiswa = $sidang->pengajuanTesis?->mahasiswa;
        [$linkSurat, $linkUndangan] = self::linkDokumenResmi($sidang);

        return array_merge(self::jadwal($sidang), [
            'nama_dosen' => $dosen->name,
            'nama_mahasiswa' => $mahasiswa?->name ?? '-',
            'nim_mahasiswa' => $mahasiswa?->identifier ?? '-',
            'tahap_sidang' => strtoupper($tahap),
            'daftar_penguji' => self::daftarPenguji($sidang),
            'link_surat_tugas' => $linkSurat,
            'link_undangan' => $linkUndangan,
            'link_naskah' => self::linkNaskah($sidang),
            'link_form_penilaian' => route('dashboard'),
            'link_kalender_ics' => route('sidang.kalenderIcs', $sidang->id),
        ]);
    }

    public static function untukMahasiswa(AktivitasSidang $sidang): array
    {
        $sidang->loadMissing('pengajuanTesis.mahasiswa');
        $mahasiswa = $sidang->pengajuanTesis?->mahasiswa;

        return array_merge(self::jadwal($sidang), [
            'nama_mahasiswa' => $mahasiswa?->name ?? '-',
            'nim_mahasiswa' => $mahasiswa?->identifier ?? '-',
            'tahap_sidang' => strtoupper($sidang->tahap_sidang),
            'link_kalender_ics' => route('sidang.kalenderIcs', $sidang->id),
        ]);
    }

    /** hari_tanggal, pukul, waktu_mulai (format lama), lokasi. */
    protected static function jadwal(AktivitasSidang $sidang): array
    {
        $mulai = $sidang->waktu_mulai ? Carbon::parse($sidang->waktu_mulai)->locale('id') : null;
        $selesai = $sidang->waktu_selesai ? Carbon::parse($sidang->waktu_selesai)->locale('id') : null;

        $pukul = '-';
        if ($mulai) {
            $pukul = $mulai->format('H.i') . ($selesai ? '-' . $selesai->format('H.i') : '') . ' WIB';
        }

        return [
            'hari_tanggal' => $mulai ? $mulai->translatedFormat('l, d F Y') : '-',
            'pukul' => $pukul,
            'waktu_mulai' => $mulai ? $mulai->translatedFormat('d F Y H:i') : '-',
            'lokasi' => self::lokasi($sidang),
        ];
    }

    protected static function lokasi(AktivitasSidang $sidang): string
    {
        $bagian = [];
        if ($sidang->ruangan) {
            $bagian[] = 'Ruang: ' . $sidang->ruangan;
        }
        if ($sidang->link_zoom) {
            $bagian[] = 'Link Zoom: ' . $sidang->link_zoom;
        }

        return $bagian ? implode("\n", $bagian) : 'Lokasi menyusul';
    }

    /**
     * Daftar bernomor seluruh dewan penguji, satu baris per orang, mis.
     * "1. Bapak/Ibu Nama, Gelar (Ketua)". Data jenis kelamin tidak tersimpan
     * di sistem, jadi sapaan memakai "Bapak/Ibu".
     */
    protected static function daftarPenguji(AktivitasSidang $sidang): string
    {
        $urutan = array_flip(self::URUTAN_PERAN);

        return $sidang->pengujiSidangs
            ->filter(fn ($ps) => $ps->dosen)
            ->sortBy(fn ($ps) => $urutan[$ps->peran_penguji] ?? 99)
            ->values()
            ->map(fn ($ps, $i) => ($i + 1) . '. Bapak/Ibu ' . $ps->dosen->name
                . ' (' . self::labelPeran($ps->peran_penguji, $sidang->tahap_sidang) . ')')
            ->implode("\n");
    }

    protected static function labelPeran(string $peran, string $tahap): string
    {
        return match ($peran) {
            'ketua_penguji' => 'Ketua',
            'sekretaris_penguji' => 'Sekretaris',
            // Sempro/Semhas: kedua pembimbing duduk sebagai Penguji 1 dan 2.
            'pembimbing_1' => $tahap === 'ujian' ? 'Pembimbing 1' : 'Penguji 1',
            'pembimbing_2' => $tahap === 'ujian' ? 'Pembimbing 2' : 'Penguji 2',
            'penguji_studi' => 'Penguji Bidang Studi',
            'penguji_pendidikan' => 'Penguji Kependidikan',
            default => ucwords(str_replace('_', ' ', $peran)),
        };
    }

    /** Link naskah mahasiswa yang relevan untuk tahap sidang ini. */
    protected static function linkNaskah(AktivitasSidang $sidang): string
    {
        $tesis = $sidang->pengajuanTesis;

        return match ($sidang->tahap_sidang) {
            'sempro' => $tesis?->pendaftaranSempro?->naskah_proposal_url ?? '(belum diunggah)',
            'semhas' => $tesis?->pendaftaranSemhas?->naskah_bab_1_5_url ?? '(belum diunggah)',
            'ujian' => $tesis?->pendaftaranUjian?->naskah_tesis_lengkap_url ?? '(belum diunggah)',
            default => '-',
        };
    }

    /**
     * [Surat Tugas, Undangan]. Sempro/Semhas memakai template generik;
     * Ujian Tesis memakai Surat Tugas Wadek I dan belum punya undangan
     * resmi tersendiri (fallback ke dashboard).
     */
    protected static function linkDokumenResmi(AktivitasSidang $sidang): array
    {
        $tahap = $sidang->tahap_sidang;

        if ($tahap === 'ujian') {
            return [
                route('dokumen.cetak', ['kode' => 'SURAT-TUGAS-WADEK1', 'id' => $sidang->id]),
                route('dashboard'),
            ];
        }

        $sufiks = $tahap === 'semhas' ? 'SEMHAS' : 'SEMPRO';

        return [
            route('dokumen.cetak', ['kode' => "SURAT-TUGAS-{$sufiks}", 'id' => $sidang->id]),
            route('dokumen.cetak', ['kode' => "UNDANGAN-{$sufiks}", 'id' => $sidang->id]),
        ];
    }
}

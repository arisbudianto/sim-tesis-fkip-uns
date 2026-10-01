<?php

namespace Database\Seeders;

use App\Domain\Notifikasi\Models\NotifikasiTemplate;
use Illuminate\Database\Seeder;

class NotifikasiTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'undangan_menguji',
                'nama_template' => 'Undangan Menguji (Dewan Penguji)',
                'deskripsi_placeholder' => '{nama_dosen} {nama_mahasiswa} {tahap_sidang} {waktu_mulai} {lokasi} {link_surat_tugas} {link_undangan} {link_naskah} {link_form_penilaian} {link_kalender_ics}',
                'body' => "Yth. {nama_dosen},\n\n"
                    . "Anda ditugaskan sebagai Dewan Penguji pada sidang {tahap_sidang} mahasiswa {nama_mahasiswa}.\n\n"
                    . "Waktu: {waktu_mulai}\n{lokasi}\n\n"
                    . "Dokumen terkait:\n"
                    . "- Surat Tugas: {link_surat_tugas}\n"
                    . "- Undangan Resmi: {link_undangan}\n"
                    . "- Naskah Lengkap: {link_naskah}\n"
                    . "- Form Penilaian Online: {link_form_penilaian}\n"
                    . "- Sinkronkan ke Kalender: {link_kalender_ics}\n\n"
                    . "Terima kasih.\nKomisi Tesis FKIP UNS",
            ],
            [
                'key' => 'approval_pembimbing',
                'nama_template' => 'Status Approval Pembimbing (ke Mahasiswa)',
                'deskripsi_placeholder' => '{nama_mahasiswa} {tahap_sidang} {nama_pembimbing} {status_lengkap}',
                'body' => "Yth. {nama_mahasiswa},\n\n"
                    . "{nama_pembimbing} telah memberikan persetujuan naskah Anda untuk tahap {tahap_sidang}.\n\n"
                    . "{status_lengkap}\n\n"
                    . "Terima kasih.\nSIM-TESIS FKIP UNS",
            ],
            [
                'key' => 'jadwal_terkunci',
                'nama_template' => 'Jadwal Sidang Terkunci (ke Mahasiswa)',
                'deskripsi_placeholder' => '{nama_mahasiswa} {tahap_sidang} {waktu_mulai} {lokasi} {link_kalender_ics}',
                'body' => "Yth. {nama_mahasiswa},\n\n"
                    . "Jadwal sidang {tahap_sidang} Anda sudah terkunci oleh Komisi Tesis.\n\n"
                    . "Waktu: {waktu_mulai}\n{lokasi}\n\n"
                    . "Sinkronkan ke kalender Anda: {link_kalender_ics}\n\n"
                    . "Mohon hadir tepat waktu.\nKomisi Tesis FKIP UNS",
            ],
            [
                'key' => 'hasil_kelulusan',
                'nama_template' => 'Hasil Sidang / Kelulusan (ke Mahasiswa)',
                'deskripsi_placeholder' => '{nama_mahasiswa} {tahap_sidang} {keputusan_sidang} {grade} {nilai_rata_rata}',
                'body' => "Yth. {nama_mahasiswa},\n\n"
                    . "Hasil sidang {tahap_sidang} Anda telah direkap oleh Komisi Tesis.\n\n"
                    . "Keputusan: {keputusan_sidang}\n"
                    . "Grade: {grade} (Nilai Rata-rata: {nilai_rata_rata})\n\n"
                    . "Silakan cek SIM-TESIS untuk detail & langkah selanjutnya.\n\n"
                    . "Selamat!\nKomisi Tesis FKIP UNS",
            ],
        ];

        foreach ($templates as $t) {
            NotifikasiTemplate::updateOrCreate(['key' => $t['key']], $t);
        }
    }
}

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

            // FR-10: Matriks Revisi Pasca Ujian — 3 template baru, dikirim
            // pada tiap perpindahan tahap alur revisi (diajukan mahasiswa ->
            // di-ACC/ditolak tiap penguji -> disahkan Kaprodi), supaya pola
            // notifikasinya konsisten dengan Sempro/Semhas/Ujian Tesis yang
            // sudah lebih dulu memakai WA Blast di setiap transisi status.
            [
                'key' => 'revisi_diajukan',
                'nama_template' => 'Matriks Revisi Diajukan (ke Dewan Penguji)',
                'deskripsi_placeholder' => '{nama_dosen} {nama_mahasiswa} {tahap_sidang} {link_revisi}',
                'body' => "Yth. {nama_dosen},\n\n"
                    . "{nama_mahasiswa} telah mengajukan matriks perbaikan naskah pasca sidang {tahap_sidang}.\n\n"
                    . "Mohon tinjau & berikan ACC/catatan perbaikan di: {link_revisi}\n\n"
                    . "Terima kasih.\nSIM-TESIS FKIP UNS",
            ],
            [
                'key' => 'revisi_perlu_perbaikan',
                'nama_template' => 'Revisi Perlu Diperbaiki Lagi (ke Mahasiswa)',
                'deskripsi_placeholder' => '{nama_mahasiswa} {nama_dosen} {tahap_sidang} {feedback_penguji} {link_revisi}',
                'body' => "Yth. {nama_mahasiswa},\n\n"
                    . "{nama_dosen} meminta perbaikan tambahan pada matriks revisi {tahap_sidang} Anda.\n\n"
                    . "Catatan: {feedback_penguji}\n\n"
                    . "Silakan perbarui matriks revisi Anda di: {link_revisi}\n\n"
                    . "Terima kasih.\nSIM-TESIS FKIP UNS",
            ],
            [
                'key' => 'revisi_disahkan',
                'nama_template' => 'Revisi Disahkan Kaprodi (ke Mahasiswa)',
                'deskripsi_placeholder' => '{nama_mahasiswa} {tahap_sidang} {status_lanjutan}',
                'body' => "Yth. {nama_mahasiswa},\n\n"
                    . "Revisi naskah {tahap_sidang} Anda sudah disahkan Kaprodi.\n\n"
                    . "{status_lanjutan}\n\n"
                    . "Selamat!\nKomisi Tesis FKIP UNS",
            ],
        ];

        foreach ($templates as $t) {
            NotifikasiTemplate::updateOrCreate(['key' => $t['key']], $t);
        }
    }
}

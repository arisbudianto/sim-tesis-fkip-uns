<x-layouts.app title="Panduan SOP dan Demo — SIM-TESIS">

    <div class="max-w-3xl mx-auto w-full flex flex-col gap-4">

        <a href="{{ route('public.index') }}" class="text-slate-500 hover:text-primary-800 text-[13px] font-semibold">&larr; Kembali ke Halaman Utama</a>

        <x-ui.card title="Unduh Panduan Lengkap">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="h-11 w-11 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
                        <x-ui.icon name="book-open" class="h-5 w-5 text-primary-800" />
                    </div>
                    <div>
                        <p class="text-[13.5px] font-bold text-primary-900">Panduan Penggunaan SIM-TESIS untuk Dosen</p>
                        <p class="text-[12.5px] text-slate-500">Dokumen Word (.docx) — langkah demi langkah penggunaan sistem bagi Dosen Pembimbing &amp; Dewan Penguji, lengkap dengan tangkapan layar dan menu Edit Profil.</p>
                    </div>
                </div>
                <a href="{{ asset('assets/panduan/Panduan_Dosen_SIM-TESIS.docx') }}" download
                   class="ui-btn ui-btn-primary whitespace-nowrap">Unduh Panduan (.docx)</a>
            </div>
        </x-ui.card>

        <x-ui.card title="Standar Operasional Prosedur (SOP) Tesis Magister">
            <div class="flex flex-col gap-3.5">
                <div class="rounded-xl border-l-4 border-primary-800 bg-slate-50 p-4">
                    <h3 class="text-[14.5px] font-extrabold text-primary-900 mb-1">Tahap 1: Pengajuan Judul dan Penetapan Pembimbing</h3>
                    <p class="text-[13px] text-slate-500 leading-relaxed">Mahasiswa mengajukan judul + 2 usulan dosen + PDF FPT-TI-00. Komisi Tesis menyetujui atau menolak, lalu menetapkan Pembimbing 1 (bidang studi) dan Pembimbing 2 (kependidikan) sesuai kuota.</p>
                </div>
                <div class="rounded-xl border-l-4 border-primary-800 bg-slate-50 p-4">
                    <h3 class="text-[14.5px] font-extrabold text-primary-900 mb-1">Tahap 2: Seminar Proposal</h3>
                    <p class="text-[13px] text-slate-500 leading-relaxed">Pendaftaran minimal H-14, unggah FPT-TI-01. Komisi memverifikasi, memplot jadwal dan 4 penguji, mengirim WA, lalu penguji mengisi rubrik 10 indikator. Komisi merilis rekap dan keputusan sidang.</p>
                </div>
                <div class="rounded-xl border-l-4 border-primary-800 bg-slate-50 p-4">
                    <h3 class="text-[14.5px] font-extrabold text-primary-900 mb-1">Tahap 3: Seminar Hasil</h3>
                    <p class="text-[13px] text-slate-500 leading-relaxed">Syarat: revisi Sempro disahkan Kaprodi, naskah Bab I–V, luaran publikasi. Alur verifikasi, plotting, penilaian, dan rekap sama seperti Sempro.</p>
                </div>
                <div class="rounded-xl border-l-4 border-primary-800 bg-slate-50 p-4">
                    <h3 class="text-[14.5px] font-extrabold text-primary-900 mb-1">Tahap 4: Ujian Tesis dan Yudisium</h3>
                    <p class="text-[13px] text-slate-500 leading-relaxed">Berkas ujian (termasuk TOEFL/EAP dan Turnitin), plotting dewan penguji, rubrik 4 dimensi, BAP, matriks revisi, lalu yudisium.</p>
                </div>
            </div>
        </x-ui.card>

    </div>

</x-layouts.app>

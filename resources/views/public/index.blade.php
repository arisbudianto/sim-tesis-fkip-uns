<x-layouts.app title="Sistem Informasi Manajemen Tesis S2 Pendidikan Guru Vokasi">

    <x-ui.hero
        title="Sistem Informasi Manajemen Tesis"
        highlight="S2 Pendidikan Guru Vokasi"
        subtitle=""
    >
        <x-slot:actions>
            @auth
                <a href="{{ route('dashboard') }}" class="ui-btn ui-btn-accent">Masuk ke Dashboard Sistem</a>
            @else
                <a href="{{ route('login') }}" class="ui-btn ui-btn-accent">Mulai Akses SIM-TESIS</a>
                <a href="{{ route('public.panduan') }}" class="ui-btn text-white border border-white/40">Lihat Alur SOP &rarr;</a>
            @endauth
        </x-slot:actions>
    </x-ui.hero>

    <x-ui.card title="4 Pilar Tahapan Tesis">
        <div class="grid md:grid-cols-2 gap-4">
            <div class="rounded-xl border border-slate-200 p-4">
                <h4 class="text-primary-800 font-extrabold text-[14px] mb-1.5">1. Penetapan Pembimbing</h4>
                <p class="text-slate-500 text-[13px] leading-relaxed">Alokasi Pembimbing 1 (Bidang Studi) dan Pembimbing 2 (Kependidikan) dengan validasi kuota otomatis oleh Komisi Tesis.</p>
            </div>
            <div class="rounded-xl border border-slate-200 p-4">
                <h4 class="text-primary-800 font-extrabold text-[14px] mb-1.5">2. Seminar Proposal (Sempro)</h4>
                <p class="text-slate-500 text-[13px] leading-relaxed">Pendaftaran minimal H-14, verifikasi berkas FPT-TI-01 s.d 09, dan pengesahan izin riset lapangan.</p>
            </div>
            <div class="rounded-xl border border-slate-200 p-4">
                <h4 class="text-primary-800 font-extrabold text-[14px] mb-1.5">3. Seminar Hasil (Semhas)</h4>
                <p class="text-slate-500 text-[13px] leading-relaxed">Telaah komprehensif Bab I–V serta validasi luaran artikel ilmiah (min. 2 draf &amp; 1 under review jurnal terakreditasi).</p>
            </div>
            <div class="rounded-xl border border-slate-200 p-4">
                <h4 class="text-primary-800 font-extrabold text-[14px] mb-1.5">4. Ujian Tesis & Yudisium</h4>
                <p class="text-slate-500 text-[13px] leading-relaxed">Plotting 4 Dewan Penguji bebas bentrok jadwal, rubrik 4 dimensi evaluasi, BAP daring, dan matriks revisi kelulusan.</p>
            </div>
        </div>
    </x-ui.card>

</x-layouts.app>

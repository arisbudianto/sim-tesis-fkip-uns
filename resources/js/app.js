import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Helper untuk form Alokasi Pembimbing (tab Pengajuan):
 * dipanggil dari tombol "Alokasikan/Ubah" di tiap baris tabel supaya
 * form di bawah otomatis terarah ke pengajuan yang dipilih.
 */
window.selectPengajuanForAlokasi = function (pengajuanId) {
    const select = document.getElementById('select-pengajuan');
    if (!select) return;
    select.value = pengajuanId;
    window.updateAlokasiAction(pengajuanId);
    document.getElementById('form-alokasi')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

window.updateAlokasiAction = function (pengajuanId) {
    const form = document.getElementById('form-alokasi');
    if (form && pengajuanId) {
        form.action = '/pengajuan/' + pengajuanId + '/alokasi-pembimbing';
    }
};

/**
 * Helper untuk form Plotting Jadwal & Dewan Penguji (tab Sidang):
 * saat mahasiswa+tahap dipilih, arahkan action form ke route plotting yang
 * sesuai dan pre-select Anggota 1 & 2 ke Pembimbing 1 & 2 (tetap bisa diganti).
 */
window.updatePlottingAction = function (selectEl) {
    const opt = selectEl.options[selectEl.selectedIndex];
    const tahap = opt.value;
    const pengajuanId = opt.getAttribute('data-pengajuan-id');
    const pembimbing1 = opt.getAttribute('data-pembimbing1');
    const pembimbing2 = opt.getAttribute('data-pembimbing2');

    if (!tahap || !pengajuanId) return;

    const routes = {
        sempro: '/sempro/plotting-jadwal/',
        semhas: '/semhas/plotting-jadwal/',
        ujian: '/ujian/plotting-jadwal/',
    };

    const form = document.getElementById('form-plotting');
    if (form) form.action = routes[tahap] + pengajuanId;

    const anggota1 = document.getElementById('plotting-anggota1');
    const anggota2 = document.getElementById('plotting-anggota2');
    if (anggota1 && pembimbing1) anggota1.value = pembimbing1;
    if (anggota2 && pembimbing2) anggota2.value = pembimbing2;
};

/**
 * Helper untuk form "Daftarkan Langsung" (tab Sidang, khusus Komisi Tesis):
 * mahasiswa & jenis sidang dipilih terpisah (dua dropdown), jadi action
 * form baru bisa disusun begitu KEDUA pilihan sudah terisi.
 */
window.updateDaftarLangsungAction = function () {
    const mhsSelect = document.getElementById('daftar-langsung-mahasiswa');
    const tahapSelect = document.getElementById('daftar-langsung-tahap');
    const form = document.getElementById('form-daftar-langsung');
    if (!mhsSelect || !tahapSelect || !form) return;

    const pengajuanId = mhsSelect.value;
    const tahap = tahapSelect.value;
    if (!pengajuanId || !tahap) return;

    form.action = '/sidang/daftar-langsung/' + tahap + '/' + pengajuanId;
};

Alpine.start();

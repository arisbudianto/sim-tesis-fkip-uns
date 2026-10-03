@php
    $role = Auth::user()->role ?? null;
    $roles = Auth::user()->roleList();
@endphp

@if(in_array('mahasiswa', $roles, true) && !in_array('kaprodi', $roles, true) && !in_array('komisi_tesis', $roles, true))
    @include('dashboard.roles.mahasiswa')
@endif

@if($role === 'komisi_tesis')
    @include('dashboard.roles.komisi-tesis')
@endif

@if($role === 'admin_prodi')
    @include('dashboard.roles.admin-prodi')
@endif

@if(in_array('kaprodi', $roles, true))
    @include('dashboard.roles.kaprodi')
@endif

@if(in_array('dosen', $roles, true) && !in_array('kaprodi', $roles, true) && $role !== 'komisi_tesis')
    @include('dashboard.roles.dosen')
@endif

{{--
    Data Master, Audit Log & Histori, dan Seluruh Pengajuan SENGAJA tidak
    lagi ditampilkan di sini (dulu ditumpuk ke bawah terus-menerus di
    halaman Ringkasan). Ketiganya sekarang jadi menu "tahap" tersendiri
    di sidebar (Administrasi), lihat dashboard.blade.php — supaya
    Ringkasan tetap ringkas dan tiap menu hanya menampilkan satu topik.
--}}

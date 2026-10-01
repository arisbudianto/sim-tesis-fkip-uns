@php
    $role = Auth::user()->role ?? null;
    $roles = Auth::user()->roleList();
@endphp

@if(in_array('mahasiswa', $roles, true) && !in_array('kaprodi', $roles, true) && !in_array('komisi_tesis', $roles, true))
    @include('dashboard.roles.mahasiswa')
@endif

@if($role === 'komisi_tesis')
    @include('dashboard.roles.komisi-tesis')
    <div class="mt-4 flex flex-col gap-4">
        @include('dashboard.tabs._master-data')
    </div>
@endif

@if($role === 'admin_prodi')
    @include('dashboard.roles.admin-prodi')
    <div class="mt-4 flex flex-col gap-4">
        @include('dashboard.tabs._master-data')
    </div>
@endif

@if(in_array('kaprodi', $roles, true))
    @include('dashboard.roles.kaprodi')
@endif

@if(in_array('dosen', $roles, true) && !in_array('kaprodi', $roles, true) && $role !== 'komisi_tesis')
    @include('dashboard.roles.dosen')
@endif

@if(in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi']))
    @include('dashboard.tabs.audit')
    <div class="mt-4">
        <h3 class="text-[14px] font-extrabold text-primary-900 mb-2">Seluruh Pengajuan Tesis (Operasional)</h3>
        @include('dashboard.tabs._semua-pengajuan')
    </div>
@endif

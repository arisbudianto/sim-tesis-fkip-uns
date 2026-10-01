<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-02 (Logbook Bimbingan Digital & Approval Berkala) resmi TIDAK
     * termasuk cakupan pengembangan SIM-TESIS. Migration ini membersihkan
     * tabel yang sempat dibuat di awal proyek sebelum keputusan tersebut.
     */
    public function up(): void
    {
        Schema::dropIfExists('logbook_bimbingans');
    }

    public function down(): void
    {
        // Sengaja tidak di-recreate — fitur ini memang dihilangkan permanen.
    }
};

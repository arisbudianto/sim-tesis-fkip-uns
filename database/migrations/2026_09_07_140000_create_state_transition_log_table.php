<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('state_transition_log', function (Blueprint $table) {
            $table->id();
            $table->uuid('pengajuan_tesis_id');
            $table->string('from_state');
            $table->string('to_state');
            $table->uuid('actor_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('actor_role')->nullable();

            // Rollback/koreksi manual oleh Komisi Tesis (bukan transisi maju
            // normal via prasyarat ACC) — dibedakan supaya histori tetap bisa
            // diaudit mana yang alur normal dan mana yang override manual.
            $table->boolean('is_override')->default(false);
            $table->text('override_reason')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('pengajuan_tesis_id')->references('id')->on('pengajuan_tesis')->cascadeOnDelete();
            $table->index('pengajuan_tesis_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('state_transition_log');
    }
};

<?php

namespace App\Domain\StateEngine\Services;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Pembimbing\Services\AdvisorQuotaEngine;
use App\Domain\StateEngine\Models\StateTransitionLog;
use App\Domain\StateEngine\TahapTesis;
use App\Models\User;
use Exception;

class LifecycleStateMachine
{
    /**
     * === API sesuai spesifikasi Modul 4 ===
     * canTransition(mahasiswaId, fromState, toState) — dipakai kalau caller
     * cuma punya ID mahasiswa (bukan instance model), mis. dari job/command.
     * Mengembalikan false (bukan exception) untuk fromState yang tidak cocok
     * dengan status_tahap mahasiswa saat ini — itu sendiri sudah menolak
     * percobaan "lompat tahap".
     */
    public static function canTransition(string $mahasiswaId, string $fromState, string $toState): bool
    {
        $tesis = PengajuanTesis::where('mahasiswa_id', $mahasiswaId)->first();
        if (!$tesis || $tesis->status_tahap !== $fromState) {
            return false;
        }

        return self::canTransitionTo($tesis, $toState);
    }

    /**
     * transition(mahasiswaId, toState, actorId) — versi resmi Modul 4.
     * actorId WAJIB diisi (siapa yang memicu transisi) untuk keperluan
     * histori/audit. Melempar Exception kalau prasyarat belum terpenuhi.
     */
    public static function transitionByMahasiswaId(string $mahasiswaId, string $toState, string $actorId): void
    {
        $tesis = PengajuanTesis::where('mahasiswa_id', $mahasiswaId)->firstOrFail();
        $actor = User::findOrFail($actorId);

        self::transition($tesis, $toState, $actor);
    }

    /**
     * Validasi prasyarat: tahap SAAT INI mahasiswa harus berstatus
     * "ACC/Disahkan" (lihat masing-masing case) sebelum boleh berpindah
     * ke $targetTahap. Method ini murni pengecekan, TIDAK mengubah data.
     */
    public static function canTransitionTo(PengajuanTesis $tesis, string $targetTahap): bool
    {
        $current = $tesis->status_tahap;

        switch ($targetTahap) {
            case TahapTesis::TAHAP_2_SEMPRO->value:
                return $current === TahapTesis::TAHAP_1_BIMBINGAN->value
                    && $tesis->pembimbing_1_id
                    && $tesis->pembimbing_2_id;

            case TahapTesis::TAHAP_3_SEMHAS->value:
                $semproSidang = $tesis->aktivitasSidangs()->where('tahap_sidang', 'sempro')->first();
                if (!$semproSidang || !$semproSidang->revisiDokumen) return false;
                return $current === TahapTesis::TAHAP_2_SEMPRO->value && $semproSidang->revisiDokumen->pengesahan_kaprodi;

            case TahapTesis::TAHAP_4_UJIAN->value:
                $semhasSidang = $tesis->aktivitasSidangs()->where('tahap_sidang', 'semhas')->first();
                if (!$semhasSidang || !$semhasSidang->revisiDokumen) return false;
                return $current === TahapTesis::TAHAP_3_SEMHAS->value && $semhasSidang->revisiDokumen->pengesahan_kaprodi;

            case TahapTesis::SELESAI_YUDISIUM->value:
                $ujianSidang = $tesis->aktivitasSidangs()->where('tahap_sidang', 'ujian')->first();
                if (!$ujianSidang || !$ujianSidang->revisiDokumen) return false;
                return $current === TahapTesis::TAHAP_4_UJIAN->value && $ujianSidang->revisiDokumen->pengesahan_kaprodi;

            default:
                return false;
        }
    }

    /**
     * Eksekusi transisi MAJU (satu-satunya jalur yang boleh mengubah
     * status_tahap di seluruh sistem — lihat acceptance criteria Modul 4).
     * $actor opsional secara teknis (nullable) supaya kode lama yang belum
     * sempat di-refactor tidak langsung patah, TAPI setiap pemanggilan baru
     * WAJIB menyertakan actor demi jejak audit yang lengkap.
     */
    public static function transition(PengajuanTesis $tesis, string $targetTahap, ?User $actor = null): void
    {
        if (!self::canTransitionTo($tesis, $targetTahap)) {
            throw new Exception("Transisi tidak diizinkan: Mahasiswa belum memenuhi prasyarat untuk tahap {$targetTahap}");
        }

        $fromState = $tesis->status_tahap;
        $tesis->update(['status_tahap' => $targetTahap]);

        self::catatHistori($tesis, $fromState, $targetTahap, $actor, false, null);
    }

    /**
     * ROLLBACK / KOREKSI MANUAL — HANYA boleh dilakukan oleh Komisi Tesis,
     * dan WAJIB menyertakan alasan (override_reason) untuk keperluan audit.
     * Tidak melalui canTransitionTo() (rollback secara definisi melanggar
     * urutan linear normal), tapi tetap tercatat di state_transition_log
     * dengan is_override = true supaya bisa dibedakan dari transisi normal.
     */
    public static function rollback(PengajuanTesis $tesis, string $targetTahap, User $actor, string $reason): void
    {
        if (!$actor->hasRole('komisi_tesis')) {
            throw new Exception('Rollback/koreksi status hanya boleh dilakukan oleh Komisi Tesis.');
        }

        if (trim($reason) === '') {
            throw new Exception('Rollback wajib menyertakan alasan (override_reason) untuk keperluan audit.');
        }

        $validTargets = array_map(fn ($c) => $c->value, TahapTesis::cases());
        if (!in_array($targetTahap, $validTargets, true)) {
            throw new Exception("Tahap tujuan '{$targetTahap}' tidak dikenali oleh state machine.");
        }

        $fromState = $tesis->status_tahap;
        $tesis->update(['status_tahap' => $targetTahap]);

        self::catatHistori($tesis, $fromState, $targetTahap, $actor, true, $reason);
    }

    protected static function catatHistori(PengajuanTesis $tesis, string $fromState, string $toState, ?User $actor, bool $isOverride, ?string $reason): void
    {
        StateTransitionLog::create([
            'pengajuan_tesis_id' => $tesis->id,
            'from_state' => $fromState,
            'to_state' => $toState,
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name,
            'actor_role' => $actor?->role,
            'is_override' => $isOverride,
            'override_reason' => $reason,
            'created_at' => now(),
        ]);
    }

    /**
     * === Modul 5 — Penentuan Pembimbing (FR-01) ===
     * Satu-satunya jalur sah untuk menetapkan Pembimbing 1 & 2 — TIDAK
     * boleh ada controller yang panggil $tesis->update(['pembimbing_1_id'
     * => ...]) langsung (acceptance criteria Modul 4 & 5).
     *
     * Catatan desain: kolom status_tahap TIDAK punya nilai enum terpisah
     * untuk "Pembimbing Ditetapkan" (skema DB hanya 5 tahap utama; baik
     * "belum ada pembimbing" maupun "pembimbing sudah ditetapkan" sama-sama
     * masih berstatus tahap_1_bimbingan — pembimbing 1&2 dibedakan lewat
     * kolom pembimbing_1_id/2_id, bukan status_tahap terpisah). Meski
     * status_tahap tidak berubah, event ini TETAP WAJIB tercatat di
     * state_transition_log (dengan label milestone tersendiri) supaya
     * tetap bisa diaudit kapan & oleh siapa pembimbing ditetapkan — sesuai
     * instruksi "sistem trigger StateEngine ke Pembimbing Ditetapkan".
     *
     * @throws Exception kalau salah satu dosen melebihi kuota bimbingan.
     */
    public static function tetapkanPembimbing(PengajuanTesis $tesis, string $pembimbing1Id, string $pembimbing2Id, string $nomorSk, string $tanggalSk, User $actor): void
    {
        if (!AdvisorQuotaEngine::checkQuota($pembimbing1Id, $tesis->id)) {
            throw new Exception('Pembimbing 1 melebihi kuota bimbingan!');
        }

        if (!AdvisorQuotaEngine::checkQuota($pembimbing2Id, $tesis->id)) {
            throw new Exception('Pembimbing 2 melebihi kuota bimbingan!');
        }

        $sudahAdaPembimbingSebelumnya = (bool) $tesis->pembimbing_1_id;

        $tesis->update([
            'pembimbing_1_id' => $pembimbing1Id,
            'pembimbing_2_id' => $pembimbing2Id,
            'nomor_sk_pembimbing' => $nomorSk,
            'tanggal_sk_pembimbing' => $tanggalSk,
        ]);

        self::catatHistori(
            $tesis,
            $sudahAdaPembimbingSebelumnya ? 'pembimbing_ditetapkan' : 'belum_ada_pembimbing',
            'pembimbing_ditetapkan',
            $actor,
            false,
            null
        );
    }

    /**
     * Label ramah-baca untuk tiap status_tahap, dipakai pada pesan blokir.
     */
    protected static function label(string $tahap): string
    {
        return TahapTesis::tryFrom($tahap)?->label() ?? $tahap;
    }

    /**
     * GATE PENDAFTARAN: dipakai oleh controller pendaftaran (Semhas, Ujian, dst)
     * untuk memastikan mahasiswa memang SUDAH berada di tahap yang didaftar
     * (artinya tahap sebelumnya sudah selesai & disahkan Kaprodi via
     * RevisiDokumenController::pengesahanKaprodi()).
     *
     * Beda dengan canTransitionTo() yang memvalidasi SAAT transisi terjadi,
     * method ini memvalidasi SAAT mahasiswa mencoba mendaftar ke tahap
     * tersebut (status_tahap seharusnya sudah persis sama).
     *
     * Return null jika boleh lanjut, atau string alasan penolakan jika tidak.
     */
    public static function blockReason(PengajuanTesis $tesis, string $requiredTahap): ?string
    {
        if ($tesis->status_tahap === $requiredTahap) {
            return null;
        }

        // Jika mahasiswa sudah lewat dari tahap ini juga tetap ditolak
        // (mencegah pendaftaran ganda / mundur ke tahap yang sudah lewat).
        $currentLabel = self::label($tesis->status_tahap);
        $requiredLabel = self::label($requiredTahap);

        return "Pendaftaran ditolak: status akademik mahasiswa saat ini adalah {$currentLabel}, "
             . "belum memenuhi syarat untuk mendaftar {$requiredLabel}. "
             . "Pastikan tahap sebelumnya sudah dinyatakan lulus dan revisinya sudah disahkan Kaprodi.";
    }

    /**
     * Sama seperti blockReason(), tapi khusus dipakai Komisi Tesis saat
     * plotting jadwal sidang (KomisiTesisController) — memastikan jenis
     * sidang yang mau dijadwalkan cocok dengan tahap_sidang yang sedang
     * dijalani mahasiswa saat ini.
     */
    public static function blockReasonForSidang(PengajuanTesis $tesis, string $tahapSidang): ?string
    {
        $expectedStatus = match ($tahapSidang) {
            'sempro' => 'tahap_2_sempro',
            'semhas' => 'tahap_3_semhas',
            'ujian'  => 'tahap_4_ujian',
            default  => null,
        };

        if ($expectedStatus === null) {
            return "Jenis sidang '{$tahapSidang}' tidak dikenali oleh state machine.";
        }

        return self::blockReason($tesis, $expectedStatus);
    }
}

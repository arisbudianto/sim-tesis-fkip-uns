<?php

namespace App\Domain\Audit\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Pencatat audit log untuk aksi approval/penolakan (siapa, kapan, aksi apa)
 * — dipakai di seluruh endpoint verifikasi/persetujuan lintas domain
 * (Sempro, Semhas, UjianTesis, Sidang, Pembimbing).
 *
 * Kegagalan mencatat log TIDAK BOLEH menggagalkan aksi bisnis utamanya
 * (mis. verifikasi tetap tersimpan meski audit log gagal ditulis) — makanya
 * dibungkus try-catch dan fallback ke storage/logs/laravel.log.
 */
class AuditLogger
{
    public static function log(?User $actor, string $action, ?string $subjectType = null, ?string $subjectId = null, ?string $description = null, array $meta = []): void
    {
        try {
            AuditLog::create([
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name,
                'actor_role' => $actor?->role,
                'action' => $action,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'description' => $description,
                'meta' => $meta,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[AuditLogger] Gagal menyimpan audit log ke database.', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

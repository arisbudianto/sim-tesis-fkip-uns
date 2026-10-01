<?php

namespace App\Domain\Notifikasi\Models;

use Illuminate\Database\Eloquent\Model;

class NotifikasiTemplate extends Model
{
    protected $table = 'notifikasi_templates';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Substitusi placeholder {nama_variabel} di body template dengan
     * data yang diberikan. Placeholder yang tidak disuplai dibiarkan
     * apa adanya (supaya kelihatan jelas kalau ada yang lupa diisi,
     * bukan hilang diam-diam).
     */
    public function render(array $data): string
    {
        $body = $this->body;
        foreach ($data as $key => $value) {
            $body = str_replace('{' . $key . '}', (string) $value, $body);
        }
        return $body;
    }
}

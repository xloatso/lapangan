<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Model untuk menyimpan data setiap lapangan dalam reservasi.
class Booking_details extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan oleh model.
    protected $table = 'booking_details';

    // Lindungi id dari pengisian massal; kolom lainnya boleh diisi.
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['duration_hours' => 'integer', 'subtotal' => 'integer'];
    }

    // Setiap detail dimiliki oleh satu reservasi melalui kolom bookings_id.
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Bookings::class, 'bookings_id');
    }

    // Setiap detail merujuk ke satu lapangan melalui kolom courts_id.
    public function court(): BelongsTo
    {
        return $this->belongsTo(Courts::class, 'courts_id');
    }
}

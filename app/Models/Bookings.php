<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bookings extends Model
{
    use HasFactory;

    // Lindungi id dari pengisian massal, mengikuti pola model maujajan.
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['booking_date' => 'date', 'total_price' => 'integer'];
    }

    public function bookingDetails(): HasMany
    {
        return $this->hasMany(Booking_details::class, 'bookings_id');
    }
}

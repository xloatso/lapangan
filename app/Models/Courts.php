<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Courts extends Model
{
    use HasFactory;

    protected $table = 'courts';

    // Lindungi id dari pengisian massal, mengikuti pola model maujajan.
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['price_per_hour' => 'integer'];
    }

    public function details(): HasMany
    {
        return $this->hasMany(Booking_details::class, 'courts_id');
    }
}

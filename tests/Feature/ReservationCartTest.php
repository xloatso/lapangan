<?php

namespace Tests\Feature;

use App\Models\Bookings;
use App\Models\Courts;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationCartTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $items): array
    {
        return ['customer_name' => 'Tim A', 'customer_phone' => '081234567890',
            'booking_date' => today()->addDay()->format('Y-m-d'), 'start_time' => '10:00', 'items' => $items];
    }

    public function test_multiple_courts_are_saved_as_one_booking_with_server_prices(): void
    {
        $first = Courts::create(['name' => 'Futsal', 'type' => 'futsal', 'price_per_hour' => 100000]);
        $second = Courts::create(['name' => 'Badminton', 'type' => 'badminton', 'price_per_hour' => 50000]);
        $this->post('/checkout', $this->payload([$first->id => 2, $second->id => 3]) + ['total_price' => 1])
            ->assertSessionHasNoErrors()->assertRedirect();
        $booking = Bookings::firstOrFail();
        $this->assertSame(350000, $booking->total_price);
        $this->assertCount(2, $booking->bookingDetails);
        $this->assertSame(5, $booking->bookingDetails->sum('duration_hours'));
    }

    public function test_conflicting_cart_is_atomic_and_cancelled_booking_can_only_reactivate_if_available(): void
    {
        $first = Courts::create(['name' => 'A', 'type' => 'futsal', 'price_per_hour' => 100000]);
        $second = Courts::create(['name' => 'B', 'type' => 'basket', 'price_per_hour' => 100000]);
        $this->post('/checkout', $this->payload([$second->id => 2]))->assertSessionHasNoErrors();
        $original = Bookings::firstOrFail();
        $this->post('/checkout', $this->payload([$first->id => 1, $second->id => 1]))->assertSessionHasErrors('schedule');
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('booking_details', 1);
        $this->actingAs(User::factory()->create());
        $this->patch('/admin/bookings/'.$original->id.'/status', ['status' => 'Batal'])->assertRedirect();
        $this->post('/checkout', $this->payload([$first->id => 1, $second->id => 1]))->assertRedirect();
        $this->assertDatabaseCount('bookings', 2);
        $this->patch('/admin/bookings/'.$original->id.'/status', ['status' => 'Lunas'])->assertSessionHasErrors('schedule');
        $this->assertSame('Batal', $original->fresh()->status);
    }

    public function test_invalid_cart_and_closing_time_are_rejected(): void
    {
        $court = Courts::create(['name' => 'A', 'type' => 'futsal', 'price_per_hour' => 100000]);
        $this->post('/checkout', $this->payload([$court->id => 0]))->assertSessionHas('error');
        $this->post('/checkout', $this->payload([999 => 1]))->assertSessionHasErrors('items');
        $this->post('/checkout', $this->payload([$court->id => -1]))->assertSessionHasErrors('items.'.$court->id);
        $this->post('/checkout', array_replace($this->payload([$court->id => 2]), ['start_time' => '21:00']))
            ->assertSessionHasErrors('start_time');
        $this->assertDatabaseCount('bookings', 0);
    }
}

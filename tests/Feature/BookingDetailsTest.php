<?php

namespace Tests\Feature;

use App\Models\Bookings;
use App\Models\Courts;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_details_belong_to_booking_and_appear_on_dashboard(): void
    {
        $court = Courts::create(['name' => 'Lapangan A', 'type' => 'futsal', 'price_per_hour' => 100000]);
        $this->post('/checkout', [
            'customer_name' => 'Pemesan A', 'customer_phone' => '081234567890',
            'booking_date' => today()->addDay()->format('Y-m-d'),
            'start_time' => '10:00', 'items' => [$court->id => 2],
        ])->assertSessionHasNoErrors()->assertRedirect(route('customer.index'));

        $booking = Bookings::firstOrFail();
        $detail = $booking->bookingDetails->sole();
        $this->assertTrue($detail->booking->is($booking));
        $this->assertTrue($detail->court->is($court));
        $this->assertTrue($court->details->sole()->is($detail));
        $this->assertSame(200000, $detail->subtotal);
        $this->assertSame($detail->subtotal, $booking->total_price);

        $this->actingAs(User::factory()->create())->get('/admin/dashboard')
            ->assertOk()->assertViewIs('dashboard')->assertSee('Pemesan A')
            ->assertSee('Lapangan A')->assertSee('10:00')->assertSee('2 jam')->assertSee('200.000');
        $this->get('/booking-details')->assertNotFound();
        $this->post('/booking-details', ['bookings_id' => $booking->id])->assertNotFound();
        $this->assertDatabaseCount('booking_details', 1);
    }
}

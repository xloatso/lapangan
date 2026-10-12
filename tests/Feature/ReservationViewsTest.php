<?php

namespace Tests\Feature;

use App\Models\Bookings;
use App\Models\Courts;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_catalog_and_reserve_a_court(): void
    {
        $court = Courts::create(['name' => 'Arena', 'type' => 'futsal', 'price_per_hour' => 150000]);
        $date = today()->addDay()->format('Y-m-d');
        $this->get('/')->assertOk()->assertViewIs('customer.index')->assertSee('Arena');
        $data = [
            'customer_name' => 'Pemesan', 'customer_phone' => '081234567890',
            'items' => [$court->id => 2], 'booking_date' => $date, 'start_time' => '10:00',
        ];
        $this->post('/checkout', $data)->assertRedirect()->assertSessionHas('success');
        $booking = Bookings::firstOrFail();
        $this->assertSame('Pemesan', $booking->customer_name);
        $this->assertSame(300000, $booking->total_price);
        $this->assertSame($court->id, $booking->bookingDetails->first()->courts_id);
        $this->get('/?date='.$date)->assertOk();
        $this->post('/checkout', $data)->assertSessionHasErrors('schedule');
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_admin_pages_and_court_form_work_with_existing_schema(): void
    {
        $this->get('/login')->assertOk()->assertViewIs('auth.login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin/dashboard')->assertOk()->assertViewIs('dashboard');
        $this->get('/admin/courts')->assertOk()->assertViewIs('admin.courts.index');
        $this->get('/admin/courts/create')->assertOk()->assertViewIs('admin.courts.create');
        $this->post('/admin/courts', [
            'name' => 'Arena Baru', 'type' => 'futsal', 'price_per_hour' => 100000, 'description' => 'Indoor',
        ])->assertRedirect('/admin/courts');
        $court = Courts::firstOrFail();
        $this->get('/admin/courts/'.$court->id.'/edit')->assertOk()->assertSee('Arena Baru');
        $booking = Bookings::create([
            'customer_name' => 'Pemesan', 'customer_phone' => '081234567890',
            'booking_date' => today()->addDay(), 'total_price' => 100000, 'status' => 'Pending',
        ]);
        $booking->bookingDetails()->create(['courts_id' => $court->id, 'start_time' => '10:00', 'duration_hours' => 1, 'subtotal' => 100000]);
        $this->get('/admin/dashboard')->assertOk()->assertSee('Pemesan');
        $this->patch('/admin/bookings/'.$booking->id.'/status', ['status' => 'Lunas'])->assertRedirect();
        $this->assertSame('Lunas', $booking->fresh()->status);
    }
}

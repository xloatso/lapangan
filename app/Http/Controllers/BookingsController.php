<?php

// Menghubungkan katalog pelanggan, checkout, dan pengelolaan status reservasi.

namespace App\Http\Controllers;

use App\Models\Booking_details;
use App\Models\Bookings;
use App\Models\Courts;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingsController extends Controller
{
    // Sediakan seluruh katalog bagi pelanggan tanpa perlu login.
    public function index()
    {
        $courts = Courts::all();
        $date = today()->format('Y-m-d');

        return view('customer.index', compact('courts', 'date'));
    }

    // Validasi checkout dan simpan reservasi beserta rinciannya dalam satu transaksi.
    public function store(Request $request)
    {
        // Durasi nol berarti lapangan tidak dipilih.
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => ['required', 'string', 'regex:/^\+?[0-9][0-9\s-]{7,23}$/'],
            'booking_date' => 'required|date_format:Y-m-d|after_or_equal:today|before_or_equal:'.today()->addMonths(3)->format('Y-m-d'),
            'start_time' => 'required|date_format:H:i|in:08:00,09:00,10:00,11:00,12:00,13:00,14:00,15:00,16:00,17:00,18:00,19:00,20:00,21:00',
            'items' => 'required|array|max:100',
            'items.*' => 'nullable|integer|min:0|max:4',
        ]);

        $items = array_filter($data['items'], fn ($duration) => $duration > 0);
        if (empty($items)) {
            return back()->withInput()->with('error', 'Pilih minimal satu lapangan!');
        }
        if (now()->greaterThanOrEqualTo(Carbon::parse($data['booking_date'].' '.$data['start_time']))) {
            throw ValidationException::withMessages(['start_time' => 'Pilih tanggal dan jam main yang akan datang.']);
        }

        try {
            $booking = DB::transaction(function () use ($data, $items) {
                // Kunci lapangan berurutan agar dua checkout tidak mengambil jadwal yang sama.
                foreach (array_keys($items) as $courtId) {
                    if (! ctype_digit((string) $courtId)) {
                        throw ValidationException::withMessages(['items' => 'Pilihan lapangan tidak valid.']);
                    }
                }
                $courts = Courts::whereIn('id', array_keys($items))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                $booking = Bookings::create([
                    'customer_name' => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'],
                    'booking_date' => $data['booking_date'],
                    'total_price' => 0,
                    'status' => 'Pending',
                ]);
                $totalPrice = 0;

                foreach ($items as $courtId => $duration) {
                    $court = $courts->get($courtId);
                    if (! $court) {
                        throw ValidationException::withMessages(['items' => 'Lapangan yang dipilih sudah tidak tersedia. Silakan pilih ulang.']);
                    }
                    if ((int) substr($data['start_time'], 0, 2) + $duration > 22) {
                        throw ValidationException::withMessages(['start_time' => 'Semua reservasi harus selesai paling lambat pukul 22.00.']);
                    }
                    $this->ensureAvailable($court->id, $data['booking_date'], $data['start_time'], $duration);

                    // Harga berasal dari database, bukan total yang dikirim browser.
                    $subtotal = $court->price_per_hour * $duration;
                    $totalPrice += $subtotal;
                    if ($totalPrice > 2147483647) {
                        throw ValidationException::withMessages(['items' => 'Total reservasi terlalu besar. Kurangi durasi atau jumlah lapangan.']);
                    }
                    $booking->bookingDetails()->create([
                        'courts_id' => $court->id,
                        'start_time' => $data['start_time'],
                        'duration_hours' => $duration,
                        'subtotal' => $subtotal,
                    ]);
                }

                $booking->update(['total_price' => $totalPrice]);

                return $booking;
            }, 3);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Gagal memproses reservasi. Silakan coba lagi.');
        }

        return redirect()->route('customer.index')->with('success', 'Reservasi berhasil dibuat! Nomor Reservasi: #'.$booking->id);
    }

    // Muat rincian dan lapangan sekaligus untuk tabel dashboard.
    public function adminDashboard()
    {
        $bookings = Bookings::with('bookingDetails.court')->latest()->get();

        return view('dashboard', compact('bookings'));
    }

    // Perbarui status; reservasi batal hanya boleh diaktifkan jika jadwal masih kosong.
    public function updateStatus(Request $request, string $id)
    {
        $data = $request->validate(['status' => 'required|in:Pending,Lunas,Batal']);
        $booking = Bookings::findOrFail($id);

        DB::transaction(function () use ($booking, $data) {
            $details = $booking->bookingDetails()->orderBy('courts_id')->get();
            Courts::whereIn('id', $details->pluck('courts_id'))->orderBy('id')->lockForUpdate()->get();
            $booking = Bookings::lockForUpdate()->findOrFail($booking->id);
            if ($data['status'] !== 'Batal') {
                foreach ($details as $detail) {
                    $this->ensureAvailable($detail->courts_id, $booking->booking_date->format('Y-m-d'), $detail->start_time, $detail->duration_hours, $booking->id);
                }
            }
            $booking->update($data);
        }, 3);

        return back()->with('success', 'Status reservasi #'.$booking->id.' berhasil diperbarui!');
    }

    // Pemeriksaan khusus sewa lapangan: jadwal tidak boleh bertumpuk.
    private function ensureAvailable(int $courtId, string $date, string $start, int $duration, ?int $except = null): void
    {
        $startHour = (int) substr($start, 0, 2) + (int) substr($start, 3, 2) / 60;
        $details = Booking_details::where('courts_id', $courtId)
            ->whereHas('booking', function ($query) use ($date, $except) {
                $query->whereDate('booking_date', $date)->where('status', '!=', 'Batal');
                if ($except) {
                    $query->where('id', '!=', $except);
                }
            })->get();
        foreach ($details as $detail) {
            $existingStart = (int) substr($detail->start_time, 0, 2) + (int) substr($detail->start_time, 3, 2) / 60;
            if ($startHour < $existingStart + $detail->duration_hours && $startHour + $duration > $existingStart) {
                throw ValidationException::withMessages(['schedule' => 'Jadwal lapangan sudah dipesan. Silakan pilih jam atau lapangan lain.']);
            }
        }
    }
}

{{-- Halaman rekap reservasi: menampilkan pelanggan, rincian menu, total, dan perubahan status. --}}
<x-app-layout>
    {{-- Rekap memuat rincian menu dan form perubahan status setiap reservasi. --}}
    <x-slot name="header">
        <div class="flex flex-wrap gap-4 justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Reservasi Masuk') }}
            </h2>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('courts.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 shadow-sm transition">
                    Kelola Lapangan
                </a>
                <a href="{{ route('customer.index') }}" target="_blank" rel="noopener" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 shadow-sm transition">
                    Lihat Lapangan Customer
                </a>
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div role="alert" class="mb-4 p-4 bg-red-100 text-red-700">{{ $errors->first() }}</div>
            @endif
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                            <tr>
                                <th class="p-4 border-b"># ID</th>
                                <th class="p-4 border-b">Pelanggan</th>
                                <th class="p-4 border-b">Tanggal Main</th>
                                <th class="p-4 border-b">Rincian Reservasi</th>
                                <th class="p-4 border-b">Total Harga</th>
                                <th class="p-4 border-b">Status</th>
                                <th class="p-4 border-b text-center">Aksi Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-sm">
                            {{-- Tampilkan setiap reservasi atau pesan kosong jika belum ada data. --}}
                            @forelse($bookings as $booking)
                                <tr class="hover:bg-gray-50">
                                    <td class="p-4 font-bold text-gray-700">#{{ $booking->id }}</td>
                                    <td class="p-4 font-medium">{{ $booking->customer_name }}</td>
                                    <td class="p-4">
                                        <span class="bg-blue-100 text-blue-800 font-bold px-2.5 py-1 rounded-full text-xs">
                                            {{ $booking->booking_date->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                                            {{-- Relasi rincian dan menu sudah dimuat bersama oleh controller. --}}
                                            @foreach($booking->bookingDetails as $detail)
                                                <li>
                                                    <strong>{{ $detail->court->name ?? 'Lapangan' }}</strong>
                                                    {{ substr($detail->start_time, 0, 5) }} / {{ $detail->duration_hours }} jam
                                                    <span class="text-xs text-gray-400">(Rp {{ number_format($detail->subtotal, 0, ',', '.') }})</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="p-4 font-bold text-green-600">
                                        Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                                    </td>
                                    <td class="p-4">
                                        @if(strtolower($booking->status) == 'pending')
                                            <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2.5 py-1 rounded">PENDING</span>
                                        @elseif(strtolower($booking->status) == 'lunas' || strtolower($booking->status) == 'selesai')
                                            <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-1 rounded">LUNAS</span>
                                        @else
                                            <span class="bg-red-100 text-red-800 text-xs font-bold px-2.5 py-1 rounded">{{ strtoupper($booking->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center">
                                        {{-- Perubahan pilihan langsung mengirim PATCH; controller membatasi nilai status yang diizinkan. --}}
                                        <form action="{{ route('admin.bookings.updateStatus', $booking->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <select aria-label="Status reservasi #{{ $booking->id }}" name="status" onchange="this.form.submit()" class="text-xs border border-gray-300 rounded p-1.5 bg-white shadow-sm font-semibold">
                                                <option value="Pending" {{ strtolower($booking->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="Lunas" {{ strtolower($booking->status) == 'lunas' || strtolower($booking->status) == 'selesai' ? 'selected' : '' }}>Selesai / Lunas</option>
                                                <option value="Batal" {{ strtolower($booking->status) == 'batal' ? 'selected' : '' }}>Batalkan</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-gray-500">Belum ada reservasi masuk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

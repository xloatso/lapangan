{{-- Halaman pemesanan publik: data pemesan, pilihan menu, dan dialog konfirmasi checkout. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Lapangan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-6 font-sans">
    <main class="max-w-5xl mx-auto">
        <header class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Reservasi Lapangan</h1>
            <p class="text-gray-500 text-sm mt-1">Pilih lapangan, tentukan durasi, dan masukkan jadwal main Anda</p>
        </header>

        {{-- Tampilkan hasil checkout dan pertahankan input ketika validasi gagal. --}}
        @if(session('success'))
            <div role="status" class="bg-green-100 text-green-700 p-4 rounded-xl mb-6">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div role="alert" class="bg-red-100 text-red-700 p-4 rounded-xl mb-6">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <ul role="alert" class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        {{-- Filter hanya menyembunyikan kartu; durasi pilihan kategori lain tetap tersimpan. --}}
        <div class="flex flex-wrap justify-center gap-3 mb-8" aria-label="Filter kategori">
            @foreach(['all' => 'Semua Lapangan', 'futsal' => 'Futsal', 'badminton' => 'Badminton', 'basket' => 'Basket'] as $category => $label)
                <button type="button" data-filter="{{ $category }}" aria-pressed="{{ $category === 'all' ? 'true' : 'false' }}"
                    class="btn-category px-5 py-2 rounded-full font-semibold text-sm border {{ $category === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600' }}">{{ $label }}</button>
            @endforeach
        </div>

        {{-- Form POST mengirim identitas, jadwal main, durasi per ID lapangan, dan token CSRF ke checkout. --}}
        <form id="orderForm" action="{{ route('customer.checkout') }}" method="POST">
            @csrf
            <section class="bg-white p-6 rounded-xl shadow-sm border mb-6">
                <h2 class="text-lg font-bold text-gray-700 mb-4 pb-2 border-b">1. Informasi Pemesan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="customer_name" class="block text-sm font-semibold mb-1">Nama Lengkap</label>
                        <input type="text" id="customer_name" name="customer_name" required maxlength="255" value="{{ old('customer_name') }}" placeholder="Masukkan nama pemesan" class="w-full border rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label for="customer_phone" class="block text-sm font-semibold mb-1">Nomor Telepon / WhatsApp</label>
                        <input type="tel" id="customer_phone" name="customer_phone" required maxlength="25" value="{{ old('customer_phone') }}" placeholder="Contoh: 081234567890" class="w-full border rounded-lg px-4 py-2">
                    </div>
                </div>
            </section>

            <h2 class="text-lg font-bold text-gray-700 mb-4">2. Pilih Lapangan</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($courts as $court)
                    <article class="court-card bg-white rounded-xl shadow-sm border overflow-hidden flex flex-col justify-between" data-category="{{ $court->type }}">
                        <div>
                            @if($court->image)
                                {{-- object-contain menampilkan seluruh foto di dalam area tetap tanpa memotong tepinya. --}}
                                <img src="{{ asset('storage/'.$court->image) }}" alt="{{ $court->name }}" class="w-full h-40 object-contain bg-gray-50 p-2">
                            @else
                                <div class="bg-gray-200 h-40 flex items-center justify-center text-gray-500">Tanpa Gambar</div>
                            @endif
                            <div class="p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">{{ $court->type }}</span>
                                    <span class="font-bold text-green-600">Rp {{ number_format($court->price_per_hour, 0, ',', '.') }} / jam</span>
                                </div>
                                <h3 class="font-bold text-gray-800 text-lg">{{ $court->name }}</h3>
                                <p class="text-xs text-gray-500 mt-1">{{ $court->description }}</p>
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 border-t">
                            <label for="quantity-{{ $court->id }}" class="block text-xs font-semibold text-gray-500 mb-1">Durasi Sewa (jam) {{ $court->name }}</label>
                            {{-- Nama input items[ID] membuat server menerima pasangan ID lapangan dan durasi sewa. --}}
                            <input id="quantity-{{ $court->id }}" type="number" name="items[{{ $court->id }}]" min="0" max="4" step="1" value="{{ old('items.'.$court->id, 0) }}" data-name="{{ $court->name }}" data-price="{{ $court->price_per_hour }}" class="item-qty w-full border rounded-lg px-3 py-2 text-center font-bold">
                        </div>
                    </article>
                @empty
                    <p class="text-gray-500 md:col-span-3 text-center">Belum ada lapangan tersedia.</p>
                @endforelse
            </div>

            <section class="bg-white p-6 rounded-xl shadow-sm border mt-6">
                <h2 class="text-lg font-bold text-gray-700 mb-4 pb-2 border-b">3. Jadwal Main</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label for="booking_date" class="block text-sm font-semibold mb-1">Tanggal main</label>
                        <input id="booking_date" name="booking_date" type="date" required min="{{ today()->format('Y-m-d') }}" max="{{ today()->addMonths(3)->format('Y-m-d') }}" value="{{ old('booking_date', $date) }}" class="w-full border rounded-lg px-4 py-2"></div>
                    <div><label for="start_time" class="block text-sm font-semibold mb-1">Jam mulai</label>
                        <select id="start_time" name="start_time" required class="w-full border rounded-lg px-4 py-2">
                            <option value="">Pilih jam mulai</option>
                            @foreach (range(8, 21) as $hour)
                                @php($time = sprintf('%02d:00', $hour))
                                <option value="{{ $time }}" @selected(old('start_time') === $time)>{{ $time }}</option>
                            @endforeach
                        </select></div>
                </div>
                <p class="text-xs text-gray-500 mt-3">Jadwal berlaku untuk semua lapangan yang dipilih. Durasi 1–4 jam per lapangan, selesai paling lambat pukul 22.00. Isi 0 untuk lapangan yang tidak dipesan.</p>
            </section>
            <div class="mt-8 text-right">
                <button id="reviewOrder" type="button" @disabled($courts->isEmpty()) class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold px-8 py-3 rounded-xl">Reservasi Sekarang</button>
            </div>

            {{-- Dialog berada di dalam form supaya tombol submit mengirim data dan token CSRF. --}}
            <dialog id="confirmModal" aria-labelledby="confirmTitle" class="rounded-2xl max-w-md w-full p-6 shadow-2xl backdrop:bg-black/50">
                <h3 id="confirmTitle" class="text-xl font-bold border-b pb-3 mb-4">Konfirmasi Reservasi</h3>
                <p class="mb-2">Nama: <strong id="modalName"></strong></p>
                <p class="mb-4">Telepon: <strong id="modalPhone"></strong></p>
                <p class="mb-4">Jadwal: <strong id="modalSchedule"></strong></p>
                <div class="border-y py-3 mb-4 max-h-48 overflow-y-auto">
                    <p class="font-semibold text-xs text-gray-500 uppercase mb-2">Rincian Item</p>
                    <ul id="modalItemList" class="space-y-2 text-sm"></ul>
                </div>
                <p class="flex justify-between font-bold mb-6">Total Pembayaran: <span id="modalTotalPrice" class="text-green-600"></span></p>
                <div class="flex gap-3">
                    <button id="cancelOrder" type="button" class="w-1/2 bg-gray-200 font-semibold py-3 rounded-xl">Batal</button>
                    <button type="submit" class="w-1/2 bg-green-600 text-white font-bold py-3 rounded-xl">Ya, Kirim Reservasi</button>
                </div>
            </dialog>
        </form>
    </main>
</body>
</html>

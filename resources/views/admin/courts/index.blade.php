{{-- Daftar lapangan admin dengan foto, tautan edit, formulir hapus, dan navigasi halaman. --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Lapangan</h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <a href="{{ route('courts.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-4 inline-block font-semibold">+ Tambah Lapangan</a>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        @if($errors->any())<div role="alert" class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ $errors->first() }}</div>@endif
        <div class="overflow-x-auto">
        <table class="w-full bg-white border mt-4 shadow-sm rounded-lg overflow-hidden">
            <thead>
                <tr class="bg-gray-100 border-b text-gray-600 text-sm">
                    <th class="p-3">Gambar</th>
                    <th class="p-3">Nama</th>
                    <th class="p-3">Kategori</th>
                    <th class="p-3">Harga</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- Data berasal dari pagination controller; satu baris menampilkan satu lapangan. --}}
                @forelse($courts as $court)
                <tr class="border-b text-center text-sm">
                    <td class="p-3">
                        @if($court->image)
                            <img src="{{ asset('storage/' . $court->image) }}" alt="{{ $court->name }}" class="w-16 h-16 object-cover mx-auto rounded">
                        @else
                            <span class="text-gray-400 text-xs">No Image</span>
                        @endif
                    </td>
                    <td class="p-3 font-semibold">{{ $court->name }}</td>
                    <td class="p-3">{{ $court->type }}</td>
                    <td class="p-3 font-bold text-green-600">Rp {{ number_format($court->price_per_hour) }}</td>
                    <td class="p-3">
                        <a href="{{ route('courts.edit', $court->id) }}" class="text-blue-600 hover:underline mr-3 font-semibold">Edit</a>
                        {{-- Penghapusan memakai form dengan token CSRF dan metode DELETE. --}}
                        <form action="{{ route('courts.destroy', $court->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Yakin hapus data ini?')" class="text-red-600 hover:underline font-semibold">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                    <tr><td colspan="5" class="p-6 text-center text-gray-500">Belum ada lapangan tersedia.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        {{-- Tampilkan tautan halaman berdasarkan hasil paginate dari controller. --}}
        <div class="mt-4">{{ $courts->links() }}</div>
    </div>
</x-app-layout>

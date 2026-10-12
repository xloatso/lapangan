<?php

// Mengelola daftar lapangan, validasi data, serta penyimpanan dan penghapusan foto lapangan.

namespace App\Http\Controllers;

use App\Models\Courts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourtsController extends Controller
{
    // Ambil lapangan terbaru sebanyak 10 per halaman untuk tabel admin.
    public function index()
    {
        $courts = Courts::latest()->paginate(10);
        return view('admin.courts.index', compact('courts'));
    }

    // Tampilkan formulir kosong untuk menambahkan lapangan.
    public function create()
    {
        return view('admin.courts.create', ['court' => new Courts]);
    }

    // Validasi data dan foto, lalu simpan lapangan baru ke database.
    public function store(Request $request)
    {
        // Batasi kategori, harga nonnegatif, dan foto JPEG/PNG maksimal 2048 KB.
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:futsal,badminton,basket',
            'price_per_hour' => 'required|integer|min:1000|max:10000000',
            'description' => 'required|string|max:3000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Menu boleh tanpa foto; database hanya menyimpan lokasi file pada disk public.
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('courts', 'public');
        }

        Courts::create([
            'name' => $request->name,
            'type' => $request->type,
            'price_per_hour' => $request->price_per_hour,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return redirect()->route('courts.index')->with('success', 'Data lapangan berhasil ditambahkan!');
    }

    // Laravel mengambil lapangan dari parameter route sebelum formulir edit ditampilkan.
    public function edit(Courts $court)
    {
        return view('admin.courts.edit', compact('court'));
    }

    // Perbarui data lapangan dan ganti foto hanya jika ada unggahan baru.
    public function update(Request $request, Courts $court)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:futsal,badminton,basket',
            'price_per_hour' => 'required|integer|min:1000|max:10000000',
            'description' => 'required|string|max:3000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Pertahankan foto lama bila form tidak menyertakan foto pengganti.
        $imagePath = $court->image;
        if ($request->hasFile('image')) {
            if ($court->image && Storage::disk('public')->exists($court->image)) {
                Storage::disk('public')->delete($court->image);
            }
            $imagePath = $request->file('image')->store('courts', 'public');
        }

        $court->update([
            'name' => $request->name,
            'type' => $request->type,
            'price_per_hour' => $request->price_per_hour,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return redirect()->route('courts.index')->with('success', 'Data lapangan berhasil diperbarui!');
    }

    // Hapus file foto yang tersimpan, kemudian hapus data lapangan.
    public function destroy(Courts $court)
    {
        if ($court->details()->exists()) {
            return back()->withErrors(['court' => 'Lapangan memiliki riwayat reservasi sehingga tidak dapat dihapus.']);
        }
        if ($court->image && Storage::disk('public')->exists($court->image)) {
            Storage::disk('public')->delete($court->image);
        }
        $court->delete();

        return redirect()->route('courts.index')->with('success', 'Data lapangan berhasil dihapus!');
    }
}

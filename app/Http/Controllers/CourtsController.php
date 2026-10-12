<?php

namespace App\Http\Controllers;

use App\Models\Courts;
use Illuminate\Http\Request;

class CourtsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courts = Courts::latest()->paginate(12);

        return view('admin.courts.index', compact('courts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.courts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        request()->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:futsal,badminton,basket,voli',
            'price_per_hour' => 'required|numeric|min:0',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
  

         $imagePath = null;

        if ($request->hasFile('image')) {

            $imagePath = $request->file('image')->store('courts', 'public');
        }
            Courts::create([
            'name'        => $request->name,

            'type'    => $request->type,

            'price_per_hour'       => $request->price_per_hour,

            'description' => $request->description,

            'image'       => $imagePath,
        ]);

        return redirect()->route('courts.index')
            ->with('success', 'Data lapangan berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Courts $courts)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Courts $courts)
    {
        return view('admin.courts.edit', compact('courts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Courts $courts)
    {
        $request->validate([

            'name'        => 'required|string|max:255',

            'type'    => 'required|in:futsal,badminton,basket,voli',

            'price_per_hour'       => 'required|numeric|min:0',

            'description' => 'required|string',

            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $imagePath = $courts->image;

        if ($request->hasFile('image')) {

            if ($courts->image && Storage::disk('public')->exists($courts->image)) {

                Storage::disk('public')->delete($courts->image);
            }

            $imagePath = $request->file('image')->store('courts', 'public');
        }

        $courts->update([

            'name'        => $request->name,

            'type'    => $request->type,

            'price_per_hour'       => $request->price_per_hour,

            'description' => $request->description,

            'image'       => $imagePath,
        ]);

        return redirect()->route('courts.index')
            ->with('success', 'Data lapangan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Courts $courts)
    {
        if ($courts->image && Storage::disk('public')->exists($courts->image)) {

            Storage::disk('public')->delete($courts->image);
        }

        $courts->delete();

        return redirect()->route('courts.index')
            ->with('success', 'Data lapangan berhasil dihapus!');
    }
}

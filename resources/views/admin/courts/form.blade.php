@csrf
@if($court->exists) @method('PUT') @endif
@if($errors->any())
    <ul role="alert" class="bg-red-100 text-red-700 p-4 rounded mb-4 list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
@endif
<div class="mb-4">
    <label for="name" class="block font-medium mb-1">Nama Lapangan</label>
    <input id="name" type="text" name="name" value="{{ old('name', $court->name) }}" maxlength="100" class="w-full border rounded p-2" required>
</div>
<div class="mb-4">
    <label for="type" class="block font-medium mb-1">Kategori</label>
    <select id="type" name="type" class="w-full border rounded p-2" required>
        @foreach(['futsal' => 'Futsal', 'badminton' => 'Badminton', 'basket' => 'Basket'] as $value => $label)
            <option value="{{ $value }}" @selected(old('type', $court->type) === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="mb-4">
    <label for="price_per_hour" class="block font-medium mb-1">Tarif per Jam (Rp)</label>
    <input id="price_per_hour" type="number" name="price_per_hour" value="{{ old('price_per_hour', $court->price_per_hour) }}" min="1000" max="10000000" step="1" class="w-full border rounded p-2" required>
</div>
<div class="mb-4">
    <label for="description" class="block font-medium mb-1">Deskripsi</label>
    <textarea id="description" name="description" class="w-full border rounded p-2" rows="3" maxlength="3000" required>{{ old('description', $court->description) }}</textarea>
</div>
<div class="mb-4">
    <label for="image" class="block font-medium mb-1">{{ $court->exists ? 'Gambar Baru (Opsional)' : 'Gambar Lapangan' }}</label>
    @if($court->image)<img src="{{ asset('storage/'.$court->image) }}" alt="{{ $court->name }}" class="h-24 object-contain mb-2">@endif
    <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="w-full border rounded p-2">
</div>
<button type="submit" class="{{ $court->exists ? 'bg-blue-600 hover:bg-blue-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-6 py-2.5 rounded font-semibold">{{ $court->exists ? 'Update Data' : 'Simpan Data' }}</button>
<a href="{{ route('courts.index') }}" class="ml-3 text-sm text-gray-600">Batal</a>

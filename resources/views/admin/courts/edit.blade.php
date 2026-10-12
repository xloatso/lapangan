<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Lapangan</h2></x-slot>
    <div class="py-12 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <form action="{{ route('courts.update', $court) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-sm border">
            @include('admin.courts.form')
        </form>
    </div>
</x-app-layout>

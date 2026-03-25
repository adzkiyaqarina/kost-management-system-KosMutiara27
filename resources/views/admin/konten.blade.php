@extends('layouts.admin')

@section('header')
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Kelola Konten Website') }}
            </h2>
            <p class="text-sm text-gray-500">Atur seluruh konten yang ditampilkan di halaman welcome/landing page.</p>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Navigation Tabs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('admin.konten.edit-hero') }}" class="p-6 rounded-lg border-2 border-gray-200 hover:border-emerald-600 hover:bg-emerald-50 transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:bg-emerald-600 transition">
                    <svg class="w-6 h-6 text-emerald-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900">Hero Section</h3>
            </div>
            <p class="text-sm text-gray-600">Atur banner, judul, & tombol utama</p>
        </a>

        <a href="{{ route('admin.konten.edit-gallery') }}" class="p-6 rounded-lg border-2 border-gray-200 hover:border-blue-600 hover:bg-blue-50 transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-600 transition">
                    <svg class="w-6 h-6 text-blue-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-4 4m0 0l-4-4m4 4v6"></path>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900">Galeri</h3>
            </div>
            <p class="text-sm text-gray-600">Kelola foto dan video carousel</p>
        </a>

        <a href="{{ route('admin.konten.edit-facilities') }}" class="p-6 rounded-lg border-2 border-gray-200 hover:border-amber-600 hover:bg-amber-50 transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center group-hover:bg-amber-600 transition">
                    <svg class="w-6 h-6 text-amber-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900">Fasilitas</h3>
            </div>
            <p class="text-sm text-gray-600">Daftar fasilitas & keunggulan</p>
        </a>

        <a href="{{ route('admin.konten.edit-contact') }}" class="p-6 rounded-lg border-2 border-gray-200 hover:border-red-600 hover:bg-red-50 transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center group-hover:bg-red-600 transition">
                    <svg class="w-6 h-6 text-red-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900">Kontak</h3>
            </div>
            <p class="text-sm text-gray-600">Lokasi, telepon & informasi</p>
        </a>
    </div>

    <!-- Info Box -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
        <p class="text-sm text-blue-900">
            <strong>💡 Catatan:</strong> Perubahan konten akan langsung ditampilkan di halaman welcome. "Pilihan Tipe Kamar" diatur melalui fitur Master Harga & Tipe di pengaturan owner.
        </p>
    </div>

    @if($errors && $errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-8 text-red-900">
            <ul>
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection

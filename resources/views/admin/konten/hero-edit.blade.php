@extends('layouts.admin')

@section('content')
<div class="px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Hero Section</h1>
            <p class="text-gray-600 mt-2">Atur banner, judul, deskripsi, dan tombol utama halaman welcome</p>
        </div>
        <a href="{{ route('admin.konten.index') }}" class="text-gray-600 hover:text-gray-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
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

    <form action="{{ route('admin.konten.update-hero') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- Badge -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <label class="block text-sm font-semibold text-gray-900 mb-2">Badge (Opsional)</label>
            <input type="text" name="badge" value="{{ $items->firstWhere('item_type', 'badge')?->value ?? '✨ Hunian Eksklusif & Nyaman' }}" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                   placeholder="✨ Hunian Eksklusif & Nyaman">
            <p class="text-sm text-gray-600 mt-2">Teks kecil di atas judul utama</p>
        </div>

        <!-- Title -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <label class="block text-sm font-semibold text-gray-900 mb-2">Judul Utama *</label>
            <textarea name="title" rows="3" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                   placeholder="Temukan Kenyamanan Seperti di Rumah Sendiri.">{{ $items->firstWhere('item_type', 'title')?->value ?? 'Temukan Kenyamanan Seperti di Rumah Sendiri.' }}</textarea>
            <p class="text-sm text-gray-600 mt-2">Judul besar yang menarik perhatian pengunjung</p>
        </div>

        <!-- Subtitle -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <label class="block text-sm font-semibold text-gray-900 mb-2">Subtitle (Teks Berwarna Hijau) *</label>
            <input type="text" name="subtitle" required value="{{ $items->firstWhere('item_type', 'subtitle')?->value ?? 'Rumah Sendiri.' }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                   placeholder="Rumah Sendiri.">
            <p class="text-sm text-gray-600 mt-2">Teks yang akan ditampilkan dengan warna hijau di dalam judul</p>
        </div>

        <!-- Description -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <label class="block text-sm font-semibold text-gray-900 mb-2">Deskripsi *</label>
            <textarea name="description" rows="4" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                   placeholder="Fasilitas lengkap, lokasi strategis, dan keamanan 24 jam. Pilihan tepat untuk mahasiswa dan profesional muda.">{{ $items->firstWhere('item_type', 'description')?->value ?? 'Fasilitas lengkap, lokasi strategis, dan keamanan 24 jam. Pilihan tepat untuk mahasiswa dan profesional muda.' }}</textarea>
        </div>

        <!-- CTA Button -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Tombol Utama (CTA)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-700 mb-2">Teks Tombol *</label>
                    <input type="text" name="cta_button_text" required 
                           value="{{ $items->firstWhere('item_type', 'cta_button_text')?->value ?? 'Lihat Ketersediaan' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           placeholder="Lihat Ketersediaan">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-2">URL/Link Tombol *</label>
                    <input type="text" name="cta_button_url" required 
                           value="{{ $items->firstWhere('item_type', 'cta_button_url')?->value ?? '#kamar' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           placeholder="#kamar">
                </div>
            </div>
        </div>

        <!-- Secondary Button -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Tombol Sekunder (Opsional)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-700 mb-2">Teks Tombol</label>
                    <input type="text" name="secondary_button_text" 
                           value="{{ $items->firstWhere('item_type', 'secondary_button_text')?->value ?? '' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           placeholder="Hubungi Kami">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-2">URL/Link Tombol</label>
                    <input type="text" name="secondary_button_url" 
                           value="{{ $items->firstWhere('item_type', 'secondary_button_url')?->value ?? '#kontak' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           placeholder="#kontak">
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Statistik (Bagian Bawah Hero)</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-700 mb-2">Angka Statistik 1</label>
                    <input type="text" name="stat_1_value" 
                           value="{{ $items->firstWhere('item_type', 'stat_1_value')?->value ?? '200+' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           placeholder="200+">
                    <input type="text" name="stat_1_label" 
                           value="{{ $items->firstWhere('item_type', 'stat_1_label')?->value ?? 'Unit Kamar' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent mt-2"
                           placeholder="Unit Kamar">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-2">Angka Statistik 2</label>
                    <input type="text" name="stat_2_value" 
                           value="{{ $items->firstWhere('item_type', 'stat_2_value')?->value ?? '100%' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           placeholder="100%">
                    <input type="text" name="stat_2_label" 
                           value="{{ $items->firstWhere('item_type', 'stat_2_label')?->value ?? 'Aman & CCTV' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent mt-2"
                           placeholder="Aman & CCTV">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-2">Angka Statistik 3</label>
                    <input type="text" name="stat_3_value" 
                           value="{{ $items->firstWhere('item_type', 'stat_3_value')?->value ?? '4.9' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           placeholder="4.9">
                    <input type="text" name="stat_3_label" 
                           value="{{ $items->firstWhere('item_type', 'stat_3_label')?->value ?? 'Rating Penghuni' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent mt-2"
                           placeholder="Rating Penghuni">
                </div>
            </div>
        </div>

        <!-- Verification Badge -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Badge Verifikasi (Di Atas Gambar)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-700 mb-2">Judul Badge</label>
                    <input type="text" name="verify_badge_title" 
                           value="{{ $items->firstWhere('item_type', 'verify_badge_title')?->value ?? 'Terverifikasi' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           placeholder="Terverifikasi">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-2">Deskripsi Badge</label>
                    <input type="text" name="verify_badge_desc" 
                           value="{{ $items->firstWhere('item_type', 'verify_badge_desc')?->value ?? 'Kebersihan & Fasilitas Terjamin' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           placeholder="Kebersihan & Fasilitas Terjamin">
                </div>
            </div>
        </div>

        <!-- Hero Image -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <label class="block text-sm font-semibold text-gray-900 mb-4">Gambar Hero (Sebelah Kanan)</label>
            
            @php
                $currentHeroImage = $items->firstWhere('item_type', 'hero_image_path')?->value;
            @endphp
            
            @if($currentHeroImage)
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600 mb-2 font-medium">Gambar Saat Ini:</p>
                    <img src="{{ asset('storage/' . $currentHeroImage) }}" alt="Current Hero" class="w-48 h-32 object-cover rounded-lg shadow">
                </div>
            @endif
            
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-emerald-500 hover:bg-emerald-50 transition" id="imageDropZone">
                <input type="file" name="hero_image" accept="image/*" class="hidden" id="heroImageInput">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-gray-600 font-medium" id="dropZoneText">Klik atau drag gambar ke sini untuk {{ $currentHeroImage ? 'mengganti' : 'upload' }}</p>
                <p class="text-sm text-gray-500 mt-2">PNG, JPG, GIF (Max 5MB)</p>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex gap-4">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-lg transition">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.konten.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-900 font-bold py-3 px-8 rounded-lg transition">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
    const dropZone = document.getElementById('imageDropZone');
    const imageInput = document.getElementById('heroImageInput');

    dropZone.addEventListener('click', () => imageInput.click());
    
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-emerald-500', 'bg-emerald-50');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-emerald-500', 'bg-emerald-50');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        imageInput.files = e.dataTransfer.files;
        dropZone.classList.remove('border-emerald-500', 'bg-emerald-50');
    });
</script>
@endsection

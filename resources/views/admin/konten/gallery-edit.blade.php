@extends('layouts.admin')

@section('content')
<div class="px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Galeri</h1>
            <p class="text-gray-600 mt-2">Kelola foto dan video carousel untuk setiap kategori</p>
        </div>
        <a href="{{ route('admin.konten.index') }}" class="text-gray-600 hover:text-gray-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
    </div>

    <!-- Section Title Settings -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
        <h3 class="font-bold text-blue-900 mb-4">Pengaturan Judul Section Galeri</h3>
        <form action="{{ route('admin.konten.update-gallery-section') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-blue-800 mb-1">Judul Section</label>
                <input type="text" name="gallery_section_title" 
                       value="{{ $section->content['title'] ?? 'Galeri Kos Mutiara27' }}"
                       class="w-full px-4 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-blue-800 mb-1">Subtitle Section</label>
                <input type="text" name="gallery_section_subtitle" 
                       value="{{ $section->content['subtitle'] ?? 'Lihat suasana dan fasilitas lengkap di area hunian kami' }}"
                       class="w-full px-4 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">
                    Simpan Judul Section
                </button>
            </div>
        </form>
    </div>

    <!-- Add Gallery Form -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Tambah Galeri Baru</h2>
        <form action="{{ route('admin.konten.store-gallery') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Category -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Kategori *</label>
                    <select name="category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Pilih Kategori</option>
                        <option value="tampak_depan">Tampak Depan</option>
                        <option value="living_room">Ruang Tamu</option>
                        <option value="bedroom">Kamar Tidur</option>
                        <option value="kitchen">Dapur</option>
                        <option value="bathroom">Kamar Mandi</option>
                        <option value="outdoor">Area Outdoor</option>
                    </select>
                </div>

                <!-- Title -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Judul Galeri *</label>
                    <input type="text" name="title" required placeholder="Contoh: Tampak Depan"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Deskripsi (Opsional)</label>
                <textarea name="description" rows="3" placeholder="Deskripsi singkat tentang galeri ini"
                         class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <!-- Images Upload -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Unggah Foto (Multiple) *</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition" id="galleryDropZone">
                    <input type="file" name="images[]" accept="image/*" multiple required class="hidden" id="galleryImageInput">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <p class="text-gray-600 font-medium">Klik atau drag gambar ke sini</p>
                    <p class="text-sm text-gray-500 mt-2">Bisa upload multiple gambar sekaligus</p>
                </div>
                <p class="text-xs text-gray-500 mt-2" id="imageCount">0 gambar dipilih</p>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition">
                Simpan Galeri
            </button>
        </form>
    </div>

    <!-- Existing Galleries -->
    <div class="space-y-6">
        <h2 class="text-2xl font-bold text-gray-900">Galeri yang Ada ({{ $galleries->count() }} item)</h2>

        @forelse($galleries as $gallery)
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ $gallery->title }}</h3>
                        <p class="text-sm text-gray-600">Kategori: <span class="font-medium capitalize">
                            @switch($gallery->category)
                                @case('tampak_depan') Tampak Depan @break
                                @case('living_room') Ruang Tamu @break
                                @case('bedroom') Kamar Tidur @break
                                @case('kitchen') Dapur @break
                                @case('bathroom') Kamar Mandi @break
                                @case('outdoor') Area Outdoor @break
                                @default {{ $gallery->category }}
                            @endswitch
                        </span></p>
                        <p class="text-xs text-gray-400 mt-1">Status: {{ $gallery->is_active ? '✅ Aktif' : '❌ Tidak Aktif' }}</p>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="editGallery({{ $gallery->id }}, '{{ addslashes($gallery->title) }}', '{{ addslashes($gallery->description ?? '') }}')" 
                                class="text-blue-600 hover:text-blue-800 font-semibold">Edit</button>
                        <form action="{{ route('admin.konten.delete-gallery', $gallery) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus galeri ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">Hapus</button>
                        </form>
                    </div>
                </div>

                @if($gallery->description)
                    <p class="text-gray-600 mb-4">{{ $gallery->description }}</p>
                @endif

                <!-- Images Preview -->
                @if($gallery->images && count($gallery->images) > 0)
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach($gallery->images as $image)
                            <div class="relative rounded-lg overflow-hidden h-32 bg-gray-100">
                                <img src="{{ asset('storage/' . $image) }}" alt="Gallery" class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 italic">Belum ada gambar</p>
                @endif
            </div>
        @empty
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <p class="text-gray-600">Belum ada galeri. Tambahkan galeri baru di atas.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full mx-4 p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900">Edit Galeri</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PATCH')
            
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Judul Galeri</label>
                <input type="text" name="title" id="editTitle" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Deskripsi</label>
                <textarea name="description" id="editDescription" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Tambah Gambar Baru (Opsional)</label>
                <input type="file" name="images[]" multiple accept="image/*"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <p class="text-xs text-gray-500 mt-1">Gambar baru akan ditambahkan ke galeri</p>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                    Simpan Perubahan
                </button>
                <button type="button" onclick="closeEditModal()" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-lg transition">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const dropZone = document.getElementById('galleryDropZone');
    const imageInput = document.getElementById('galleryImageInput');
    const imageCount = document.getElementById('imageCount');

    dropZone.addEventListener('click', () => imageInput.click());
    
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        imageInput.files = e.dataTransfer.files;
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        updateImageCount();
    });

    imageInput.addEventListener('change', updateImageCount);

    function updateImageCount() {
        imageCount.textContent = imageInput.files.length + ' gambar dipilih';
    }

    function editGallery(id, title, description) {
        document.getElementById('editTitle').value = title;
        document.getElementById('editDescription').value = description;
        document.getElementById('editForm').action = `/admin/konten/gallery/${id}`;
        
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
</script>
@endsection


@extends('layouts.admin')

@section('content')
<div class="px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Informasi Kontak</h1>
            <p class="text-gray-600 mt-2">Kelola lokasi, telepon, email, dan maps embed</p>
        </div>
        <a href="{{ route('admin.konten.index') }}" class="text-gray-600 hover:text-gray-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
    </div>

    <!-- Section Title Settings -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8">
        <h3 class="font-bold text-red-900 mb-4">Pengaturan Judul Section Kontak</h3>
        <form action="{{ route('admin.konten.update-contact-section') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-red-800 mb-1">Judul Section</label>
                <input type="text" name="contact_section_title" 
                       value="{{ $section->content['title'] ?? 'Lokasi & Kontak' }}"
                       class="w-full px-4 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-red-800 mb-1">Subtitle Section</label>
                <input type="text" name="contact_section_subtitle" 
                       value="{{ $section->content['subtitle'] ?? 'Temukan kami dengan mudah dan hubungi untuk informasi lebih lanjut' }}"
                       class="w-full px-4 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition">
                    Simpan Judul Section
                </button>
            </div>
        </form>
    </div>

    <!-- Add Contact Form -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Tambah Informasi Kontak</h2>
        <form action="{{ route('admin.konten.store-contact') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Contact Type -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Jenis Kontak *</label>
                <select name="contact_type" id="contactType" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <option value="">Pilih Jenis Kontak</option>
                    <option value="address">Alamat</option>
                    <option value="phone">Nomor Telepon / WhatsApp</option>
                    <option value="email">Email</option>
                    <option value="maps_embed">Maps Embed (iframe)</option>
                </select>
            </div>

            <!-- Label -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Label / Judul *</label>
                <input type="text" name="label" required placeholder="Contoh: Kantor Utama, WhatsApp Kami, Email Resmi"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
            </div>

            <!-- Contact Value -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Isi Kontak *</label>
                <textarea id="contactValue" name="contact_value" rows="4" required placeholder="Masukkan alamat, nomor telepon, email, atau iframe code"
                         class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent font-mono text-sm"></textarea>
                <p class="text-xs text-gray-500 mt-2" id="contactHint">
                    Untuk Alamat: Masukkan alamat lengkap<br>
                    Untuk Telepon: Masukkan nomor dengan format 0812xxxxxxx atau +62812xxxxxxx<br>
                    Untuk Email: Masukkan alamat email<br>
                    Untuk Maps: Masukkan kode iframe dari Google Maps embed
                </p>
            </div>

            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg transition">
                Simpan Informasi Kontak
            </button>
        </form>
    </div>

    <!-- Existing Contacts -->
    <div class="space-y-4">
        <h2 class="text-2xl font-bold text-gray-900">Informasi Kontak yang Ada</h2>

        @forelse($contacts as $contact)
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-red-100">
                                @switch($contact->contact_type)
                                    @case('address')
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        </svg>
                                        @break
                                    @case('phone')
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        @break
                                    @case('email')
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        @break
                                    @case('maps_embed')
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7.882"></path>
                                        </svg>
                                        @break
                                @endswitch
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $contact->label }}</h3>
                                <p class="text-xs text-gray-500">
                                    @switch($contact->contact_type)
                                        @case('address') Alamat @break
                                        @case('phone') Telepon / WhatsApp @break
                                        @case('email') Email @break
                                        @case('maps_embed') Google Maps @break
                                    @endswitch
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 bg-gray-50 rounded p-4">
                            @if($contact->contact_type === 'maps_embed')
                                <p class="text-xs text-gray-500 mb-2">Embed Code:</p>
                                <code class="text-xs text-gray-600 break-all">{{ Str::limit($contact->contact_value, 100) }}</code>
                            @else
                                <p class="text-gray-700 font-medium">{{ $contact->contact_value }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button onclick="editContact({{ $contact->id }}, '{{ $contact->contact_type }}', '{{ $contact->label }}', '{{ addslashes($contact->contact_value) }}')" class="text-blue-600 hover:text-blue-800 font-semibold">Edit</button>
                        <form action="{{ route('admin.konten.delete-contact', $contact) }}" method="POST" class="inline-block" onsubmit="confirmSubmit(event, 'Hapus kontak ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <p class="text-gray-600">Belum ada informasi kontak. Tambahkan di atas.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full mx-4 p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900">Edit Informasi Kontak</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="editForm" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="contact_id" id="editContactId">
            
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Jenis Kontak</label>
                <p id="editContactType" class="px-4 py-2 bg-gray-100 rounded-lg text-gray-700 font-medium"></p>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Label / Judul</label>
                <input type="text" name="label" id="editLabel" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Isi Kontak</label>
                <textarea name="contact_value" id="editContactValue" rows="5"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent font-mono text-sm"></textarea>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition">
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
    const contactTypeSelect = document.getElementById('contactType');
    const contactHint = document.getElementById('contactHint');
    const hints = {
        address: 'Untuk Alamat: Masukkan alamat lengkap',
        phone: 'Untuk Telepon: Masukkan nomor dengan format 0812xxxxxxx atau +62812xxxxxxx',
        email: 'Untuk Email: Masukkan alamat email',
        maps_embed: 'Untuk Maps: Paste kode iframe dari Google Maps embed'
    };

    const typeLabels = {
        address: 'Alamat',
        phone: 'Telepon / WhatsApp',
        email: 'Email',
        maps_embed: 'Google Maps Embed'
    };

    contactTypeSelect.addEventListener('change', function() {
        contactHint.textContent = hints[this.value] || '';
    });

    function editContact(id, type, label, value) {
        document.getElementById('editContactId').value = id;
        document.getElementById('editContactType').textContent = typeLabels[type] || type;
        document.getElementById('editLabel').value = label;
        document.getElementById('editContactValue').value = value;
        document.getElementById('editForm').action = `/admin/konten/contact/${id}`;
        
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close modal when clicking outside
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });
</script>
@endsection

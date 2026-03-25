<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentSection;
use App\Models\ContentItem;
use App\Models\ContentGallery;
use App\Models\ContentFacility;
use App\Models\ContentContact;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ContentController extends Controller
{
    public function index(): View
    {
        $sections = ContentSection::where('owner_id', Auth::id())->get();
        return view('admin.konten', ['sections' => $sections]);
    }

    // Hero Section
    public function editHero(): View
    {
        $section = ContentSection::getOrCreateSection('hero', 'Hero Section');
        $items = $section->items()->orderBy('sort_order')->get();
        return view('admin.konten.hero-edit', ['section' => $section, 'items' => $items]);
    }

    public function updateHero(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'badge' => 'nullable|string|max:255',
            'title' => 'required|string|max:500',
            'subtitle' => 'required|string',
            'description' => 'required|string',
            'cta_button_text' => 'required|string|max:100',
            'cta_button_url' => 'required|string|max:255',
            'secondary_button_text' => 'nullable|string|max:100',
            'secondary_button_url' => 'nullable|string|max:255',
            'stat_1_value' => 'nullable|string|max:50',
            'stat_1_label' => 'nullable|string|max:100',
            'stat_2_value' => 'nullable|string|max:50',
            'stat_2_label' => 'nullable|string|max:100',
            'stat_3_value' => 'nullable|string|max:50',
            'stat_3_label' => 'nullable|string|max:100',
            'verify_badge_title' => 'nullable|string|max:100',
            'verify_badge_desc' => 'nullable|string|max:255',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $section = ContentSection::getOrCreateSection('hero', 'Hero Section');
        
        // Handle image upload
        if ($request->hasFile('hero_image')) {
            $path = $request->file('hero_image')->store('hero', 'public');
            ContentItem::updateOrCreate(
                ['content_section_id' => $section->id, 'item_type' => 'hero_image_path'],
                ['value' => $path, 'sort_order' => 99]
            );
        }

        // Update or create items - including new statistics and badge fields
        $itemTypes = [
            'badge', 'title', 'subtitle', 'description', 
            'cta_button_text', 'cta_button_url', 
            'secondary_button_text', 'secondary_button_url',
            'stat_1_value', 'stat_1_label',
            'stat_2_value', 'stat_2_label',
            'stat_3_value', 'stat_3_label',
            'verify_badge_title', 'verify_badge_desc'
        ];
        
        foreach ($itemTypes as $type) {
            if (isset($validated[$type]) || $request->has($type)) {
                ContentItem::updateOrCreate(
                    ['content_section_id' => $section->id, 'item_type' => $type],
                    ['value' => $validated[$type] ?? $request->input($type, ''), 'sort_order' => array_search($type, $itemTypes)]
                );
            }
        }

        // Log activity
        \App\Services\LoggerService::log(
            'update_content',
            'Update Hero Section',
            $section,
            null,
            $validated
        );

        return redirect()->route('admin.konten.edit-hero')->with('success', 'Hero section updated successfully');
    }

    // Gallery Section
    public function editGallery(): View
    {
        $section = ContentSection::getOrCreateSection('gallery', 'Gallery Section');
        $galleries = $section->galleries()->orderBy('sort_order')->get();
        $categories = ['living_room', 'bedroom', 'kitchen', 'bathroom', 'workspace', 'outdoor'];
        return view('admin.konten.gallery-edit', ['section' => $section, 'galleries' => $galleries, 'categories' => $categories]);
    }

    public function storeGallery(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $section = ContentSection::getOrCreateSection('gallery', 'Gallery Section');
        
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('galleries', 'public');
                $images[] = $path;
            }
        }

        $gallery = ContentGallery::create([
            'content_section_id' => $section->id,
            'category' => $validated['category'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'images' => $images,
            'sort_order' => ContentGallery::where('content_section_id', $section->id)->max('sort_order') + 1,
            'is_active' => true,
        ]);

        // Log activity
        \App\Services\LoggerService::log(
            'create_content',
            'Tambah Gallery Baru: ' . $validated['title'],
            $gallery
        );

        return redirect()->route('admin.konten.edit-gallery')->with('success', 'Gallery added successfully');
    }

    public function updateGallery(Request $request, ContentGallery $gallery): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Append new images to existing ones
        if ($request->hasFile('images')) {
            $existingImages = $gallery->images ?? [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('galleries', 'public');
                $existingImages[] = $path;
            }
            $validated['images'] = $existingImages;
        }

        $gallery->update($validated);

        return redirect()->route('admin.konten.edit-gallery')->with('success', 'Galeri berhasil diperbarui');
    }

    public function updateGallerySection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gallery_section_title' => 'required|string|max:255',
            'gallery_section_subtitle' => 'nullable|string|max:500',
        ]);

        $section = ContentSection::getOrCreateSection('gallery', 'Gallery Section');
        
        $content = $section->content ?? [];
        $content['title'] = $validated['gallery_section_title'];
        $content['subtitle'] = $validated['gallery_section_subtitle'] ?? '';
        
        $section->update(['content' => $content]);

        return redirect()->route('admin.konten.edit-gallery')->with('success', 'Judul section galeri berhasil diperbarui');
    }

    public function deleteGallery(ContentGallery $gallery): RedirectResponse
    {
        $title = $gallery->title;
        
        \App\Services\LoggerService::log(
            'delete_content',
            'Hapus Gallery: ' . $title,
            null
        );

        $gallery->delete();
        return redirect()->route('admin.konten.edit-gallery')->with('success', 'Gallery deleted successfully');
    }

    // Facilities Section
    public function editFacilities(): View
    {
        $section = ContentSection::getOrCreateSection('facilities', 'Facilities Section');
        $facilities = $section->facilities()->orderBy('sort_order')->get();
        $colors = ['emerald', 'blue', 'amber', 'red', 'purple', 'green'];
        return view('admin.konten.facilities-edit', ['section' => $section, 'facilities' => $facilities, 'colors' => $colors]);
    }

    public function storeFacility(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'facility_name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'required|string|max:50',
            'icon_color' => 'required|string|max:50',
        ]);

        $section = ContentSection::getOrCreateSection('facilities', 'Facilities Section');
        
        ContentFacility::create([
            'content_section_id' => $section->id,
            'facility_name' => $validated['facility_name'],
            'description' => $validated['description'],
            'icon' => $validated['icon'],
            'icon_color' => $validated['icon_color'],
            'sort_order' => ContentFacility::where('content_section_id', $section->id)->max('sort_order') + 1,
            'is_active' => true,
        ]);

        return redirect()->route('admin.konten.edit-facilities')->with('success', 'Fasilitas berhasil ditambahkan');
    }

    public function updateFacility(Request $request, ContentFacility $facility): RedirectResponse
    {
        $validated = $request->validate([
            'facility_name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:50',
            'icon_color' => 'nullable|string|max:50',
        ]);

        $facility->update($validated);

        return redirect()->route('admin.konten.edit-facilities')->with('success', 'Fasilitas berhasil diperbarui');
    }

    public function updateFacilitiesSection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'facilities_section_title' => 'required|string|max:255',
            'facilities_section_subtitle' => 'nullable|string|max:500',
        ]);

        $section = ContentSection::getOrCreateSection('facilities', 'Facilities Section');
        
        $content = $section->content ?? [];
        $content['title'] = $validated['facilities_section_title'];
        $content['subtitle'] = $validated['facilities_section_subtitle'] ?? '';
        
        $section->update(['content' => $content]);

        return redirect()->route('admin.konten.edit-facilities')->with('success', 'Judul section fasilitas berhasil diperbarui');
    }

    public function deleteFacility(ContentFacility $facility): RedirectResponse
    {
        $facility->delete();
        return redirect()->route('admin.konten.edit-facilities')->with('success', 'Facility deleted successfully');
    }

    // Contact Section
    public function editContact(): View
    {
        $section = ContentSection::getOrCreateSection('contact', 'Contact Section');
        $contacts = $section->contacts()->orderBy('sort_order')->get();
        $contactTypes = ['address', 'phone', 'email', 'maps_embed'];
        return view('admin.konten.contact-edit', ['section' => $section, 'contacts' => $contacts, 'contactTypes' => $contactTypes]);
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_type' => 'required|string|in:address,phone,email,maps_embed',
            'contact_value' => 'required|string',
            'label' => 'nullable|string|max:255',
        ]);

        $section = ContentSection::getOrCreateSection('contact', 'Contact Section');
        
        ContentContact::create([
            'content_section_id' => $section->id,
            'contact_type' => $validated['contact_type'],
            'contact_value' => $validated['contact_value'],
            'label' => $validated['label'] ?? null,
            'sort_order' => ContentContact::where('content_section_id', $section->id)->max('sort_order') + 1,
            'is_active' => true,
        ]);

        return redirect()->route('admin.konten.edit-contact')->with('success', 'Contact information added successfully');
    }

    public function updateContact(Request $request, ContentContact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'contact_value' => 'required|string',
            'label' => 'nullable|string|max:255',
        ]);

        $contact->update($validated);

        return redirect()->route('admin.konten.edit-contact')->with('success', 'Contact information updated successfully');
    }

    public function deleteContact(ContentContact $contact): RedirectResponse
    {
        $contact->delete();
        return redirect()->route('admin.konten.edit-contact')->with('success', 'Informasi kontak berhasil dihapus');
    }

    public function updateContactSection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_section_title' => 'required|string|max:255',
            'contact_section_subtitle' => 'nullable|string|max:500',
        ]);

        $section = ContentSection::getOrCreateSection('contact', 'Contact Section');
        
        $content = $section->content ?? [];
        $content['title'] = $validated['contact_section_title'];
        $content['subtitle'] = $validated['contact_section_subtitle'] ?? '';
        
        $section->update(['content' => $content]);

        return redirect()->route('admin.konten.edit-contact')->with('success', 'Judul section kontak berhasil diperbarui');
    }
}

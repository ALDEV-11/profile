<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGalleryRequest;
use App\Http\Requests\UpdateGalleryRequest;
use App\Models\Gallery;
use App\Services\GalleryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function __construct(
        protected GalleryService $galleries,
    ) {
    }

    /**
     * Display a listing of gallery photos.
     */
    public function index(Request $request): View
    {
        $galleries = $this->galleries->list($request->user()->id, $request->only('q'));

        return view('admin.galleries.index', compact('galleries'));
    }

    /**
     * Show the form for uploading a new photo.
     */
    public function create(): View
    {
        return view('admin.galleries.create');
    }

    /**
     * Store a newly uploaded photo.
     */
    public function store(StoreGalleryRequest $request): RedirectResponse
    {
        $this->galleries->create($request->user()->id, $request->validated(), $request->file('image'));

        return redirect()->route('admin.galleries.index')
            ->with('success', 'Foto berhasil diunggah.');
    }

    /**
     * Show the form for editing the specified photo.
     */
    public function edit(Gallery $gallery): View
    {
        return view('admin.galleries.edit', compact('gallery'));
    }

    /**
     * Update the specified photo.
     */
    public function update(UpdateGalleryRequest $request, Gallery $gallery): RedirectResponse
    {
        $this->galleries->update($gallery, $request->validated(), $request->file('image'));

        return redirect()->route('admin.galleries.index')
            ->with('success', 'Foto berhasil diperbarui.');
    }

    /**
     * Remove (soft delete) the specified photo.
     */
    public function destroy(Gallery $gallery): RedirectResponse
    {
        $this->galleries->delete($gallery);

        return redirect()->route('admin.galleries.index')
            ->with('success', 'Foto berhasil dihapus.');
    }
}

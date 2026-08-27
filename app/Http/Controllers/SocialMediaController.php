<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocialMediaRequest;
use App\Http\Requests\UpdateSocialMediaRequest;
use App\Models\SocialMedia;
use App\Services\SocialMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocialMediaController extends Controller
{
    public function __construct(
        protected SocialMediaService $socialMedias,
    ) {
    }

    /**
     * Display a listing of social media links.
     */
    public function index(Request $request): View
    {
        $socialMedias = $this->socialMedias->list($request->user()->id, $request->only('q'));
        $canAdd = $socialMedias->count() < SocialMediaService::MAX_LINKS;

        return view('admin.social-media.index', compact('socialMedias', 'canAdd'));
    }

    /**
     * Show the form for creating a new link.
     */
    public function create(): View
    {
        if (SocialMediaService::MAX_LINKS > $this->socialMedias->list(auth()->id())->count()) {
            return view('admin.social-media.create');
        }

        return redirect()->route('admin.social-medias.index')
            ->with('error', 'Maksimal '.SocialMediaService::MAX_LINKS.' link sosial media.');
    }

    /**
     * Store a newly created link.
     */
    public function store(StoreSocialMediaRequest $request): RedirectResponse
    {
        $this->socialMedias->create($request->user()->id, $request->validated());

        return redirect()->route('admin.social-medias.index')
            ->with('success', 'Link sosial media berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified link.
     */
    public function edit(SocialMedia $socialMedia): View
    {
        return view('admin.social-media.edit', compact('socialMedia'));
    }

    /**
     * Update the specified link.
     */
    public function update(UpdateSocialMediaRequest $request, SocialMedia $socialMedia): RedirectResponse
    {
        $this->socialMedias->update($socialMedia, $request->validated());

        return redirect()->route('admin.social-medias.index')
            ->with('success', 'Link sosial media berhasil diperbarui.');
    }

    /**
     * Remove the specified link.
     */
    public function destroy(SocialMedia $socialMedia): RedirectResponse
    {
        $this->socialMedias->delete($socialMedia);

        return redirect()->route('admin.social-medias.index')
            ->with('success', 'Link sosial media berhasil dihapus.');
    }
}

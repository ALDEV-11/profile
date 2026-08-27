<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Services\ProfileService;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profiles,
    ) {
    }

    /**
     * Show the profile edit form pre-filled with current values.
     */
    public function edit(): View
    {
        $profile = $this->profiles->forUser(auth()->id());

        return view('admin.profile.edit', compact('profile'));
    }

    /**
     * Save profile changes, process an uploaded image if provided.
     */
    public function update(UpdateProfileRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        $profile = $this->profiles->forUser(auth()->id());

        $this->profiles->update(
            $profile,
            $data,
            $request->file('profile_image'),
            (bool) $request->boolean('remove_image'),
        );

        return redirect()->route('admin.profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}

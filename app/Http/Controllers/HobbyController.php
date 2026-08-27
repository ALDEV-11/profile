<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHobbyRequest;
use App\Models\Hobby;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HobbyController extends Controller
{
    /**
     * Display a listing of hobbies.
     */
    public function index(): View
    {
        $hobbies = Hobby::ordered()->get();

        return view('admin.hobbies.index', compact('hobbies'));
    }

    /**
     * Show the form for creating a new hobby.
     */
    public function create(): View
    {
        return view('admin.hobbies.create');
    }

    /**
     * Store a newly created hobby.
     */
    public function store(StoreHobbyRequest $request): RedirectResponse
    {
        Hobby::create($request->validated() + ['user_id' => $request->user()->id]);

        return redirect()->route('admin.hobbies.index')
            ->with('success', 'Hobby berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified hobby.
     */
    public function edit(Hobby $hobby): View
    {
        return view('admin.hobbies.edit', compact('hobby'));
    }

    /**
     * Update the specified hobby.
     */
    public function update(StoreHobbyRequest $request, Hobby $hobby): RedirectResponse
    {
        $hobby->update($request->validated());

        return redirect()->route('admin.hobbies.index')
            ->with('success', 'Hobby berhasil diperbarui.');
    }

    /**
     * Remove (soft delete) the specified hobby.
     */
    public function destroy(Hobby $hobby): RedirectResponse
    {
        $hobby->delete();

        return redirect()->route('admin.hobbies.index')
            ->with('success', 'Hobby berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEducationRequest;
use App\Http\Requests\UpdateEducationRequest;
use App\Models\Education;
use App\Services\EducationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EducationController extends Controller
{
    public function __construct(
        protected EducationService $educations,
    ) {
    }

    /**
     * Display a listing of educations.
     */
    public function index(Request $request): View
    {
        $educations = $this->educations->list($request->user()->id, $request->only('q'));

        return view('admin.educations.index', compact('educations'));
    }

    /**
     * Show the form for creating a new education.
     */
    public function create(): View
    {
        return view('admin.educations.create');
    }

    /**
     * Store a newly created education.
     */
    public function store(StoreEducationRequest $request): RedirectResponse
    {
        $this->educations->create($request->user()->id, $request->validated());

        return redirect()->route('admin.educations.index')
            ->with('success', 'Education berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified education.
     */
    public function edit(Education $education): View
    {
        return view('admin.educations.edit', compact('education'));
    }

    /**
     * Update the specified education.
     */
    public function update(UpdateEducationRequest $request, Education $education): RedirectResponse
    {
        $this->educations->update($education, $request->validated());

        return redirect()->route('admin.educations.index')
            ->with('success', 'Education berhasil diperbarui.');
    }

    /**
     * Remove (soft delete) the specified education.
     */
    public function destroy(Education $education): RedirectResponse
    {
        $this->educations->delete($education);

        return redirect()->route('admin.educations.index')
            ->with('success', 'Education berhasil dihapus.');
    }

    /**
     * Restore a soft-deleted education.
     */
    public function restore(int $education): RedirectResponse
    {
        $record = Education::withTrashed()->findOrFail($education);

        $this->educations->restore($record);

        return redirect()->route('admin.educations.index')
            ->with('success', 'Education berhasil dipulihkan.');
    }
}

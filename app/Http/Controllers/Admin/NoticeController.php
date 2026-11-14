<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class NoticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.notices.index', [
            'notices' => Notice::query()->latest()->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.notices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:4096'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('notices', 'public');
        }

        unset($validated['attachment']);

        Notice::query()->create($validated);

        return redirect()
            ->route('admin.notices.index')
            ->with('status', 'Notice created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notice $notice): View
    {
        return view('admin.notices.edit', compact('notice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notice $notice): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:4096'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('attachment')) {
            if ($notice->attachment_path) {
                Storage::disk('public')->delete($notice->attachment_path);
            }

            $validated['attachment_path'] = $request->file('attachment')->store('notices', 'public');
        }

        unset($validated['attachment']);

        $notice->update($validated);

        return redirect()
            ->route('admin.notices.index')
            ->with('status', 'Notice updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notice $notice): RedirectResponse
    {
        if ($notice->attachment_path) {
            Storage::disk('public')->delete($notice->attachment_path);
        }

        $notice->delete();

        return redirect()
            ->route('admin.notices.index')
            ->with('status', 'Notice deleted.');
    }
}

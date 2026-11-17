<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->string('q'));

        return view('admin.notices.index', [
            'query' => $query,
            'notices' => Notice::query()
                ->search($query)
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.notices.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['required', 'string', 'max:50'],
            'attachment' => ['nullable', 'file', 'max:4096'],
            'is_published' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'pinned_until' => ['nullable', 'date'],
        ]);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_featured'] = $request->boolean('is_featured');

        $attachment = $request->file('attachment');
        if ($attachment instanceof UploadedFile) {
            $validated['attachment_path'] = $attachment->store('notices', 'public');
        }

        unset($validated['attachment']);

        Notice::query()->create($validated);

        return redirect()
            ->route('admin.notices.index')
            ->with('status', 'Notice created.');
    }

    public function edit(Notice $notice): View
    {
        return view('admin.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['required', 'string', 'max:50'],
            'attachment' => ['nullable', 'file', 'max:4096'],
            'is_published' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'pinned_until' => ['nullable', 'date'],
        ]);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_featured'] = $request->boolean('is_featured');

        $attachment = $request->file('attachment');
        if ($attachment instanceof UploadedFile) {
            if ($notice->attachment_path) {
                Storage::disk('public')->delete($notice->attachment_path);
            }

            $validated['attachment_path'] = $attachment->store('notices', 'public');
        }

        unset($validated['attachment']);

        $notice->update($validated);

        return redirect()
            ->route('admin.notices.index')
            ->with('status', 'Notice updated.');
    }

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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->string('q'));

        return view('admin.news.index', [
            'query' => $query,
            'newsItems' => News::query()
                ->search($query)
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.news.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');

        $image = $request->file('image');
        if ($image instanceof UploadedFile) {
            $validated['image_path'] = $image->store('news', 'public');
        }

        unset($validated['image']);

        News::query()->create($validated);

        return redirect()
            ->route('admin.news.index')
            ->with('status', 'News item created.');
    }

    public function edit(News $news): View
    {
        return view('admin.news.edit', compact('news'));
    }

    public function update(Request $request, News $news): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');

        $image = $request->file('image');
        if ($image instanceof UploadedFile) {
            if ($news->image_path) {
                Storage::disk('public')->delete($news->image_path);
            }

            $validated['image_path'] = $image->store('news', 'public');
        }

        unset($validated['image']);

        $news->update($validated);

        return redirect()
            ->route('admin.news.index')
            ->with('status', 'News item updated.');
    }

    public function destroy(News $news): RedirectResponse
    {
        if ($news->image_path) {
            Storage::disk('public')->delete($news->image_path);
        }

        $news->delete();

        return redirect()
            ->route('admin.news.index')
            ->with('status', 'News item deleted.');
    }
}

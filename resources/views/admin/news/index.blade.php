@extends('layouts.admin')

@section('title', 'News')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4" style="gap: 1rem;">
        <div>
            <h1 class="h3 mb-1">News</h1>
            <p class="text-muted mb-0">Create, feature, and publish campus stories.</p>
        </div>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New article
        </a>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group" style="max-width: 420px;">
            <input type="search" name="q" value="{{ $query }}" class="form-control" placeholder="Search news...">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </div>
    </form>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Title</th>
                        <th>Published</th>
                        <th>Featured</th>
                        <th>Image</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($newsItems as $item)
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $item->title }}</div>
                                <small class="text-muted">{{ $item->slug }}</small>
                            </td>
                            <td>{{ optional($item->published_at)->toDateString() ?? '—' }}</td>
                            <td>
                                @if ($item->is_featured)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($item->image_path)
                                    <i class="bi bi-image text-success"></i>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('news.show', $item) }}" class="btn btn-sm btn-outline-info" target="_blank">View</a>
                                <a href="{{ route('admin.news.edit', $item) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('admin.news.destroy', $item) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this news item?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No news items yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $newsItems->links() }}</div>
@endsection

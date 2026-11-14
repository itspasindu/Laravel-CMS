@extends('layouts.admin')

@section('title', 'News')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">News</h1>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New article
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Title</th>
                        <th>Published</th>
                        <th>Image</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($newsItems as $item)
                        <tr>
                            <td>{{ $item->title }}</td>
                            <td>{{ optional($item->published_at)->toDateString() ?? '—' }}</td>
                            <td>
                                @if ($item->image_path)
                                    <i class="bi bi-image text-success"></i>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right">
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
                            <td colspan="4" class="text-center text-muted py-4">No news items yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $newsItems->links() }}</div>
@endsection

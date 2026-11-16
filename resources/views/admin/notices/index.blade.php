@extends('layouts.admin')

@section('title', 'Notices')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4" style="gap: 1rem;">
        <div>
            <h1 class="h3 mb-1">Notices</h1>
            <p class="text-muted mb-0">Pin urgent updates and organize by category.</p>
        </div>
        <a href="{{ route('admin.notices.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New notice
        </a>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group" style="max-width: 420px;">
            <input type="search" name="q" value="{{ $query }}" class="form-control" placeholder="Search notices...">
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
                        <th>Category</th>
                        <th>Published</th>
                        <th>Featured</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notices as $notice)
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $notice->title }}</div>
                                <small class="text-muted">{{ $notice->slug }}</small>
                            </td>
                            <td><span class="badge badge-info">{{ $notice->category }}</span></td>
                            <td>{{ $notice->is_published ? 'Yes' : 'No' }}</td>
                            <td>{{ $notice->is_featured ? 'Yes' : '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('notices.show', $notice) }}" class="btn btn-sm btn-outline-info" target="_blank">View</a>
                                <a href="{{ route('admin.notices.edit', $notice) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('admin.notices.destroy', $notice) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this notice?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No notices yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $notices->links() }}</div>
@endsection

@extends('layouts.admin')

@section('title', 'Notices')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Notices</h1>
        <a href="{{ route('admin.notices.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New notice
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Title</th>
                        <th>Published</th>
                        <th>Attachment</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notices as $notice)
                        <tr>
                            <td>{{ $notice->title }}</td>
                            <td>{{ $notice->is_published ? 'Yes' : 'No' }}</td>
                            <td>
                                @if ($notice->attachment_path)
                                    <i class="bi bi-paperclip text-success"></i>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right">
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
                            <td colspan="4" class="text-center text-muted py-4">No notices yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $notices->links() }}</div>
@endsection

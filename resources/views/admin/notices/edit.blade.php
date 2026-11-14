@extends('layouts.admin')

@section('title', 'Edit notice')

@section('content')
    <h1 class="h3 mb-4">Edit notice</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.notices.update', $notice) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.notices._form', ['notice' => $notice])
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.notices.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection

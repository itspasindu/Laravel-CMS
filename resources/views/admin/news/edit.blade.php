@extends('layouts.admin')

@section('title', 'Edit news')

@section('content')
    <h1 class="h3 mb-4">Edit news</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.news.update', $news) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.news._form', ['news' => $news])
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.news.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection

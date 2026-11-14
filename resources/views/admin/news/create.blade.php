@extends('layouts.admin')

@section('title', 'Create news')

@section('content')
    <h1 class="h3 mb-4">Create news</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('admin.news._form')
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="{{ route('admin.news.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection

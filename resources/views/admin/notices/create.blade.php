@extends('layouts.admin')

@section('title', 'Create notice')

@section('content')
    <h1 class="h3 mb-4">Create notice</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.notices.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('admin.notices._form')
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="{{ route('admin.notices.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection

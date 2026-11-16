@extends('layouts.public')

@section('title', 'News — '.config('app.name'))

@section('content')
    <div class="mb-8">
        <p class="cms-chip">Archive</p>
        <h1 class="cms-brand mt-3 text-4xl font-semibold">News</h1>
        <p class="mt-2 text-[var(--cms-muted)]">All published campus stories.</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($newsItems as $item)
            <a href="{{ route('news.show', $item) }}" class="cms-card block overflow-hidden">
                @if ($item->image_path)
                    <img src="{{ asset('storage/'.$item->image_path) }}" alt="" class="h-40 w-full object-cover">
                @else
                    <div class="h-40 bg-gradient-to-br from-slate-700 to-teal-800"></div>
                @endif
                <div class="p-5">
                    <h2 class="cms-display text-xl">{{ $item->title }}</h2>
                    <p class="mt-2 text-sm text-slate-500">{{ optional($item->published_at)->toFormattedDateString() }}</p>
                    <p class="mt-2 text-sm text-slate-700">{{ $item->excerpt }}</p>
                </div>
            </a>
        @empty
            <p class="text-slate-500">No news published yet.</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $newsItems->links() }}</div>
@endsection

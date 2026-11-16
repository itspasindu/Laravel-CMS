@extends('layouts.public')

@section('title', $news->title.' — '.config('app.name'))

@section('content')
    <article class="mx-auto max-w-3xl">
        <a href="{{ route('news.index') }}" class="text-sm font-semibold text-[var(--cms-accent)] hover:underline">← All news</a>
        <p class="cms-chip mt-5">{{ optional($news->published_at)->toFormattedDateString() }}</p>
        <h1 class="cms-brand mt-3 text-4xl font-semibold sm:text-5xl">{{ $news->title }}</h1>
        @if ($news->excerpt)
            <p class="mt-4 text-lg text-[var(--cms-muted)]">{{ $news->excerpt }}</p>
        @endif

        @if ($news->image_path)
            <img src="{{ asset('storage/'.$news->image_path) }}" alt="{{ $news->title }}" class="mt-8 h-72 w-full rounded-3xl object-cover shadow-sm">
        @endif

        <div class="prose prose-slate mt-8 max-w-none whitespace-pre-line text-base leading-8 text-slate-800">{{ $news->body }}</div>
    </article>

    @if ($related->isNotEmpty())
        <section class="mt-14">
            <h2 class="cms-display mb-4 text-2xl">More stories</h2>
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($related as $item)
                    <a href="{{ route('news.show', $item) }}" class="cms-card block p-4">
                        <h3 class="font-semibold">{{ $item->title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $item->excerpt }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection

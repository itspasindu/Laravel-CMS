@extends('layouts.public')

@section('title', config('app.name', 'Dummy CMS'))

@section('content')
    <section class="fade-up mb-10 overflow-hidden rounded-[28px] border border-slate-200/80 bg-white/80 p-8 shadow-sm backdrop-blur sm:p-10">
        <p class="cms-chip">Campus bulletin</p>
        <h1 class="cms-brand mt-4 text-4xl font-semibold text-[var(--cms-ink)] sm:text-5xl">
            {{ config('app.name', 'Dummy CMS') }}
        </h1>
        <p class="mt-3 max-w-2xl text-base leading-relaxed text-[var(--cms-muted)] sm:text-lg">
            Browse featured news and pinned notices. Search the catalogue, open full articles, and manage everything from the admin area after sign-in.
        </p>

        <form action="{{ route('home') }}" method="GET" class="mt-6 flex flex-col gap-3 sm:flex-row">
            <input
                type="search"
                name="q"
                value="{{ $query }}"
                placeholder="Search news and notices..."
                class="cms-input sm:flex-1"
            >
            <button type="submit" class="cms-btn-accent">Search</button>
            @if ($query !== '')
                <a href="{{ route('home') }}" class="cms-btn border border-slate-300 bg-white text-slate-700">Clear</a>
            @endif
        </form>
    </section>

    @if ($featuredNews->isNotEmpty() || $featuredNotices->isNotEmpty())
        <section class="fade-up-delay mb-12">
            <div class="mb-4 flex items-end justify-between gap-3">
                <h2 class="cms-display text-3xl text-[var(--cms-ink)]">Featured</h2>
                <p class="text-sm text-slate-500">Highlighted by editors</p>
            </div>
            <div class="grid gap-5 lg:grid-cols-3">
                @foreach ($featuredNews as $item)
                    <a href="{{ route('news.show', $item) }}" class="cms-card block overflow-hidden">
                        @if ($item->image_path)
                            <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->title }}" class="h-44 w-full object-cover">
                        @else
                            <div class="flex h-44 items-end bg-gradient-to-br from-teal-700 to-slate-800 p-5 text-white">
                                <span class="cms-display text-2xl">News</span>
                            </div>
                        @endif
                        <div class="p-5">
                            <span class="cms-chip">Featured news</span>
                            <h3 class="cms-display mt-3 text-xl">{{ $item->title }}</h3>
                            <p class="mt-2 text-sm text-slate-600">{{ $item->excerpt }}</p>
                        </div>
                    </a>
                @endforeach
                @foreach ($featuredNotices as $notice)
                    <a href="{{ route('notices.show', $notice) }}" class="cms-card block p-5">
                        <span class="cms-chip">{{ $notice->category }}</span>
                        <h3 class="cms-display mt-3 text-xl">{{ $notice->title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($notice->body, 140) }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div class="grid gap-10 lg:grid-cols-2">
        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="cms-display text-3xl">News</h2>
                <a href="{{ route('news.index') }}" class="text-sm font-semibold text-[var(--cms-accent)] hover:underline">View all</a>
            </div>
            <div class="space-y-4">
                @forelse ($newsItems as $item)
                    <article class="cms-card p-5">
                        <div class="flex gap-4">
                            @if ($item->image_path)
                                <img src="{{ asset('storage/'.$item->image_path) }}" alt="" class="hidden h-24 w-28 rounded-xl object-cover sm:block">
                            @endif
                            <div class="min-w-0 flex-1">
                                @if ($item->is_featured)
                                    <span class="cms-chip mb-2">Featured</span>
                                @endif
                                <h3 class="cms-display text-xl">
                                    <a href="{{ route('news.show', $item) }}" class="hover:text-[var(--cms-accent)]">{{ $item->title }}</a>
                                </h3>
                                <p class="mt-1 text-sm text-slate-500">{{ optional($item->published_at)->toFormattedDateString() }}</p>
                                <p class="mt-2 text-slate-700">{{ $item->excerpt ?: \Illuminate\Support\Str::limit($item->body, 160) }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-6 text-slate-500">No news items match your search.</p>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="cms-display text-3xl">Notices</h2>
                <a href="{{ route('notices.index') }}" class="text-sm font-semibold text-[var(--cms-accent)] hover:underline">View all</a>
            </div>
            <div class="space-y-4">
                @forelse ($notices as $notice)
                    <article class="cms-card p-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="cms-chip">{{ $notice->category }}</span>
                            @if ($notice->isPinned())
                                <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-amber-800">Pinned</span>
                            @endif
                        </div>
                        <h3 class="cms-display mt-3 text-xl">
                            <a href="{{ route('notices.show', $notice) }}" class="hover:text-[var(--cms-accent)]">{{ $notice->title }}</a>
                        </h3>
                        <p class="mt-2 text-slate-700">{{ \Illuminate\Support\Str::limit($notice->body, 160) }}</p>
                    </article>
                @empty
                    <p class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-6 text-slate-500">No notices match your search.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection

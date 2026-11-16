@extends('layouts.public')

@section('title', $notice->title.' — '.config('app.name'))

@section('content')
    <article class="mx-auto max-w-3xl">
        <a href="{{ route('notices.index') }}" class="text-sm font-semibold text-[var(--cms-accent)] hover:underline">← All notices</a>
        <div class="mt-5 flex flex-wrap gap-2">
            <span class="cms-chip">{{ $notice->category }}</span>
            @if ($notice->isPinned())
                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold uppercase text-amber-800">Pinned until {{ $notice->pinned_until->toFormattedDateString() }}</span>
            @endif
        </div>
        <h1 class="cms-brand mt-3 text-4xl font-semibold sm:text-5xl">{{ $notice->title }}</h1>
        <div class="mt-8 whitespace-pre-line text-base leading-8 text-slate-800">{{ $notice->body }}</div>

        @if ($notice->attachment_path)
            <a href="{{ asset('storage/'.$notice->attachment_path) }}" class="cms-btn-accent mt-8 inline-flex">
                Download attachment
            </a>
        @endif
    </article>
@endsection

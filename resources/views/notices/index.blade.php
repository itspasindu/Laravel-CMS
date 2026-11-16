@extends('layouts.public')

@section('title', 'Notices — '.config('app.name'))

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="cms-chip">Board</p>
            <h1 class="cms-brand mt-3 text-4xl font-semibold">Notices</h1>
        </div>
        <form method="GET" class="flex gap-2">
            <select name="category" class="cms-input w-auto">
                <option value="">All categories</option>
                @foreach ($categories as $item)
                    <option value="{{ $item }}" @selected($category === $item)>{{ ucfirst($item) }}</option>
                @endforeach
            </select>
            <button class="cms-btn-accent" type="submit">Filter</button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse ($notices as $notice)
            <a href="{{ route('notices.show', $notice) }}" class="cms-card block p-5">
                <div class="flex flex-wrap gap-2">
                    <span class="cms-chip">{{ $notice->category }}</span>
                    @if ($notice->isPinned())
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold uppercase text-amber-800">Pinned</span>
                    @endif
                </div>
                <h2 class="cms-display mt-3 text-2xl">{{ $notice->title }}</h2>
                <p class="mt-2 text-slate-700">{{ \Illuminate\Support\Str::limit($notice->body, 200) }}</p>
            </a>
        @empty
            <p class="text-slate-500">No notices in this category.</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $notices->links() }}</div>
@endsection

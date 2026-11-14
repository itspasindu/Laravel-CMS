@extends('layouts.public')

@section('title', config('app.name', 'Dummy CMS'))

@section('content')
    <div class="mb-10">
        <h1 class="text-3xl font-semibold tracking-tight">{{ config('app.name', 'Dummy CMS') }}</h1>
        <p class="mt-2 max-w-2xl text-slate-600">
            Public listing of dummy News and Notice items. Sign in to manage content from the admin area.
        </p>
    </div>

    <div class="grid gap-10 lg:grid-cols-2">
        <section>
            <h2 class="mb-4 text-xl font-semibold">News</h2>
            <div class="space-y-4">
                @forelse ($newsItems as $item)
                    <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        @if ($item->image_path)
                            <img
                                src="{{ asset('storage/'.$item->image_path) }}"
                                alt="{{ $item->title }}"
                                class="mb-3 h-40 w-full rounded object-cover"
                            >
                        @endif
                        <h3 class="text-lg font-medium">{{ $item->title }}</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ optional($item->published_at)->toFormattedDateString() }}
                        </p>
                        <p class="mt-3 text-slate-700">{{ \Illuminate\Support\Str::limit($item->body, 180) }}</p>
                    </article>
                @empty
                    <p class="text-slate-500">No news items yet.</p>
                @endforelse
            </div>
        </section>

        <section>
            <h2 class="mb-4 text-xl font-semibold">Notices</h2>
            <div class="space-y-4">
                @forelse ($notices as $notice)
                    <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-lg font-medium">{{ $notice->title }}</h3>
                        <p class="mt-3 text-slate-700">{{ \Illuminate\Support\Str::limit($notice->body, 180) }}</p>
                        @if ($notice->attachment_path)
                            <a
                                href="{{ asset('storage/'.$notice->attachment_path) }}"
                                class="mt-3 inline-block text-sm font-medium text-sky-700 hover:underline"
                            >
                                Download attachment
                            </a>
                        @endif
                    </article>
                @empty
                    <p class="text-slate-500">No notices yet.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection

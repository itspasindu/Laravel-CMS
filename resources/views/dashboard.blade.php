<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-2xl bg-white p-5 shadow-sm dark:bg-gray-800">
                    <p class="text-sm text-gray-500">News</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['news'] }}</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm dark:bg-gray-800">
                    <p class="text-sm text-gray-500">Published news</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['published_news'] }}</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm dark:bg-gray-800">
                    <p class="text-sm text-gray-500">Notices</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['notices'] }}</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm dark:bg-gray-800">
                    <p class="text-sm text-gray-500">Published notices</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['published_notices'] }}</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm dark:bg-gray-800">
                    <p class="text-sm text-gray-500">Users</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['users'] }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.news.create') }}" class="rounded-xl bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-600">New news</a>
                <a href="{{ route('admin.notices.create') }}" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">New notice</a>
                <a href="{{ route('home') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Public site</a>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Latest news</h3>
                    <ul class="mt-4 space-y-3">
                        @forelse ($latestNews as $item)
                            <li class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-gray-800 dark:text-gray-200">{{ $item->title }}</span>
                                <a href="{{ route('admin.news.edit', $item) }}" class="text-teal-700 hover:underline">Edit</a>
                            </li>
                        @empty
                            <li class="text-gray-500">No news yet.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Latest notices</h3>
                    <ul class="mt-4 space-y-3">
                        @forelse ($latestNotices as $notice)
                            <li class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-gray-800 dark:text-gray-200">{{ $notice->title }}</span>
                                <a href="{{ route('admin.notices.edit', $notice) }}" class="text-teal-700 hover:underline">Edit</a>
                            </li>
                        @empty
                            <li class="text-gray-500">No notices yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

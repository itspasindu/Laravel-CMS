<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the public home page with dummy CMS items.
     */
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->string('q'));

        $newsQuery = News::query()->published()->search($query);
        $noticeQuery = Notice::query()->published()->search($query);

        return view('home', [
            'query' => $query,
            'featuredNews' => (clone $newsQuery)->featured()->latest('published_at')->take(3)->get(),
            'newsItems' => $newsQuery->latest('published_at')->take(8)->get(),
            'featuredNotices' => (clone $noticeQuery)->featured()->latest()->take(3)->get(),
            'notices' => $noticeQuery
                ->orderByRaw('case when pinned_until is not null and pinned_until > ? then 0 else 1 end', [now()])
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }
}

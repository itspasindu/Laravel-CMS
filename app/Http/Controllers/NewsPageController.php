<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\View\View;

class NewsPageController extends Controller
{
    public function index(): View
    {
        return view('news.index', [
            'newsItems' => News::query()->published()->latest('published_at')->paginate(9),
        ]);
    }

    public function show(News $news): View
    {
        abort_unless($news->published_at !== null && $news->published_at->lte(now()), 404);

        return view('news.show', [
            'news' => $news,
            'related' => News::query()
                ->published()
                ->whereKeyNot($news->id)
                ->latest('published_at')
                ->take(3)
                ->get(),
        ]);
    }
}

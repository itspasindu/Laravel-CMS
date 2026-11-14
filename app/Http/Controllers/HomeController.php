<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Notice;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the public home page with dummy CMS items.
     */
    public function __invoke(): View
    {
        return view('home', [
            'newsItems' => News::query()
                ->whereNotNull('published_at')
                ->latest('published_at')
                ->take(10)
                ->get(),
            'notices' => Notice::query()
                ->where('is_published', true)
                ->latest()
                ->take(10)
                ->get(),
        ]);
    }
}

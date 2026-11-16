<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Notice;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'stats' => [
                'news' => News::query()->count(),
                'published_news' => News::query()->published()->count(),
                'notices' => Notice::query()->count(),
                'published_notices' => Notice::query()->published()->count(),
                'users' => User::query()->count(),
            ],
            'latestNews' => News::query()->latest()->take(5)->get(),
            'latestNotices' => Notice::query()->latest()->take(5)->get(),
        ]);
    }
}

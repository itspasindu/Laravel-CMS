<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoticePageController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->string('category')->toString();

        $notices = Notice::query()
            ->published()
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->orderByRaw('case when pinned_until is not null and pinned_until > ? then 0 else 1 end', [now()])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('notices.index', [
            'notices' => $notices,
            'category' => $category,
            'categories' => Notice::query()->published()->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function show(Notice $notice): View
    {
        abort_unless($notice->is_published, 404);

        return view('notices.show', compact('notice'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\Notice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_detail_page_is_available(): void
    {
        $news = News::factory()->create([
            'title' => 'Detail Story',
            'slug' => 'detail-story',
        ]);

        $response = $this->get(route('news.show', $news));

        $response->assertOk();
        $response->assertSee('Detail Story');
    }

    public function test_notice_detail_page_is_available(): void
    {
        $notice = Notice::factory()->create([
            'title' => 'Pinned Board Note',
            'slug' => 'pinned-board-note',
            'category' => 'urgent',
        ]);

        $response = $this->get(route('notices.show', $notice));

        $response->assertOk();
        $response->assertSee('Pinned Board Note');
        $response->assertSee('urgent');
    }

    public function test_unpublished_news_is_hidden(): void
    {
        $news = News::factory()->create([
            'slug' => 'draft-story',
            'published_at' => null,
        ]);

        $this->get(route('news.show', $news))->assertNotFound();
    }
}

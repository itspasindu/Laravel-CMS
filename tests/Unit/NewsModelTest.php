<?php

namespace Tests\Unit;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_model_casts_published_at_to_datetime(): void
    {
        $news = News::factory()->create([
            'published_at' => '2026-01-15 10:00:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $news->published_at);
        $this->assertSame('2026-01-15', $news->published_at->toDateString());
    }
}

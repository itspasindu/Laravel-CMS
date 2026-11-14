<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_news_with_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.news.store'), [
            'title' => 'Launch Announcement',
            'body' => 'Dummy CMS news body for tests.',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'image' => UploadedFile::fake()->image('story.jpg'),
        ]);

        $response->assertRedirect(route('admin.news.index'));

        $this->assertDatabaseHas('news', [
            'title' => 'Launch Announcement',
        ]);

        $news = News::query()->first();

        $this->assertNotNull($news);
        $this->assertNotNull($news->image_path);
        Storage::disk('public')->assertExists($news->image_path);
    }
}

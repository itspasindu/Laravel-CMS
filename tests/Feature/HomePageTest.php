<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\Notice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_and_lists_dummy_items(): void
    {
        News::factory()->create(['title' => 'Campus News Item']);
        Notice::factory()->create(['title' => 'Campus Notice Item']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Campus News Item');
        $response->assertSee('Campus Notice Item');
        $response->assertSee('Search news and notices');
    }

    public function test_home_page_can_search_content(): void
    {
        News::factory()->create(['title' => 'Robotics showcase']);
        News::factory()->create(['title' => 'Unrelated story']);

        $response = $this->get(route('home', ['q' => 'Robotics']));

        $response->assertOk();
        $response->assertSee('Robotics showcase');
        $response->assertDontSee('Unrelated story');
    }
}

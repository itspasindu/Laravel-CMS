<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        News::factory()->featured()->create([
            'title' => 'Orientation week opens with campus tours',
            'excerpt' => 'New students explore libraries, labs, and student services across the main quad.',
            'body' => "Orientation week is underway with guided tours, faculty meet-and-greets, and resource fairs.\n\nVisit the student services desk for timetable help and ID card collection.",
            'published_at' => now()->subDay(),
        ]);

        News::factory()->count(4)->create();
    }
}

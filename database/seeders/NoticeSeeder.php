<?php

namespace Database\Seeders;

use App\Models\Notice;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Notice::factory()->featured()->create([
            'title' => 'Library late hours this Friday',
            'category' => 'academic',
            'body' => 'The central library will remain open until 11pm this Friday for exam preparation.',
            'is_published' => true,
        ]);

        Notice::factory()->create([
            'title' => 'Sports day registration deadline',
            'category' => 'events',
            'body' => 'Register your team before Wednesday noon at the athletics office.',
            'is_published' => true,
            'is_featured' => false,
        ]);

        Notice::factory()->count(3)->create();
    }
}

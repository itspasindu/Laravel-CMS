<?php

namespace Database\Factories;

use App\Models\Notice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Notice>
 */
class NoticeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'body' => fake()->paragraph(),
            'category' => fake()->randomElement(['general', 'academic', 'events', 'urgent']),
            'attachment_path' => null,
            'is_published' => true,
            'is_featured' => false,
            'pinned_until' => null,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'pinned_until' => now()->addDays(7),
        ]);
    }
}

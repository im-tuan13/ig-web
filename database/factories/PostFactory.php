<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'image' => 'https://picsum.photos/seed/'.fake()->unique()->numberBetween(1, 100000).'/1080/1080',
            'caption' => fake()->sentence(rand(4, 12)),
        ];
    }
}

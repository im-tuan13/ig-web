<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileLikesTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_can_count_likes_for_posts_without_error(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'image' => 'posts/example.jpg',
            'caption' => 'Hello',
        ]);

        $this->assertSame(0, $post->likedByUsers()->count());
    }
}

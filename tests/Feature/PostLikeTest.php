<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_like_a_post_idempotently(): void
    {
        $user = User::factory()->create();
        $post = Post::create(['user_id' => User::factory()->create()->id, 'image' => 'posts/photo.jpg']);

        $this->actingAs($user)->postJson(route('posts.likes.store', $post))
            ->assertOk()
            ->assertJson(['liked' => true, 'likes_count' => 1]);

        $this->actingAs($user)->postJson(route('posts.likes.store', $post))
            ->assertOk()
            ->assertJson(['liked' => true, 'likes_count' => 1]);

        $this->assertDatabaseCount('post_likes', 1);
    }

    public function test_user_can_unlike_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::create(['user_id' => User::factory()->create()->id, 'image' => 'posts/photo.jpg']);
        $post->likedByUsers()->attach($user);

        $this->actingAs($user)->deleteJson(route('posts.likes.destroy', $post))
            ->assertOk()
            ->assertJson(['liked' => false, 'likes_count' => 0]);

        $this->assertDatabaseCount('post_likes', 0);
    }

    public function test_direct_post_page_uses_the_interactive_like_component(): void
    {
        $user = User::factory()->create();
        $post = Post::create(['user_id' => User::factory()->create()->id, 'image' => 'posts/photo.jpg']);

        $this->actingAs($user)
            ->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('postLike('.$post->id.', 0, false)', false);
    }
}

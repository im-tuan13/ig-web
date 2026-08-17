<?php

namespace Tests\Feature;

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_searching_posts_returns_matching_caption(): void
    {
        $viewer = User::factory()->create();
        $post = Post::create(['user_id' => $viewer->id, 'image' => 'posts/a.jpg', 'caption' => 'Sunset in Bali']);

        $this->actingAs($viewer)
            ->getJson('/search/posts?q=Bali')
            ->assertOk()
            ->assertJsonFragment(['id' => $post->id]);
    }

    public function test_searching_posts_excludes_private_accounts_the_viewer_does_not_follow(): void
    {
        $viewer = User::factory()->create();
        $privateOwner = User::factory()->create(['is_private' => true]);
        Post::create(['user_id' => $privateOwner->id, 'image' => 'posts/hidden.jpg', 'caption' => 'Secret Bali trip']);

        $response = $this->actingAs($viewer)->getJson('/search/posts?q=Bali');

        $response->assertOk();
        $this->assertCount(0, $response->json());
    }

    public function test_searching_hashtags_returns_matching_tag_with_post_count(): void
    {
        $viewer = User::factory()->create();
        $tag = Hashtag::create(['name' => 'sunset']);
        $post = Post::create(['user_id' => $viewer->id, 'image' => 'posts/a.jpg', 'caption' => '#sunset']);
        $post->hashtags()->attach($tag);

        $this->actingAs($viewer)
            ->getJson('/search/hashtags?q=sun')
            ->assertOk()
            ->assertJsonFragment(['name' => 'sunset', 'posts_count' => 1]);
    }

    public function test_hashtag_page_excludes_private_accounts_posts_for_non_follower(): void
    {
        $viewer = User::factory()->create();
        $privateOwner = User::factory()->create(['is_private' => true]);
        $tag = Hashtag::create(['name' => 'sunset']);
        $post = Post::create(['user_id' => $privateOwner->id, 'image' => 'posts/hidden.jpg', 'caption' => '#sunset']);
        $post->hashtags()->attach($tag);

        $this->actingAs($viewer)
            ->get(route('hashtags.show', 'sunset'))
            ->assertOk()
            ->assertDontSee('posts/hidden.jpg', false);
    }
}

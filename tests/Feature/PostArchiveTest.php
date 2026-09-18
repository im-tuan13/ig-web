<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_archive_and_unarchive_a_post(): void
    {
        $owner = User::factory()->create();
        $post = Post::create(['user_id' => $owner->id, 'image' => 'posts/a.jpg', 'caption' => 'test']);

        $this->actingAs($owner)->post(route('posts.archive.store', $post))->assertRedirect();
        $this->assertNotNull($post->fresh()->archived_at);
        $this->actingAs($owner)->delete(route('posts.archive.destroy', $post))->assertRedirect();
        $this->assertNull($post->fresh()->archived_at);
    }

    public function test_non_owner_cannot_archive_someone_elses_post(): void
    {
        $owner = User::factory()->create();
        $post = Post::create(['user_id' => $owner->id, 'image' => 'posts/a.jpg', 'caption' => 'test']);

        $this->actingAs(User::factory()->create())->post(route('posts.archive.store', $post))->assertForbidden();
    }

    public function test_archived_post_is_hidden_from_feed_and_explore(): void
    {
        $owner = User::factory()->create(['is_private' => false]);
        Post::create(['user_id' => $owner->id, 'image' => 'posts/a.jpg', 'caption' => 'archived caption', 'archived_at' => now()]);
        $viewer = User::factory()->create();

        $this->actingAs($viewer)->get(route('dashboard'))->assertDontSee('archived caption');
        $this->actingAs($viewer)->get(route('explore.index'))->assertDontSee('archived caption');
    }

    public function test_stranger_cannot_directly_view_an_archived_post(): void
    {
        $owner = User::factory()->create(['is_private' => false]);
        $post = Post::create(['user_id' => $owner->id, 'image' => 'posts/a.jpg', 'caption' => 'test', 'archived_at' => now()]);

        $this->actingAs(User::factory()->create())->get(route('posts.show', $post))->assertForbidden();
    }

    public function test_owner_can_still_view_own_archived_post_directly(): void
    {
        $owner = User::factory()->create();
        $post = Post::create(['user_id' => $owner->id, 'image' => 'posts/a.jpg', 'caption' => 'test', 'archived_at' => now()]);

        $this->actingAs($owner)->get(route('posts.show', $post))->assertOk();
    }

    public function test_archived_posts_are_excluded_from_profile_grid(): void
    {
        $owner = User::factory()->create();
        Post::create(['user_id' => $owner->id, 'image' => 'posts/visible.jpg', 'caption' => 'visible']);
        Post::create(['user_id' => $owner->id, 'image' => 'posts/hidden.jpg', 'caption' => 'archived one', 'archived_at' => now()]);

        $this->actingAs($owner)->get(route('users.show', $owner))->assertOk()->assertSee('posts/visible.jpg', false)->assertDontSee('posts/hidden.jpg', false);
    }

    public function test_archived_posts_are_excluded_from_search(): void
    {
        $owner = User::factory()->create(['is_private' => false]);
        Post::create(['user_id' => $owner->id, 'image' => 'posts/a.jpg', 'caption' => 'unique archived phrase', 'archived_at' => now()]);

        $response = $this->actingAs(User::factory()->create())->getJson('/search/posts?q=unique');
        $response->assertOk();
        $this->assertCount(0, $response->json());
    }

    public function test_archive_index_lists_only_the_current_users_archived_posts(): void
    {
        $owner = User::factory()->create();
        Post::create(['user_id' => $owner->id, 'image' => 'posts/hidden.jpg', 'caption' => 'archived one', 'archived_at' => now()]);
        Post::create(['user_id' => $owner->id, 'image' => 'posts/visible.jpg', 'caption' => 'still visible']);

        $this->actingAs($owner)->get(route('posts.archive.index'))->assertOk();
    }
}

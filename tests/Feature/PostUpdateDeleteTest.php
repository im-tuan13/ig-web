<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostUpdateDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_edit_post_caption(): void
    {
        $owner = User::factory()->create();
        $post = Post::create(['user_id' => $owner->id, 'image' => 'posts/photo.jpg', 'caption' => 'Old #caption']);

        $this->actingAs($owner)
            ->put(route('posts.update', $post), ['caption' => 'New caption #updated'])
            ->assertRedirect(route('posts.show', $post));

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'caption' => 'New caption #updated']);
        $this->assertDatabaseHas('hashtags', ['name' => 'updated']);
    }

    public function test_non_owner_cannot_edit_post(): void
    {
        $post = Post::create(['user_id' => User::factory()->create()->id, 'image' => 'posts/photo.jpg']);

        $this->actingAs(User::factory()->create())
            ->put(route('posts.update', $post), ['caption' => 'Changed'])
            ->assertForbidden();
    }

    public function test_owner_can_delete_post_and_related_records(): void
    {
        $owner = User::factory()->create();
        $commenter = User::factory()->create();
        $liker = User::factory()->create();
        $post = Post::create(['user_id' => $owner->id, 'image' => 'posts/photo.jpg']);
        Comment::create(['post_id' => $post->id, 'user_id' => $commenter->id, 'comment' => 'Comment']);
        $post->likedByUsers()->attach($liker->id);

        $this->actingAs($owner)
            ->delete(route('posts.destroy', $post))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('comments', ['post_id' => $post->id]);
        $this->assertDatabaseMissing('post_likes', ['post_id' => $post->id]);
    }

    public function test_non_owner_cannot_delete_post(): void
    {
        $post = Post::create(['user_id' => User::factory()->create()->id, 'image' => 'posts/photo.jpg']);

        $this->actingAs(User::factory()->create())
            ->delete(route('posts.destroy', $post))
            ->assertForbidden();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_a_comment_to_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::create(['user_id' => User::factory()->create()->id, 'image' => 'posts/photo.jpg']);

        $this->actingAs($user)->postJson(route('posts.comments.store', $post), [
            'comment' => 'Nice photo!',
        ])
            ->assertCreated()
            ->assertJsonPath('comment.body', 'Nice photo!')
            ->assertJsonPath('comment.user.username', $user->username)
            ->assertJson(['comments_count' => 1]);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'comment' => 'Nice photo!',
        ]);
    }

    public function test_comment_is_required(): void
    {
        $user = User::factory()->create();
        $post = Post::create(['user_id' => User::factory()->create()->id, 'image' => 'posts/photo.jpg']);

        $this->actingAs($user)->postJson(route('posts.comments.store', $post), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('comment');
    }

    public function test_comment_owner_can_delete_their_comment(): void
    {
        $owner = User::factory()->create();
        $post = Post::create(['user_id' => $owner->id, 'image' => 'posts/photo.jpg']);
        $comment = $post->comments()->create(['user_id' => $owner->id, 'comment' => 'Remove me']);

        $this->actingAs($owner)
            ->deleteJson(route('posts.comments.destroy', [$post, $comment]))
            ->assertOk()
            ->assertJson(['deleted' => true, 'comments_count' => 0]);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_post_owner_can_delete_someone_elses_comment(): void
    {
        $owner = User::factory()->create();
        $commenter = User::factory()->create();
        $post = Post::create(['user_id' => $owner->id, 'image' => 'posts/photo.jpg']);
        $comment = $post->comments()->create(['user_id' => $commenter->id, 'comment' => 'Remove me']);

        $this->actingAs($owner)
            ->deleteJson(route('posts.comments.destroy', [$post, $comment]))
            ->assertOk();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_other_user_cannot_delete_someone_elses_comment(): void
    {
        $owner = User::factory()->create();
        $commenter = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::create(['user_id' => $owner->id, 'image' => 'posts/photo.jpg']);
        $comment = $post->comments()->create(['user_id' => $commenter->id, 'comment' => 'Keep me']);

        $this->actingAs($other)
            ->deleteJson(route('posts.comments.destroy', [$post, $comment]))
            ->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }
}

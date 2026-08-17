<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_mentioning_user_in_post_caption_creates_notification(): void
    {
        Storage::fake('public');
        $author = User::factory()->create();
        $mentioned = User::factory()->create(['username' => 'budi']);
        $file = UploadedFile::fake()->create('post.jpg', 1024, 'image/jpeg');

        $this->actingAs($author)->post(route('posts.store'), ['caption' => 'Hai @budi, lihat ini!', 'image' => $file]);

        $this->assertDatabaseHas('notifications', ['user_id' => $mentioned->id, 'actor_id' => $author->id, 'type' => 'mention']);
    }

    public function test_mentioning_self_does_not_create_notification(): void
    {
        Storage::fake('public');
        $author = User::factory()->create(['username' => 'budi']);
        $file = UploadedFile::fake()->create('post.jpg', 1024, 'image/jpeg');

        $this->actingAs($author)->post(route('posts.store'), ['caption' => 'Selfie time @budi', 'image' => $file]);

        $this->assertDatabaseMissing('notifications', ['user_id' => $author->id, 'type' => 'mention']);
    }

    public function test_mentioning_user_in_comment_creates_notification(): void
    {
        $postOwner = User::factory()->create();
        $commenter = User::factory()->create();
        $mentioned = User::factory()->create(['username' => 'siti']);
        $post = Post::create(['user_id' => $postOwner->id, 'image' => 'posts/a.jpg', 'caption' => 'test']);

        $this->actingAs($commenter)->postJson(route('posts.comments.store', $post), ['comment' => 'Cek ini @siti']);

        $this->assertDatabaseHas('notifications', ['user_id' => $mentioned->id, 'actor_id' => $commenter->id, 'type' => 'mention']);
    }

    public function test_caption_renders_mention_as_clickable_link(): void
    {
        $author = User::factory()->create();
        $mentioned = User::factory()->create(['username' => 'budi']);
        $post = Post::create(['user_id' => $author->id, 'image' => 'posts/a.jpg', 'caption' => 'Hai @budi']);

        $this->actingAs($author)->get(route('posts.show', $post))->assertSee(route('users.show', $mentioned), false);
    }
}

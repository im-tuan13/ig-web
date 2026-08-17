<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_a_reel(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $video = UploadedFile::fake()->create('clip.mp4', 2048, 'video/mp4');

        $this->actingAs($user)
            ->post(route('reels.store'), ['video' => $video, 'caption' => 'My first reel'])
            ->assertRedirect(route('reels.index'));

        $this->assertDatabaseHas('posts', ['user_id' => $user->id, 'caption' => 'My first reel', 'is_video' => true]);
    }

    public function test_reel_upload_rejects_non_video_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $image = UploadedFile::fake()->create('photo.jpg', 500, 'image/jpeg');

        $this->actingAs($user)->post(route('reels.store'), ['video' => $image])->assertSessionHasErrors('video');
        $this->assertDatabaseMissing('posts', ['user_id' => $user->id]);
    }

    public function test_reels_index_only_shows_video_posts(): void
    {
        $owner = User::factory()->create(['is_private' => false]);
        Post::create(['user_id' => $owner->id, 'image' => 'posts/photo.jpg', 'caption' => 'just a photo', 'is_video' => false]);
        Post::create(['user_id' => $owner->id, 'image' => 'reels/clip.mp4', 'caption' => 'a real reel', 'is_video' => true]);

        $response = $this->actingAs(User::factory()->create())->get(route('reels.index'));
        $response->assertOk()->assertSee('a real reel')->assertDontSee('just a photo');
    }

    public function test_reels_index_excludes_private_accounts_not_followed(): void
    {
        $privateOwner = User::factory()->create(['is_private' => true]);
        Post::create(['user_id' => $privateOwner->id, 'image' => 'reels/secret.mp4', 'caption' => 'secret reel', 'is_video' => true]);

        $this->actingAs(User::factory()->create())->get(route('reels.index'))->assertDontSee('secret reel');
    }

    public function test_reels_index_excludes_archived_reels(): void
    {
        $owner = User::factory()->create(['is_private' => false]);
        Post::create(['user_id' => $owner->id, 'image' => 'reels/gone.mp4', 'caption' => 'archived reel', 'is_video' => true, 'archived_at' => now()]);

        $this->actingAs(User::factory()->create())->get(route('reels.index'))->assertDontSee('archived reel');
    }

    public function test_mentioning_user_in_reel_caption_creates_notification(): void
    {
        Storage::fake('public');
        $author = User::factory()->create();
        $mentioned = User::factory()->create(['username' => 'budi']);
        $video = UploadedFile::fake()->create('clip.mp4', 2048, 'video/mp4');

        $this->actingAs($author)->post(route('reels.store'), ['video' => $video, 'caption' => 'Cek ini @budi']);

        $this->assertDatabaseHas('notifications', ['user_id' => $mentioned->id, 'actor_id' => $author->id, 'type' => 'mention']);
    }
}

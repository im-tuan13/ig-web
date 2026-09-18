<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_like_creates_notification_for_post_owner(): void
    {
        $owner = User::factory()->create();
        $liker = User::factory()->create();
        $post = Post::create(['user_id' => $owner->id, 'image' => 'posts/photo.jpg']);

        $this->actingAs($liker)->postJson(route('posts.likes.store', $post));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'actor_id' => $liker->id,
            'type' => 'like',
            'post_id' => $post->id,
        ]);
    }

    public function test_like_does_not_create_notification_for_self(): void
    {
        $user = User::factory()->create();
        $post = Post::create(['user_id' => $user->id, 'image' => 'posts/photo.jpg']);

        $this->actingAs($user)->postJson(route('posts.likes.store', $post));

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_comment_creates_notification_for_post_owner(): void
    {
        $owner = User::factory()->create();
        $commenter = User::factory()->create();
        $post = Post::create(['user_id' => $owner->id, 'image' => 'posts/photo.jpg']);

        $this->actingAs($commenter)->postJson(route('posts.comments.store', $post), [
            'comment' => 'Nice post!',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'actor_id' => $commenter->id,
            'type' => 'comment',
            'post_id' => $post->id,
        ]);
    }

    public function test_comment_does_not_create_notification_for_self(): void
    {
        $user = User::factory()->create();
        $post = Post::create(['user_id' => $user->id, 'image' => 'posts/photo.jpg']);

        $this->actingAs($user)->postJson(route('posts.comments.store', $post), [
            'comment' => 'My own post!',
        ]);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_follow_creates_notification(): void
    {
        $target = User::factory()->create();
        $follower = User::factory()->create();

        $this->actingAs($follower)->post(route('users.follow', $target));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $target->id,
            'actor_id' => $follower->id,
            'type' => 'follow',
            'post_id' => null,
        ]);
    }

    public function test_unread_count_returns_correct_number(): void
    {
        $user = User::factory()->create();
        $actor = User::factory()->create();

        Notification::create(['user_id' => $user->id, 'actor_id' => $actor->id, 'type' => 'follow']);
        Notification::create(['user_id' => $user->id, 'actor_id' => $actor->id, 'type' => 'like', 'post_id' => Post::create(['user_id' => $user->id, 'image' => 'posts/p.jpg'])->id]);

        $response = $this->actingAs($user)->getJson(route('notifications.count'));

        $response->assertOk()->assertJson(['count' => 2]);
    }

    public function test_mark_all_as_read(): void
    {
        $user = User::factory()->create();
        $actor = User::factory()->create();

        Notification::create(['user_id' => $user->id, 'actor_id' => $actor->id, 'type' => 'follow']);
        Notification::create(['user_id' => $user->id, 'actor_id' => $actor->id, 'type' => 'follow']);

        $this->actingAs($user)->post(route('notifications.read'));

        $this->assertEquals(0, $user->notifications()->unread()->count());
    }

    public function test_notifications_index_page_loads(): void
    {
        $user = User::factory()->create();
        $actor = User::factory()->create();
        $post = Post::create(['user_id' => $user->id, 'image' => 'posts/photo.jpg']);

        Notification::create(['user_id' => $user->id, 'actor_id' => $actor->id, 'type' => 'like', 'post_id' => $post->id]);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk()
            ->assertSee('Notifications')
            ->assertSee($actor->username);
    }

    public function test_notifications_index_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk()
            ->assertSee('No notifications yet');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_follow_and_unfollow_another_user(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($actor)->post(route('users.follow', $target))->assertRedirect();
        $this->assertTrue($actor->following()->whereKey($target)->exists());

        $this->actingAs($actor)->delete(route('users.unfollow', $target))->assertRedirect();
        $this->assertFalse($actor->following()->whereKey($target)->exists());
    }

    public function test_user_cannot_follow_themselves(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('users.follow', $user))->assertForbidden();
    }

    public function test_feed_contains_own_and_followed_posts_only(): void
    {
        $actor = User::factory()->create();
        $followed = User::factory()->create();
        $other = User::factory()->create();
        $actor->following()->attach($followed);

        $ownPost = Post::create(['user_id' => $actor->id, 'image' => 'posts/own.jpg', 'caption' => 'own post']);
        $followedPost = Post::create(['user_id' => $followed->id, 'image' => 'posts/followed.jpg', 'caption' => 'followed post']);
        $otherPost = Post::create(['user_id' => $other->id, 'image' => 'posts/other.jpg', 'caption' => 'other post']);

        $response = $this->actingAs($actor)->get(route('dashboard'));

        $response->assertSee($ownPost->caption)
            ->assertSee($followedPost->caption)
            ->assertDontSee($otherPost->caption);
    }

    public function test_ajax_follow_public_account_returns_accepted_and_notifies(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create(['is_private' => false]);

        $this->actingAs($actor)
            ->postJson("/ajax/users/{$target->username}/follow")
            ->assertOk()
            ->assertJson(['followed' => true, 'status' => 'accepted']);

        $this->assertTrue($actor->following()->whereKey($target)->exists());
        $this->assertDatabaseHas('notifications', ['actor_id' => $actor->id, 'type' => 'follow']);
    }

    public function test_ajax_follow_private_account_returns_pending(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create(['is_private' => true]);

        $this->actingAs($actor)
            ->postJson("/ajax/users/{$target->username}/follow")
            ->assertOk()
            ->assertJson(['followed' => false, 'status' => 'pending']);
    }

    public function test_ajax_unfollow_returns_json(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();
        $actor->following()->attach($target, ['status' => 'accepted']);

        $this->actingAs($actor)
            ->deleteJson("/ajax/users/{$target->username}/follow")
            ->assertOk()
            ->assertJson(['followed' => false]);

        $this->assertFalse($actor->following()->whereKey($target)->exists());
    }
}

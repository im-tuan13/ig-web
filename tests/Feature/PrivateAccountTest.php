<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Story;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_following_a_public_account_is_instant(): void
    {
        $follower = User::factory()->create();
        $target = User::factory()->create(['is_private' => false]);

        $this->actingAs($follower)->post(route('users.follow', $target))->assertRedirect();

        $this->assertDatabaseHas('follows', [
            'follower_id' => $follower->id,
            'following_id' => $target->id,
            'status' => 'accepted',
        ]);
    }

    public function test_following_a_private_account_creates_pending_request(): void
    {
        $follower = User::factory()->create();
        $target = User::factory()->create(['is_private' => true]);

        $this->actingAs($follower)->post(route('users.follow', $target))->assertRedirect();

        $this->assertDatabaseHas('follows', [
            'follower_id' => $follower->id,
            'following_id' => $target->id,
            'status' => 'pending',
        ]);
    }

    public function test_pending_request_is_not_counted_or_listed_until_accepted(): void
    {
        $follower = User::factory()->create();
        $owner = User::factory()->create(['is_private' => true]);

        $this->actingAs($follower)->post(route('users.follow', $owner));

        $this->actingAs($owner)
            ->get(route('users.show', $owner))
            ->assertSee('0</span>', false);
        $this->actingAs($owner)
            ->get(route('users.followers', $owner))
            ->assertJsonCount(0, 'data');

        $follow = Follow::query()->firstOrFail();
        $this->actingAs($owner)->post(route('follow-requests.accept', $follow));

        $this->actingAs($owner)
            ->get(route('users.show', $owner))
            ->assertSee('1</span>', false);
        $this->actingAs($owner)
            ->get(route('users.followers', $owner))
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $follower->id);
    }

    public function test_private_account_owner_can_accept_request_and_posts_become_visible(): void
    {
        $follower = User::factory()->create();
        $owner = User::factory()->create(['is_private' => true]);
        $post = Post::create(['user_id' => $owner->id, 'image' => 'posts/private.jpg', 'caption' => 'Private caption']);

        $this->actingAs($follower)->post(route('users.follow', $owner));
        $followModel = Follow::query()
            ->where('follower_id', $follower->id)
            ->where('following_id', $owner->id)
            ->firstOrFail();

        $this->actingAs($owner)
            ->post(route('follow-requests.accept', $followModel))
            ->assertRedirect();

        $this->assertDatabaseHas('follows', [
            'id' => $followModel->id,
            'status' => 'accepted',
        ]);
        $this->actingAs($follower)
            ->get(route('users.show', $owner))
            ->assertSee(route('posts.show', $post), false)
            ->assertSee($post->image, false);
    }

    public function test_private_account_owner_can_reject_request(): void
    {
        $follower = User::factory()->create();
        $owner = User::factory()->create(['is_private' => true]);

        $this->actingAs($follower)->post(route('users.follow', $owner));
        $follow = Follow::query()->firstOrFail();

        $this->actingAs($owner)
            ->delete(route('follow-requests.reject', $follow))
            ->assertRedirect();

        $this->assertDatabaseMissing('follows', ['id' => $follow->id]);
    }

    public function test_another_user_cannot_accept_or_reject_someone_elses_request(): void
    {
        $follower = User::factory()->create();
        $owner = User::factory()->create(['is_private' => true]);
        $other = User::factory()->create();

        $this->actingAs($follower)->post(route('users.follow', $owner));
        $follow = Follow::query()->firstOrFail();

        $this->actingAs($other)
            ->post(route('follow-requests.accept', $follow))
            ->assertForbidden();
        $this->actingAs($other)
            ->delete(route('follow-requests.reject', $follow))
            ->assertForbidden();
    }

    public function test_non_follower_cannot_see_private_posts_or_stories_in_feed_or_explore(): void
    {
        $viewer = User::factory()->create();
        $privateOwner = User::factory()->create(['is_private' => true]);
        $post = Post::create(['user_id' => $privateOwner->id, 'image' => 'posts/hidden.jpg', 'caption' => 'Hidden private post']);
        Story::create(['user_id' => $privateOwner->id, 'image' => 'stories/hidden.jpg', 'expires_at' => now()->addDay()]);

        $this->actingAs($viewer)
            ->get(route('dashboard'))
            ->assertDontSee($post->caption)
            ->assertDontSee('stories/hidden.jpg');

        $this->actingAs($viewer)
            ->get(route('users.show', $privateOwner))
            ->assertSee('This Account is Private')
            ->assertDontSee(route('posts.show', $post), false);

        $this->actingAs($viewer)
            ->get(route('explore.index'))
            ->assertDontSee($post->caption);
    }

    public function test_stranger_cannot_directly_view_private_users_post_via_url(): void
    {
        $stranger = User::factory()->create();
        $privateOwner = User::factory()->create(['is_private' => true]);
        $post = Post::create(['user_id' => $privateOwner->id, 'image' => 'posts/hidden.jpg', 'caption' => 'Hidden private post']);

        $this->actingAs($stranger)
            ->get(route('posts.show', $post))
            ->assertForbidden();
    }

    public function test_follower_can_directly_view_private_users_post_via_url(): void
    {
        $follower = User::factory()->create();
        $privateOwner = User::factory()->create(['is_private' => true]);
        $post = Post::create(['user_id' => $privateOwner->id, 'image' => 'posts/visible.jpg', 'caption' => 'Visible to followers']);

        $this->actingAs($follower)->post(route('users.follow', $privateOwner));
        $follow = Follow::query()->firstOrFail();
        $this->actingAs($privateOwner)->post(route('follow-requests.accept', $follow));

        $this->actingAs($follower)
            ->get(route('posts.show', $post))
            ->assertOk();
    }

    public function test_stranger_cannot_directly_view_private_users_story_via_url(): void
    {
        $stranger = User::factory()->create();
        $privateOwner = User::factory()->create(['is_private' => true]);
        $story = Story::create(['user_id' => $privateOwner->id, 'image' => 'stories/hidden.jpg', 'expires_at' => now()->addDay()]);

        $this->actingAs($stranger)
            ->get(route('stories.show', $story))
            ->assertForbidden();
    }

    public function test_story_viewer_navigation_does_not_leak_stories_of_unfollowed_users(): void
    {
        $viewer = User::factory()->create();
        $unrelatedStranger = User::factory()->create(['is_private' => false]);

        $ownStory = Story::create(['user_id' => $viewer->id, 'image' => 'stories/own.jpg', 'expires_at' => now()->addDay()]);
        Story::create(['user_id' => $unrelatedStranger->id, 'image' => 'stories/stranger.jpg', 'expires_at' => now()->addDay()]);

        $this->actingAs($viewer)
            ->get(route('stories.show', $ownStory))
            ->assertOk()
            ->assertDontSee('stories/stranger.jpg', false);
    }
}

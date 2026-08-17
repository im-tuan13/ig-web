<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_collection(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('collections.store'), ['name' => 'Resep Favorit'])->assertRedirect();
        $this->assertDatabaseHas('collections', ['user_id' => $user->id, 'name' => 'Resep Favorit']);
    }

    public function test_adding_post_to_collection_also_saves_it(): void
    {
        $user = User::factory()->create(); $postOwner = User::factory()->create();
        $post = Post::create(['user_id' => $postOwner->id, 'image' => 'posts/a.jpg', 'caption' => 'test']);
        $collection = Collection::create(['user_id' => $user->id, 'name' => 'Resep']);
        $this->actingAs($user)->post(route('collections.posts.store', [$collection, $post]))->assertRedirect();
        $this->assertTrue($collection->posts()->whereKey($post->id)->exists());
        $this->assertTrue($user->savedPosts()->whereKey($post->id)->exists());
    }

    public function test_user_can_remove_post_from_collection(): void
    {
        $user = User::factory()->create(); $postOwner = User::factory()->create();
        $post = Post::create(['user_id' => $postOwner->id, 'image' => 'posts/a.jpg', 'caption' => 'test']);
        $collection = Collection::create(['user_id' => $user->id, 'name' => 'Resep']); $collection->posts()->attach($post);
        $this->actingAs($user)->delete(route('collections.posts.destroy', [$collection, $post]))->assertRedirect();
        $this->assertFalse($collection->posts()->whereKey($post->id)->exists());
    }

    public function test_stranger_cannot_view_someone_elses_collection(): void
    {
        $owner = User::factory()->create(); $stranger = User::factory()->create();
        $collection = Collection::create(['user_id' => $owner->id, 'name' => 'Private stuff']);
        $this->actingAs($stranger)->get(route('collections.show', $collection))->assertForbidden();
    }

    public function test_deleting_collection_does_not_delete_the_underlying_saved_post(): void
    {
        $user = User::factory()->create(); $postOwner = User::factory()->create();
        $post = Post::create(['user_id' => $postOwner->id, 'image' => 'posts/a.jpg', 'caption' => 'test']);
        $collection = Collection::create(['user_id' => $user->id, 'name' => 'Resep']); $collection->posts()->attach($post); $user->savedPosts()->syncWithoutDetaching([$post->id]);
        $this->actingAs($user)->delete(route('collections.destroy', $collection))->assertRedirect();
        $this->assertDatabaseMissing('collections', ['id' => $collection->id]);
        $this->assertTrue($user->savedPosts()->whereKey($post->id)->exists());
    }
}

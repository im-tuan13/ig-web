<?php

namespace Tests\Feature;

use App\Models\Highlight;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HighlightTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_highlight_from_own_story(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $story = Story::create(['user_id' => $user->id, 'image' => 'stories/one.jpg', 'expires_at' => now()->subDay()]);
        $cover = UploadedFile::fake()->create('cover.jpg', 1024, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('highlights.store'), [
            'title' => 'Summer', 'story_ids' => [$story->id], 'cover_image' => $cover,
        ]);

        $highlight = Highlight::first();
        $response->assertRedirect(route('highlights.show', $highlight));
        $this->assertDatabaseHas('highlights', ['user_id' => $user->id, 'title' => 'Summer']);
        $this->assertDatabaseHas('stories', ['id' => $story->id, 'highlight_id' => $highlight->id]);
        Storage::disk('public')->assertExists($highlight->cover_image);
    }

    public function test_creating_a_highlight_without_stories_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('highlights.store'), [
            'title' => 'Empty highlight',
        ])->assertRedirect()
            ->assertSessionHasErrors('story_ids');
    }

    public function test_user_cannot_add_someone_elses_story(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $story = Story::create(['user_id' => $other->id, 'image' => 'stories/other.jpg', 'expires_at' => now()]);

        $this->actingAs($user)->post(route('highlights.store'), [
            'title' => 'Nope', 'story_ids' => [$story->id],
        ])->assertForbidden();
        $this->assertDatabaseCount('highlights', 0);
    }

    public function test_only_owner_can_update_or_delete_highlight(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $highlight = Highlight::create(['user_id' => $owner->id, 'title' => 'Original']);

        $this->actingAs($other)->put(route('highlights.update', $highlight), ['title' => 'Changed'])
            ->assertForbidden();
        $this->actingAs($other)->delete(route('highlights.destroy', $highlight))->assertForbidden();
        $this->assertDatabaseHas('highlights', ['id' => $highlight->id, 'title' => 'Original']);
    }

    public function test_other_user_can_view_a_highlight_including_expired_stories(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $highlight = Highlight::create(['user_id' => $owner->id, 'title' => 'Archive']);
        Story::create(['user_id' => $owner->id, 'highlight_id' => $highlight->id, 'image' => 'stories/expired.jpg', 'expires_at' => now()->subDay()]);

        $this->actingAs($viewer)->get(route('highlights.show', $highlight))
            ->assertOk()
            ->assertSee('Archive');
    }
}

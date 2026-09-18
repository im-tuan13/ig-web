<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_group_conversation(): void
    {
        $creator = User::factory()->create();
        $memberA = User::factory()->create();
        $memberB = User::factory()->create();
        $creator->following()->attach([$memberA->id, $memberB->id], ['status' => 'accepted']);

        $response = $this->actingAs($creator)->post(route('messages.group.store'), [
            'name' => 'Study Squad',
            'participants' => [$memberA->id, $memberB->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('conversations', ['name' => 'Study Squad', 'is_group' => true]);
        $conversation = Conversation::where('name', 'Study Squad')->firstOrFail();
        $this->assertTrue($conversation->participants()->whereKey($creator->id)->exists());
        $this->assertTrue($conversation->participants()->whereKey($memberA->id)->exists());
        $this->assertTrue($conversation->participants()->whereKey($memberB->id)->exists());
    }

    public function test_group_conversation_requires_at_least_two_other_participants(): void
    {
        $creator = User::factory()->create();
        $memberA = User::factory()->create();

        $response = $this->actingAs($creator)->post(route('messages.group.store'), [
            'name' => 'Too small',
            'participants' => [$memberA->id],
        ]);

        $response->assertSessionHasErrors('participants');
        $this->assertDatabaseMissing('conversations', ['name' => 'Too small']);
    }

    public function test_any_member_can_send_a_message_in_a_group(): void
    {
        $creator = User::factory()->create();
        $memberA = User::factory()->create();
        $memberB = User::factory()->create();
        $conversation = Conversation::createGroup($creator, [$memberA->id, $memberB->id], 'Trio');

        $this->actingAs($memberB)
            ->post(route('messages.store', $conversation), ['message' => 'Hi all'])
            ->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $memberB->id,
            'message' => 'Hi all',
        ]);
    }

    public function test_group_conversation_displays_group_name_in_list(): void
    {
        $creator = User::factory()->create();
        $memberA = User::factory()->create();
        $memberB = User::factory()->create();
        $conversation = Conversation::createGroup($creator, [$memberA->id, $memberB->id], 'Trio');
        $conversation->messages()->create(['user_id' => $creator->id, 'message' => 'Hello group']);

        $this->actingAs($creator)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSee('Trio');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_conversations_list(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conv = Conversation::findOrCreateBetween($user, $other);
        $conv->messages()->create(['user_id' => $user->id, 'message' => 'Hello']);

        $response = $this->actingAs($user)->get(route('messages.index'));

        $response->assertOk()
            ->assertSee($other->username);
    }

    public function test_user_can_open_conversation(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conv = Conversation::findOrCreateBetween($user, $other);
        $conv->messages()->create(['user_id' => $user->id, 'message' => 'Hi there']);

        $response = $this->actingAs($user)->get(route('messages.show', $conv));

        $response->assertOk()
            ->assertSee('Hi there')
            ->assertSee($other->username);
    }

    public function test_user_cannot_open_conversation_they_are_not_part_of(): void
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $intruder = User::factory()->create();

        $conv = Conversation::findOrCreateBetween($user, $user2);

        $this->actingAs($intruder)
            ->get(route('messages.show', $conv))
            ->assertForbidden();
    }

    public function test_user_can_send_message(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conv = Conversation::findOrCreateBetween($user, $other);

        $response = $this->actingAs($user)->postJson(route('messages.store', $conv), [
            'message' => 'Hello, world!',
        ]);

        $response->assertCreated()
            ->assertJson(['message' => 'Hello, world!']);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conv->id,
            'user_id' => $user->id,
            'message' => 'Hello, world!',
        ]);
    }

    public function test_user_cannot_send_message_in_unowned_conversation(): void
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $intruder = User::factory()->create();

        $conv = Conversation::findOrCreateBetween($user, $user2);

        $this->actingAs($intruder)
            ->postJson(route('messages.store', $conv), ['message' => 'Hey'])
            ->assertForbidden();
    }

    public function test_poll_returns_new_messages(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conv = Conversation::findOrCreateBetween($user, $other);

        // Create an old message
        $conv->messages()->create([
            'user_id' => $other->id,
            'message' => 'Old message',
        ]);

        // Poll with a future timestamp should return nothing
        $after = now()->addHour()->toIso8601String();

        $response = $this->actingAs($user)
            ->getJson(route('messages.poll', $conv) . '?after=' . urlencode($after));

        $response->assertOk()
            ->assertJsonCount(0);
    }

    public function test_conversation_view_renders_messages_in_chronological_order_with_date_dividers(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $conversation = Conversation::findOrCreateBetween($user, $other);

        $yesterday = $conversation->messages()->create(['user_id' => $other->id, 'message' => 'Yesterday']);
        $yesterday->created_at = Carbon::parse('2026-08-20 23:59:00');
        $yesterday->saveQuietly();
        $today = $conversation->messages()->create(['user_id' => $user->id, 'message' => 'Today']);
        $today->created_at = Carbon::parse('2026-08-21 00:01:00');
        $today->saveQuietly();

        $html = $this->actingAs($user)->get(route('messages.show', $conversation))->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'data-message-id="'.$today->id.'"'), strpos($html, 'data-message-id="'.$yesterday->id.'"'));
        $this->assertSame(2, substr_count($html, 'data-date-divider'));
    }

    public function test_poll_returns_cross_day_messages_in_chronological_order(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $conversation = Conversation::findOrCreateBetween($user, $other);
        $after = Carbon::parse('2026-08-20 23:00:00')->toIso8601String();

        $late = $conversation->messages()->create(['user_id' => $other->id, 'message' => 'Late yesterday']);
        $late->created_at = Carbon::parse('2026-08-20 23:30:00');
        $late->saveQuietly();
        $early = $conversation->messages()->create(['user_id' => $other->id, 'message' => 'Early today']);
        $early->created_at = Carbon::parse('2026-08-21 00:30:00');
        $early->saveQuietly();

        $messages = $this->actingAs($user)->getJson(route('messages.poll', $conversation) . '?after=' . urlencode($after))->assertOk()->json();

        $this->assertSame(['Late yesterday', 'Early today'], array_column($messages, 'message'));
    }

    public function test_unread_count_works(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conv = Conversation::findOrCreateBetween($user, $other);
        $conv->messages()->create(['user_id' => $other->id, 'message' => 'Unread!']);

        $response = $this->actingAs($user)->getJson(route('messages.unread.count'));

        $response->assertOk()
            ->assertJson(['count' => 1]);
    }

    public function test_marking_conversation_as_read(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conv = Conversation::findOrCreateBetween($user, $other);
        $conv->messages()->create(['user_id' => $other->id, 'message' => 'Unread!']);

        $this->actingAs($user)->post(route('messages.read', $conv));

        $this->assertEquals(0, $conv->unreadCountFor($user));
    }

    public function test_find_or_create_between_users_returns_existing(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conv1 = Conversation::findOrCreateBetween($user, $other);
        $conv2 = Conversation::findOrCreateBetween($user, $other);

        $this->assertEquals($conv1->id, $conv2->id);
    }

    public function test_empty_conversations_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('messages.index'));

        $response->assertOk()
            ->assertSee('No messages yet');
    }
}

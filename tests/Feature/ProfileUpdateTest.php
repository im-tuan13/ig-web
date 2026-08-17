<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_form_can_enable_private_account_without_other_changes(): void
    {
        $user = User::factory()->create(['is_private' => false]);

        $this->actingAs($user)
            ->post(route('profile.update'), [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'bio' => $user->bio,
                'is_private' => '1',
            ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('success', 'Profile berhasil diperbarui.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_private' => true,
        ]);
    }
}

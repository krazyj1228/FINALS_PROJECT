<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_before_creating_a_portfolio(): void
    {
        $this->get(route('portfolios.create'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_register_and_continue_to_the_requested_portfolio_form(): void
    {
        $this->get(route('portfolios.create'))->assertRedirect(route('login'));

        $this->post(route('register.store'), [
            'name' => 'Jordan Lee',
            'email' => 'jordan@example.com',
            'password' => 'portfolio-pass-123',
            'password_confirmation' => 'portfolio-pass-123',
        ])->assertRedirect(route('portfolios.create'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Jordan Lee',
            'email' => 'jordan@example.com',
        ]);
        $this->get(route('portfolios.create'))->assertOk();
    }

    public function test_existing_user_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create(['email' => 'jordan@example.com']);

        $this->post(route('login.authenticate'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('portfolios.index'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_invalid_credentials_return_a_login_error(): void
    {
        User::factory()->create(['email' => 'jordan@example.com']);

        $this->from(route('login'))
            ->post(route('login.authenticate'), [
                'email' => 'jordan@example.com',
                'password' => 'incorrect-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }
}

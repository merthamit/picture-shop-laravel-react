<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_sends_a_verification_email_in_the_users_language(): void
    {
        Notification::fake();

        $this->withHeader('Accept-Language', 'tr')
            ->postJson('/api/user/register', ['name' => 'Ayşe', 'email' => 'ayse@example.com', 'password' => 'secret123'])
            ->assertOk()
            ->assertJsonPath('message', __('messages.account_created', [], 'tr'));

        $user = User::whereEmail('ayse@example.com')->firstOrFail();
        $this->assertSame('tr', $user->locale);
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_the_signed_link_verifies_the_email_and_redirects_to_the_shop(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->get($url)->assertRedirect('http://frontend.test/login?verified=1');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_a_tampered_verification_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $this->get("/api/email/verify/{$user->id}/" . sha1($user->email) . '?signature=fake')
            ->assertRedirect('http://frontend.test/login?verified=expired');
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_users_can_reset_a_forgotten_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson('/api/user/forgot-password', ['email' => $user->email])->assertOk();
        // Unknown addresses get the same answer (no account enumeration)
        $this->postJson('/api/user/forgot-password', ['email' => 'nobody@example.com'])->assertOk();

        $token = null;
        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use (&$token, $user) {
            $token = $notification->token;
            // The link points to the React reset page, not to Laravel
            return str_starts_with($notification->toMail($user)->actionUrl, 'http://frontend.test/reset-password?token=');
        });

        $this->postJson('/api/user/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_an_invalid_reset_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/user/reset-password', [
            'token' => 'invalid',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_login_errors_are_translated(): void
    {
        $this->withHeader('Accept-Language', 'tr')
            ->postJson('/api/user/login', ['email' => 'x@example.com', 'password' => 'wrong-pass'])
            ->assertJsonPath('error', 'Bu bilgilerle eşleşen bir hesap bulunamadı.');

        $this->withHeader('Accept-Language', 'tr')
            ->postJson('/api/user/register', [])
            ->assertJsonPath('errors.email.0', 'e-posta alanı zorunludur.');
    }

    public function test_users_can_save_their_language(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->putJson('/api/user/locale', ['locale' => 'tr'])->assertOk();
        $this->assertSame('tr', $user->fresh()->locale);
        $this->putJson('/api/user/locale', ['locale' => 'de'])->assertUnprocessable();
    }
}

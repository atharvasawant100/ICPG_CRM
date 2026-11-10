<?php

namespace Tests\Feature\Auth;

use App\Mail\ResetPasswordOtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_forgot_password_button(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee(route('password.request'));
        $response->assertSee('Forgot Password?');
    }

    public function test_forgot_password_page_renders_successfully(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Forgot Password?');
        $response->assertSee('no-reply@iconicpages.com');
        $response->assertSee(route('password.email'));
    }

    public function test_cannot_request_otp_for_non_existent_email(): void
    {
        Mail::fake();

        $response = $this->from(route('password.request'))->post(route('password.email'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHasErrors(['email']);
        Mail::assertNothingSent();
    }

    public function test_cannot_request_otp_for_deactivated_user(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'inactive@iconicpages.com',
            'is_active' => false,
        ]);

        $response = $this->from(route('password.request'))->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHasErrors(['email']);
        Mail::assertNothingSent();
    }

    public function test_otp_email_is_sent_from_no_reply_iconicpages_com(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'staff@iconicpages.com',
            'is_active' => true,
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('password.reset', ['email' => $user->email]));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);

        Mail::assertSent(ResetPasswordOtpMail::class, function (ResetPasswordOtpMail $mail) use ($user) {
            $mail->build();

            return $mail->hasTo($user->email)
                && $mail->hasFrom('no-reply@iconicpages.com')
                && strlen($mail->otp) === 6;
        });
    }

    public function test_reset_password_page_renders_with_email_prefilled(): void
    {
        $response = $this->get(route('password.reset', ['email' => 'test@iconicpages.com']));

        $response->assertStatus(200);
        $response->assertSee('value="test@iconicpages.com"', false);
        $response->assertSee('Verification OTP (6 digits)');
    }

    public function test_cannot_reset_password_with_incorrect_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'test@iconicpages.com',
            'password' => Hash::make('old-password-123'),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $response = $this->post(route('password.update'), [
            'email' => $user->email,
            'otp' => '999999',
            'password' => 'NewSecretPassword123!',
            'password_confirmation' => 'NewSecretPassword123!',
        ]);

        $response->assertSessionHasErrors(['otp']);
        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));
    }

    public function test_cannot_reset_password_with_expired_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'test@iconicpages.com',
            'password' => Hash::make('old-password-123'),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => Carbon::now()->subMinutes(16),
        ]);

        $response = $this->post(route('password.update'), [
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'NewSecretPassword123!',
            'password_confirmation' => 'NewSecretPassword123!',
        ]);

        $response->assertSessionHasErrors(['otp']);
        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));
    }

    public function test_successfully_resets_password_with_valid_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'test@iconicpages.com',
            'password' => Hash::make('old-password-123'),
            'is_active' => true,
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('654321'),
            'created_at' => now(),
        ]);

        $response = $this->post(route('password.update'), [
            'email' => $user->email,
            'otp' => '654321',
            'password' => 'BrandNewPassword999!',
            'password_confirmation' => 'BrandNewPassword999!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        // Token should be cleared
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);

        // User password should be updated
        $this->assertTrue(Hash::check('BrandNewPassword999!', $user->fresh()->password));

        // User can now log in with the new password
        $loginResponse = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'BrandNewPassword999!',
        ]);

        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_resend_otp_generates_new_otp(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'resend@iconicpages.com',
            'is_active' => true,
        ]);

        $response = $this->post(route('password.resend'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');
        Mail::assertSent(ResetPasswordOtpMail::class);
    }
}

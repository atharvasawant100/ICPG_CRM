<?php

namespace Database\Factories;

use App\Models\User;
use App\Rbac\Rbac;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => true,
            // 'role' is intentionally omitted. It is a read-only mirror of the
            // Spatie role, so a factory that sets it directly would create
            // exactly the drift RoleMirrorIntegrityTest guards against.
            // Use ->admin() or ->withRbacRole() to grant a real role.
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * A user holding the admin role.
     */
    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRbacRole(Rbac::ADMIN_ROLE);
        });
    }

    /**
     * A user holding a specific Spatie role.
     */
    public function withRbacRole(string $role): static
    {
        return $this->afterCreating(function (User $user) use ($role) {
            $user->assignRbacRole($role);
        });
    }

    /**
     * A deactivated user.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Jetstream\Features;

/**
 * @extends Factory<User>
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
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'remember_token' => Str::random(10),
            'role_id' => User::ROL_LECTOR_ID,
        ];
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

    /**
     * Indicate that the user has the "administrador" role.
     */
    public function administrador(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Rol::where('codigo', User::ROLE_ADMIN)->value('id'),
        ]);
    }

    /**
     * Alias de administrador() conservado para los tests existentes.
     */
    public function admin(): static
    {
        return $this->administrador();
    }

    /**
     * Indicate that the user has the "editor" role.
     */
    public function editor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Rol::where('codigo', User::ROLE_EDITOR)->value('id'),
        ]);
    }

    /**
     * Indicate that the user has the "lector" role (default).
     */
    public function lector(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Rol::where('codigo', User::ROLE_LECTOR)->value('id'),
        ]);
    }

    /**
     * Indicate that the user should have a personal team.
     */
    public function withPersonalTeam(?callable $callback = null): static
    {
        if (! Features::hasTeamFeatures()) {
            return $this->state([]);
        }

        return $this->has(
            Team::factory()
                ->state(fn (array $attributes, User $user) => [
                    'name' => $user->name.'\'s Team',
                    'user_id' => $user->id,
                    'personal_team' => true,
                ])
                ->when(is_callable($callback), $callback),
            'ownedTeams'
        );
    }
}

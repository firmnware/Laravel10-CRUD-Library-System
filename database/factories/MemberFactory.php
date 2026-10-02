<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            // member_code sengaja TIDAK diisi — biar Member::booted()
            // (generator tunggal, C2) yang memproduksinya.
            'join_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'status' => 'active',
        ];
    }

    public function blocked(): static
    {
        return $this->state(fn () => ['status' => 'blocked']);
    }
}

<?php

namespace Database\Factories;

use App\Models\SmsTopup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SmsTopup>
 */
class SmsTopupFactory extends Factory
{
    protected $model = SmsTopup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => 1000.00,
            'notes' => fake()->sentence(),
            'user_id' => null,
        ];
    }
}

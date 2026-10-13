<?php

namespace Database\Factories;

use App\Models\SmsLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SmsLog>
 */
class SmsLogFactory extends Factory
{
    protected $model = SmsLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipient' => '+9477'.fake()->numerify('#######'),
            'message' => fake()->sentence(),
            'sms_count' => 1,
            'cost_per_sms' => 0.8500,
            'total_cost' => 0.8500,
            'is_double_charge' => false,
            'status' => 'sent',
            'sender_id' => 'Sportynix',
            'response' => json_encode(['success' => true]),
            'user_id' => null,
        ];
    }
}

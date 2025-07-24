<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;

class BreakTimeFactory extends Factory
{
    /**
     * The name of the factry's corresponding model.
     * 
     * @var string
     */
    protected $model = BreakTime::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => $this->faker->randomNumber(),
            'branch_number' => '1',
            'started_at' => $this->faker->dateTimeBetween('12:00:00', '12:00:00'),
            'ended_at' => $this->faker->dateTimeBetween('13:00:00', '13:00:00'),
        ];
    }
}

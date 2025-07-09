<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;
use App\Models\Attendance;

class AttendanceFactory extends Factory
{
    /**
     * The name of the factry's corresponding model.
     * 
     * @var string
     */
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = Faker::create('ja_JP');

        $pastDate = $faker->dateTimeBetween('-2 months', 'now');
        $pastDateForm = $pastDate->format('Y-m-d H:i:s');

        return [
            'user_id' => $this->faker->numberBetween(1,5),
            'date' => $pastDate->format('Y-m-d'),
            'status' => $this->faker->numberBetween(3,3),
            'attendanced_at' => $pastDate->format('Y-m-d H:i:s'),
            'leaved_at' => $this->faker->dateTimeBetween(
                $pastDate,
                date('Y-m-d H:i:s',
                strtotime('+10 hour', 
                strtotime($pastDateForm)
            ))), // 出勤開始時刻の10時間後まで。
            'total_break_time' => $this->faker->dateTimeBetween(
                '01:00:00',
                '02:00:00',
            )->format('H:i:s'),
            'remarks' => $this->faker->sentence(3),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

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

        $pastDate = $faker->unique()->dateTimeBetween('-11 weeks', '-4 weeks')->format('Y-m-d');
        $attendanced_at = Carbon::parse($pastDate)->copy()->startOfDay()->addHours(9);
        $fiveHours = Carbon::parse($pastDate)->copy()->startOfDay()->addHours(14);
        $tenHours = Carbon::parse($pastDate)->copy()->startOfDay()->addHours(19);
        $leaved_at = $this->faker->dateTimeBetween($fiveHours, $tenHours);
         // 出勤開始時刻の4時間後から10時間後まででランダムに。

        $total_break_time = $this->faker->dateTimeBetween('01:00:00', '01:00:00');

        $net_attendance_time = Carbon::parse($attendanced_at)->diff(Carbon::parse($leaved_at)); 
        $now = Carbon::now();
        $carbon_attendance_time = Carbon::instance($now->copy()->add($net_attendance_time));

        $break_time_dateInterval = Carbon::parse('0:0:0')->diff($total_break_time);
        $carbon_break_time = Carbon::instance($now->copy()->add($break_time_dateInterval));

        $total_attendance_time = Carbon::parse('0:0:0')->add($carbon_break_time->diff($carbon_attendance_time));

        return [
            'user_id' => $this->faker->numberBetween(1,5),
            'date' => $pastDate,
            'status' => $this->faker->numberBetween(3,3),
            'attendanced_at' => $attendanced_at->format('Y-m-d H:i:s'),
            'leaved_at' => $leaved_at->format('Y-m-d H:i:s'),
            'total_break_time' => $total_break_time,
            'total_attendance_time' => $total_attendance_time->format('H:i:s'),
            'remarks' => null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Attendance $attendance) {
            BreakTime::factory()->count(1)->create([
                'attendance_id' => $attendance->id,
                'started_at' => Carbon::parse($attendance->attendanced_at)->addHours(3)->format('Y-m-d H:i:s'),
                'ended_at' => Carbon::parse($attendance->attendanced_at)->addHours(4)->format('Y-m-d H:i:s'),
            ]);
        });
    } 
}

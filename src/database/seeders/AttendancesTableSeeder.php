<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // テストケースID:5「ステータス確認機能」に必要な当日出勤データを生成
        $now = Carbon::now();
        $today = $now->isoFormat('YYYY-MM-DD');

        $attendanced_at = $now->copy()->subHours(9);
        $attendance = Attendance::create([
            'user_id' => '3',
            'date' => $today,
            'status' => '3',  // 退勤済
            'attendanced_at' => $attendanced_at,
            'leaved_at' => $now,
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);

        $started_at = $now->copy()->startOfDay()->addHours(12);
        $ended_at = $now->copy()->startOfDay()->addHours(13);
        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => $started_at,
            'ended_at' => $ended_at,
        ]);


        $attendance = Attendance::create([
            'user_id' => '2',
            'date' => $today,
            'status' => '2',  // 休憩中
            'attendanced_at' => $now,
        ]);

        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => $now,
        ]);

        
        $param =[
            'user_id' => '1',
            'date' => $today,
            'status' => '1',  // 出勤中
            'attendanced_at' => $now,
        ];
        DB::table('attendances')->insert($param);

        // 他テストケースでも使用する当月データとして7月分を生成
        $attendance = Attendance::create([
            'user_id' => '4',
            'date' => '2025-07-01',
            'status' => '3',
            'attendanced_at' => '2025-07-01 09:00:00',
            'leaved_at' => '2025-07-01 19:00:00',
            'total_break_time' => '02:00:00',
            'total_attendance_time' => '08:00:00',
        ]);

        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-01 12:00:00',
            'ended_at' => '2025-07-01 13:00:00',
        ]);
        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '2',
            'started_at' => '2025-07-01 15:00:00',
            'ended_at' => '2025-07-01 15:30:00',
        ]);
        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '3',
            'started_at' => '2025-07-01 17:30:00',
            'ended_at' => '2025-07-01 18:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2025-07-01',
            'status' => '3',
            'attendanced_at' => '2025-07-01 08:30:00',
            'leaved_at' => '2025-07-01 17:30:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);
        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-01 12:00:00',
            'ended_at' => '2025-07-01 13:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '2',
            'date' => '2025-07-01',
            'status' => '3',
            'attendanced_at' => '2025-07-01 09:15:00',
            'leaved_at' => '2025-07-01 18:15:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);
        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-01 12:00:00',
            'ended_at' => '2025-07-01 13:00:00',
        ]);


        $attendance = Attendance::create([
            'user_id' => '4',
            'date' => '2025-07-02',
            'status' => '3',
            'attendanced_at' => '2025-07-02 09:00:00',
            'leaved_at' => '2025-07-02 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);
        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-02 12:00:00',
            'ended_at' => '2025-07-02 13:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2025-07-02',
            'status' => '3',
            'attendanced_at' => '2025-07-02 09:00:00',
            'leaved_at' => '2025-07-02 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);
        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-02 12:00:00',
            'ended_at' => '2025-07-02 13:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '2',
            'date' => '2025-07-02',
            'status' => '3',
            'attendanced_at' => '2025-07-02 09:00:00',
            'leaved_at' => '2025-07-02 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);
        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-02 12:00:00',
            'ended_at' => '2025-07-02 13:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '4',
            'date' => '2025-07-03',
            'status' => '3',
            'attendanced_at' => '2025-07-03 09:00:00',
            'leaved_at' => '2025-07-03 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);

        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-03 12:00:00',
            'ended_at' => '2025-07-03 13:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '4',
            'date' => '2025-07-04',
            'status' => '3',
            'attendanced_at' => '2025-07-04 09:00:00',
            'leaved_at' => '2025-07-04 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);

        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-04 12:00:00',
            'ended_at' => '2025-07-04 13:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '4',
            'date' => '2025-07-07',
            'status' => '3',
            'attendanced_at' => '2025-07-07 09:00:00',
            'leaved_at' => '2025-07-07 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);

        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-07 12:00:00',
            'ended_at' => '2025-07-07 13:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '4',
            'date' => '2025-07-08',
            'status' => '3',
            'attendanced_at' => '2025-07-08 09:00:00',
            'leaved_at' => '2025-07-08 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);

        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-08 12:00:00',
            'ended_at' => '2025-07-08 13:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '4',
            'date' => '2025-07-09',
            'status' => '3',
            'attendanced_at' => '2025-07-09 09:00:00',
            'leaved_at' => '2025-07-09 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);

        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-09 12:00:00',
            'ended_at' => '2025-07-09 13:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '4',
            'date' => '2025-07-10',
            'status' => '3',
            'attendanced_at' => '2025-07-10 09:00:00',
            'leaved_at' => '2025-07-10 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);

        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-10 12:00:00',
            'ended_at' => '2025-07-10 13:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '4',
            'date' => '2025-07-11',
            'status' => '3',
            'attendanced_at' => '2025-07-11 09:00:00',
            'leaved_at' => '2025-07-11 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);

        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-11 12:00:00',
            'ended_at' => '2025-07-11 13:00:00',
        ]);

    }
    
}

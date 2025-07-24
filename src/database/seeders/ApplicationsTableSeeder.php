<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Application;
use App\Models\CorrectBreakTime;

class ApplicationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2025-07-17',
            'status' => '3',  // 出勤中
            'attendanced_at' => '2025-07-17 09:00:00',
            'leaved_at' => '2025-07-17 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);
        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-17 12:00:00',
            'ended_at' => '2025-07-17 13:00:00',
        ]);
        $application = Application::create([
            'attendance_id' => $attendance->id,
            'approval_status' => '0',
            'application_date' => '2025-07-18',
            'correct_attendanced_at' => '2025-07-17 09:30:00',
            'correct_leaved_at' => '2025-07-17 18:30:00',
            'remarks' => 'テストID:15のため',
        ]);
        $correct_break_time = CorrectBreakTime::create([
            'application_id' => $application->id,
            'branch_number' => '1',
            'started_at' => '2025-07-17 12:30:00',
            'ended_at' => '2025-07-17 13:30:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '2',
            'date' => '2025-07-17',
            'status' => '3',  // 出勤中
            'attendanced_at' => '2025-07-17 09:00:00',
            'leaved_at' => '2025-07-17 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);
        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-17 12:00:00',
            'ended_at' => '2025-07-17 13:00:00',
        ]);
        $application = Application::create([
            'attendance_id' => $attendance->id,
            'approval_status' => '0',
            'application_date' => '2025-07-18',
            'correct_attendanced_at' => '2025-07-17 09:30:00',
            'correct_leaved_at' => '2025-07-17 18:30:00',
            'remarks' => 'テストID:15のため',
        ]);
        $correct_break_time = CorrectBreakTime::create([
            'application_id' => $application->id,
            'branch_number' => '1',
            'started_at' => '2025-07-17 12:30:00',
            'ended_at' => '2025-07-17 13:30:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '4',
            'date' => '2025-07-17',
            'status' => '3',  // 出勤中
            'attendanced_at' => '2025-07-17 09:00:00',
            'leaved_at' => '2025-07-17 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);
        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-17 12:00:00',
            'ended_at' => '2025-07-17 13:00:00',
        ]);
        $application = Application::create([
            'attendance_id' => $attendance->id,
            'approval_status' => '0',
            'application_date' => '2025-07-18',
            'correct_attendanced_at' => '2025-07-17 09:30:00',
            'correct_leaved_at' => '2025-07-17 18:30:00',
            'remarks' => 'テストID:15のため',
        ]);
        $correct_break_time = CorrectBreakTime::create([
            'application_id' => $application->id,
            'branch_number' => '1',
            'started_at' => '2025-07-17 12:30:00',
            'ended_at' => '2025-07-17 13:30:00',
        ]);


        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2025-07-16',
            'status' => '3',  // 出勤中
            'attendanced_at' => '2025-07-16 09:00:00',
            'leaved_at' => '2025-07-16 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);
        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-16 12:00:00',
            'ended_at' => '2025-07-16 13:00:00',
        ]);
        $application = Application::create([
            'attendance_id' => $attendance->id,
            'approval_status' => '1',
            'application_date' => '2025-07-17',
            'correct_attendanced_at' => '2025-07-16 09:00:00',
            'correct_leaved_at' => '2025-07-16 18:00:00',
            'remarks' => 'テストID:15のため',
        ]);
        $correct_break_time = CorrectBreakTime::create([
            'application_id' => $application->id,
            'branch_number' => '1',
            'started_at' => '2025-07-16 12:00:00',
            'ended_at' => '2025-07-16 13:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '2',
            'date' => '2025-07-16',
            'status' => '3',  // 出勤中
            'attendanced_at' => '2025-07-16 09:00:00',
            'leaved_at' => '2025-07-16 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);
        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-16 12:00:00',
            'ended_at' => '2025-07-16 13:00:00',
        ]);
        $application = Application::create([
            'attendance_id' => $attendance->id,
            'approval_status' => '1',
            'application_date' => '2025-07-17',
            'correct_attendanced_at' => '2025-07-16 09:00:00',
            'correct_leaved_at' => '2025-07-16 18:00:00',
            'remarks' => 'テストID:15のため',
        ]);
        $correct_break_time = CorrectBreakTime::create([
            'application_id' => $application->id,
            'branch_number' => '1',
            'started_at' => '2025-07-16 12:00:00',
            'ended_at' => '2025-07-16 13:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => '4',
            'date' => '2025-07-16',
            'status' => '3',  // 出勤中
            'attendanced_at' => '2025-07-16 09:00:00',
            'leaved_at' => '2025-07-16 18:00:00',
            'total_break_time' => '01:00:00',
            'total_attendance_time' => '08:00:00',
        ]);
        $break_time = BreakTime::create([
            'attendance_id' => $attendance->id,
            'branch_number' => '1',
            'started_at' => '2025-07-16 12:00:00',
            'ended_at' => '2025-07-16 13:00:00',
        ]);
        $application = Application::create([
            'attendance_id' => $attendance->id,
            'approval_status' => '1',
            'application_date' => '2025-07-17',
            'correct_attendanced_at' => '2025-07-16 09:00:00',
            'correct_leaved_at' => '2025-07-16 18:00:00',
            'remarks' => 'テストID:15のため',
        ]);
        $correct_break_time = CorrectBreakTime::create([
            'application_id' => $application->id,
            'branch_number' => '1',
            'started_at' => '2025-07-16 12:00:00',
            'ended_at' => '2025-07-16 13:00:00',
        ]);

    }
}

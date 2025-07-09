<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CorrectRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Application;
use App\Models\CorrectBreakTime;
use App\Models\User;
use Carbon\Carbon;

class ApplicationController extends Controller
{
    public function application(CorrectRequest $request, $id) {

        $approval_status = '0';  // '0'を「承認待ち」とする
        $correct_attendanced_at = Carbon::parse($request->attendanced_at);
        $correct_leaved_at = Carbon::parse($request->leaved_at);
        $now = Carbon::now();
        $application_date = $now->isoFormat('YYYY-MM-DD');

        $application = Application::create([
            'attendance_id' => $id,
            'approval_status' => $approval_status,
            'application_date' => $application_date,
            'correct_attendanced_at' => $correct_attendanced_at,
            'correct_leaved_at' => $correct_leaved_at,
            'remarks' => $request->remarks,
        ]);

        $started_at_arrays = $request->started_at;
        $ended_at_arrays = $request->ended_at;

        $break_times = array_map(function ($started_at, $ended_at) {
            return ['started_at' => $started_at, 'ended_at' => $ended_at,];
        }, $started_at_arrays, $ended_at_arrays);

        $i = 0;
        foreach($break_times as $break_time) {
            $i++;
            $correct_break_time = CorrectBreakTime::create([
                'application_id' => $application['id'],
                'branch_number' => $i,
                'started_at' => Carbon::parse($break_time['started_at']),
                'ended_at' => Carbon::parse($break_time['ended_at']),
            ]);
        }
        return redirect()->route('attendance.detail',['id' => $id]);
    }

    public function list($tab) {
        if($tab == 'waiting') {
            $approval_status = '0';
        } elseif ($tab == 'approved') {
            $approval_status = '1'; 
        }

        $user_id = Auth::id();
        $user = User::find($user_id);
        
        if($user->is_admin) {
            // 管理者の場合は、全ユーザーの申請が対象。
            $applications = Application::with('attendance.user')->where('approval_status', $approval_status)->get();
        } else {
            // 一般ユーザーの場合は、ログイン中の自身の申請のみ
            $applications = Application::GetByUserAndApproval($user_id, $approval_status);
        }

        return view('application-list', compact('applications', 'user'));
    }

    public function detail($attendance_correct_request) {

        $application = Application::with('attendance.user')->find($attendance_correct_request);
        $correct_break_times = CorrectBreakTime::ApplicationId($attendance_correct_request)->get();

        $user = Auth::user(); // 権限判定のためログイン中のユーザーも取得。

        return view('application-detail', compact('user', 'application', 'correct_break_times', 'attendance_correct_request'));
    }

    public function update($attendance_correct_request) {

        $application = Application::with('attendance')->find($attendance_correct_request);
        $correct_break_times = CorrectBreakTime::ApplicationId($attendance_correct_request)->get();
        $attendance_id = $application->attendance_id;

        // まずは休憩時間を原データベースに更新し、休憩の合計時間を算出。
        $i = 0;
        $total_break_time = Carbon::parse("0:0:0");   // 休憩時間合計の変数初期化
        foreach($correct_break_times as $break_time) {
            $i++;
            $update_started_at = Carbon::parse($break_time['started_at']);
            $update_ended_at = Carbon::parse($break_time['ended_at']);
            $update_break_time = BreakTime::updateOrCreate(
                ['attendance_id' => $attendance_id, 'branch_number' => $i ],
                ['started_at' => $update_started_at,
                 'ended_at' => $update_ended_at,
            ]);

            $diff = $update_started_at->diff($update_ended_at);
            $total_break_time = $total_break_time->copy()->add($diff);
        }

        // 次に勤務時間の合計を集計。
        $attendanced_at = Carbon::parse($application->correct_attendanced_at);
        $leaved_at = Carbon::parse($application->correct_leaved_at);
        $net_attendance_time = $attendanced_at->diff($leaved_at);

        // 休憩時間を控除。共にCarbonオブジェクトに変換した上で、勤務開始終了 ー 休憩時間を算出。
        $now = Carbon::now();
        $carbon_attendance_time = Carbon::instance($now->copy()->add($net_attendance_time));
        $break_time_dateInterval = Carbon::parse('0:0:0')->diff($total_break_time);
        $carbon_break_time = Carbon::instance($now->copy()->add($break_time_dateInterval));
        $total_attendance_time = Carbon::parse('0:0:0')->add($carbon_break_time->diff($carbon_attendance_time));

        // 勤怠管理の原データベースを更新する
        $attendance = Attendance::find($attendance_id)->update([
            'attendanced_at' => $attendanced_at,
            'leaved_at' => $leaved_at,
            'total_break_time' => $total_break_time,
            'total_attendance_time' => $total_attendance_time,
            'remarks' => $application->remarks,
        ]);

        // 最後に申請ステータスを承認済にする

        $approval_status = '1'; // '1'が承認済み
        $application_id = $application->id;

        $application = Application::find($application_id)->update([
            'approval_status' => $approval_status,
        ]);

        return redirect()->route('application.detail',['attendance_correct_request' => $attendance_correct_request]);

    }
}

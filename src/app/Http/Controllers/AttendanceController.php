<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Application;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function today()
    {
        // 出勤登録画面の表示のために必要なデータを取得。
        $user_id = Auth::id();
        $user = User::find($user_id);

        $now = Carbon::now();
        $now->setLocale('ja');
        $today = $now->isoFormat('YYYY-MM-DD');
        $today_form = $now->isoFormat('YYYY年MM月DD日(ddd)');
        $isExist = Attendance::TodayAttendance($user_id, $today)->exists();
        if($isExist == "1") {
            $today_attendance = Attendance::TodayAttendance($user_id, $today)->first();
            return view('attendance', compact('user', 'now', 'today_form', 'today_attendance'));
        }else{
            return view('attendance', compact('user', 'now', 'today_form'));
        }        
    }

    public function store(Request $request)
    {
        // その日の最初の処理として出勤登録画面で「出勤」ボタンを押下したケース処理
        $user_id = Auth::id();
        $now = Carbon::now();
        $date = $now->isoFormat('YYYY-MM-DD');
        $status = $request->update_status;
        $attendanced_at = $now;
        $today_attendance = Attendance::create([
            'user_id' => $user_id,
            'date' => $date,
            'status' => $status,
            'attendanced_at' => $attendanced_at,
        ]);
        return redirect()->route('attendance')->with('today_attendance', $today_attendance);
    }

    public function update(Request $request)
    {
        // 出勤登録後の休憩や退勤のボタンを押下した場合の処理。
        $user_id = Auth::id();
        $now = Carbon::now();
        $today = $now->isoFormat('YYYY-MM-DD');
        $status = $request->update_status;
        $today_attendance = Attendance::TodayAttendance($user_id, $today)->first();
        switch($status) {
            case '2':
                // 「休憩入」ボタンを押下したケース処理。「休憩中」のステータスへ更新される
                $attendance_id = $today_attendance->id;
                $isExist = BreakTime::AttencanceId($attendance_id)->exists();
                if($isExist == 1) {
                    $branch_number = BreakTime::AttencanceId($attendance_id)
                        ->max('branch_number') + 1;
                } else {
                    $branch_number = 1;
                }
                $today_break_time = BreakTime::Create([
                    'attendance_id' => $attendance_id,
                    'branch_number' => $branch_number,
                    'started_at' => $now,
                ]);
                $today_attendance = Attendance::find($attendance_id)->update(['status' => $status,]);
                break;
            case '1':
                // 「休憩戻」ボタンを押下したケース処理。「出勤中」のステータスへ更新される
                $attendance_id = $today_attendance->id;
                $branch_number = BreakTime::AttencanceId($attendance_id)->max('branch_number');
                $break_time = BreakTime::GetBreakTime($attendance_id, $branch_number)->update([
                    'ended_at' => $now,
                ]);
                $total_break_time = Carbon::parse("0:0:0");   // 初期化
                // これまでの休憩時間の累積計算を行う
                for ($i = 1; $i <= $branch_number; $i++) {
                    $branch_break_time = BreakTime::GetBreakTime($attendance_id, $i)->first();
                    $branch_started_at = Carbon::parse($branch_break_time->started_at);
                    $branch_ended_at = Carbon::parse($branch_break_time->ended_at);
                    $diff = $branch_started_at->diff($branch_ended_at);
                    $total_break_time = $total_break_time->copy()->add($diff);
                }
                $today_attendance = Attendance::find($attendance_id)->update([
                    'status' => $status,
                    'total_break_time' => $total_break_time->format('H:i:s'),
                ]);
                break;     
            case '3':
                // 「退勤」ボタンを押下したケース処理。「退勤済」のステータスへ更新される
                $attendance_id = $today_attendance->id;
                $isExist = BreakTime::AttencanceId($attendance_id)->exists();
                if($isExist <> 1) {
                    // 休憩時間の入力がない場合、デフォルトとして12:00-13:00の1時間休憩を設定する
                    $total_break_time = Carbon::parse("1:0:0");
                    $break_time = BreakTime::Create([
                        'attendance_id' => $attendance_id,
                        'branch_number' => '1',
                        'started_at' => Carbon::parse("12:0:0"),
                        'ended_at' => Carbon::parse("13:0:0"),
                    ]);
                } else {
                    // 休憩時間の入力がある場合、（case '1'の処理で）計算済の累積休憩時間を取得。
                    $entered_total = Attendance::find($attendance_id)->total_break_time;
                    $total_break_time = Carbon::parse($entered_total);
                }
                // 最後に勤務時間の合計を集計してデータベースを更新する
                $attendanced_at = Carbon::parse($today_attendance['attendanced_at']);
                $leaved_at = $now;
                $net_attendance_time = $attendanced_at->diff($leaved_at);

                // 休憩時間を控除。共にCarbonオブジェクトに変換した上で、勤務開始・終了 ー 休憩時間を算出。
                $carbon_attendance_time = Carbon::instance($now->copy()->add($net_attendance_time));

                $break_time_dateInterval = Carbon::parse('0:0:0')->diff($total_break_time);
                $carbon_break_time = Carbon::instance($now->copy()->add($break_time_dateInterval));
                $total_attendance_time = Carbon::parse('0:0:0')->add($carbon_break_time->diff($carbon_attendance_time));

                $today_attendance = Attendance::find($attendance_id)->update([
                    'status' => $status,
                    'leaved_at' => $leaved_at,
                    'total_break_time' => $total_break_time->format('H:i:s'),
                    'total_attendance_time' => $total_attendance_time->format('H:i:s'),
                ]);
                break;
        }
        return redirect()->route('attendance')->with('today_attendance', $today_attendance);
    }

    public function detail($id) {
        $attendance = Attendance::find($id);
        $break_times = BreakTime::AttencanceId($id)->get();
        $attendance_user_id = $attendance->user_id;
        $attendance_user = User::find($attendance_user_id);
        $user = Auth::user(); // 権限判定のためログイン中のユーザーも取得。

        // 承認待ちのステータスの申請が存在しているかをチェックして結果をviewに渡す
        $approval_status = "0";  
        $isExistsApplication = Application::AttendanceIdApprovalStatus($id, $approval_status)->exists();

        return view('attendance-detail', compact('user', 'attendance_user', 'attendance', 'break_times', 'isExistsApplication', 'id'));

    }
}

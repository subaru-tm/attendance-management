<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListController extends Controller
{
    public function list(Request $request)
    {
        $user_id = Auth::id();
        $user = User::find($user_id);

        // 前月、翌月の指定があった場合は、その月を当月として設定。
        if ( isset($request['month']) )
        {
            $this_month = Carbon::parse($request['month'])->format('Y-m');
        } else {
            // ヘッダリンクから勤務一覧を開く等、前月翌月指定がない場合は今月を当月とする
            $now = Carbon::now();
            $this_month = $now->format('Y-m');
        }
        // 当月の該当ユーザーの勤務実績データをデータベースより取得する


        $thisMonthAttendances = Attendance::GetThisMonthWithUser($user_id, $this_month)->toArray();
        $attendance = Attendance::with('user')->where('user_id', $user_id)->first();

        $attendance_user = $attendance->user;

        // 勤務がない日の表示用に、当月の全ての日付を取得。
        $startOfMonth = Carbon::parse($this_month)->startOfMonth();
        $endOfMonth = Carbon::parse($this_month)->lastOfMonth();
        $dates = [];
        while ($startOfMonth->lte($endOfMonth)) {
            $dates[] = clone $startOfMonth;
            $startOfMonth->addDay();
        }
        // viewでの当月、前月、翌月のフォーム用に前月、翌月を設定しておく
        $this_month_display = Carbon::parse($this_month)->format('Y/m');
        $oneMonthAgo = Carbon::parse($this_month)->copy()->subMonth();
        $oneMonthLater = Carbon::parse($this_month)->copy()->addMonth();

        if (count($thisMonthAttendances) == 0)
        {
            // 当月の勤務データが一件もない場合、エラー回避のため、
            // viewには勤務データは渡さずに月指定用データとメッセージのみ渡す
            return view('attendance-list', compact(
                'user',
                'attendance_user',
                'this_month_display',
                'oneMonthAgo',
                'oneMonthLater',
            ))->with('message', 'ご指定の月の勤務データがありません');
        } else
        {
            // 今月の全日付に対して、出勤データがある日は出勤データを、
            // 出勤データがない日には日付とnull値をセットする配列を作成し、viewに渡す。
            $attendanceLists = [];
            foreach($dates as $date) {
                foreach($thisMonthAttendances as $attendance) {
                    if ( $date->isoFormat('YYYY-MM-DD') == $attendance['date']) {
                        // 出勤データがある日付にDBの値をセット。
                        $attendanceLists[] = $attendance;
                        break;
                    }
                    if ( Carbon::parse($attendance['date'])->lessThan($date) ) {
                        continue;
                            // 同じ日付を出力しないように、取得済の日付はスキップ
                    }
                }
                // 出勤データがない日付（で初めてループに登場した際に）は日付とnullをセット。
                if ( $date->isoFormat('YYYY-MM-DD') <> $attendance['date'] ) {
                    $attendanceLists[] = [
                        'id' => null,
                        'date' => $date->isoFormat('YYYY-MM-DD'),
                        'attendanced_at' => null,
                        'leaved_at' => null,
                        'total_break_time' => null,
                        'total_attendance_time' => null,
                    ];
                }
            }
        }

        return view('attendance-list', compact(
            'user',
            'attendance_user',
            'attendanceLists',
            'this_month_display',
            'oneMonthAgo',
            'oneMonthLater',
        ));
    }

    public function byStaff(Request $request, $id) {

        $user_id = $id;
        // 管理者用に勤怠データのユーザとログイン中ユーザーは別々に取得。
        $attendance_user = User::find($id); 
        $user = Auth::user();

        // 前月、翌月の指定があった場合は、その月を当月として設定。
        if ( isset($request['month']) )
        {
            $this_month = Carbon::parse($request['month'])->format('Y-m');
        } else {
            // 前月翌月指定がない場合は今月を当月とする
            $now = Carbon::now();
            $this_month = $now->format('Y-m');
        }
        // 当月の該当ユーザーの勤務実績データをデータベースより取得する
        $thisMonthAttendances = Attendance::ThisMonth($user_id, $this_month)->get();
        
        // 勤務がない日の表示用に、当月の全ての日付を取得。
        $startOfMonth = Carbon::parse($this_month)->startOfMonth();
        $endOfMonth = Carbon::parse($this_month)->lastOfMonth();
        $dates = [];
        while ($startOfMonth->lte($endOfMonth)) {
            $dates[] = clone $startOfMonth;
            $startOfMonth->addDay();
        }
        // viewでの当月、前月、翌月のフォーム用に前月、翌月を設定しておく
        $this_month_display = Carbon::parse($this_month)->format('Y/m');
        $oneMonthAgo = Carbon::parse($this_month)->copy()->subMonth();
        $oneMonthLater = Carbon::parse($this_month)->copy()->addMonth();
        
        if (count($thisMonthAttendances) == 0)
        {
            // 当月の勤務データが一件もない場合、エラー回避のため、
            // viewには勤務データは渡さずに月指定用データとメッセージのみ渡す
            return view('attendance-list', compact(
                'user',
                'attendance_user',
                'this_month_display',
                'oneMonthAgo',
                'oneMonthLater',
            ))->with('message', 'ご指定の月の勤務データがありません');
        } else
        {
            // 今月の全日付に対して、出勤データがある日は出勤データを、
            // 出勤データがない日には日付とnull値をセットする配列を作成し、viewに渡す。
            $attendanceLists = [];
            foreach($dates as $date) {
                foreach($thisMonthAttendances as $attendance) {
                    if ( $date->isoFormat('YYYY-MM-DD') == $attendance['date']) {
                        // 出勤データがある日付にDBの値をセット。
                        $attendanceLists[] = $attendance;
                        break;
                    }
                    if ( Carbon::parse($attendance['date'])->lessThan($date) ) {
                        continue;
                            // 同じ日付を出力しないように、取得済の日付はスキップ
                    }
                }
                // 出勤データがない日付（で初めてループに登場した際に）は日付とnullをセット。
                if ( $date->isoFormat('YYYY-MM-DD') <> $attendance['date'] ) {
                    $attendanceLists[] = [
                        'id' => null,
                        'date' => $date->isoFormat('YYYY-MM-DD'),
                        'attendanced_at' => null,
                        'leaved_at' => null,
                        'total_break_time' => null,
                        'total_attendance_time' => null,
                    ];
                }
            }
        }
        
        return view('attendance-list', compact(
            'user',
            'attendance_user',
            'attendanceLists',
            'this_month_display',
            'oneMonthAgo',
            'oneMonthLater',
        ));
    }
}

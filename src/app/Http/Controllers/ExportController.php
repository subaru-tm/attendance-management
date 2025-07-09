<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;


class ExportController extends Controller
{
    public function csvExport(Request $request, $id) {

        $user_id = $id;
        $attendance_user = User::find($id); 
        $user = Auth::user();
        
        $this_month = Carbon::parse($request['month'])->addMonth()->format('Y-m');

        $thisMonthAttendances = Attendance::ThisMonth($user_id, $this_month)->get()->toArray();
        
        // 勤務がない日の表示用に、当月の全ての日付を取得。
        $startOfMonth = Carbon::parse($this_month)->startOfMonth();
        $endOfMonth = Carbon::parse($this_month)->lastOfMonth();
        $dates = [];
        while ($startOfMonth->lte($endOfMonth)) {
            $dates[] = clone $startOfMonth;
            $startOfMonth->addDay();
        }

       
        // 今月の全日付に対して、出勤データがある日は出勤データを、
        // 出勤データがない日には日付とnull値をセットする配列を作成。
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

    // csvデータを生成
    $csvContent = [
        [ $attendance_user['name'] . 'さんの勤怠' ],
        ['日付', '出勤', '退勤', '休憩', '合計'],
    ];
    foreach ($attendanceLists as $attendance) {
        array_push($csvContent, [
            '0' => Carbon::parse($attendance['date'])->isoFormat('MM/DD(ddd)'),
            '1' => Carbon::parse($attendance['attendanced_at'])->format('H:i'),
            '2' => Carbon::parse($attendance['leaved_at'])->format('H:i'),
            '3' => Carbon::parse($attendance['total_break_time'])->format('H:i'),
            '4' => Carbon::parse($attendance['total_attendance_time'])->format('H:i'),
        ]);
    }


    $filename = 'attendanceList.csv';
    $handle = fopen('php://temp', 'r+');

    foreach ($csvContent as $row) {
        fputcsv($handle, $row);
    }
    rewind($handle);
    $csv = stream_get_contents($handle);
    $sjisData = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');

    fclose($handle);

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename .'"',
    ];

    return Response::make($sjisData, 200, $headers);
    }
}

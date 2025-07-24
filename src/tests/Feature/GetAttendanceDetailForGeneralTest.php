<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Tests\TestCase;

class GetAttendanceDetailForGeneralTest extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = DatabaseSeeder::class;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_GetNameDetailDisplayCheck()
    {
        // 10-1. 勤怠詳細画面の「名前」がログインユーザーの氏名になっている　ことを検証

        $user_id = '4';  // シーダー生成済のユーザー

        // 1. 勤怠情報が登録されたユーザーにログインする

        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        $now = Carbon::now();
        $this_month = $now->isoFormat('YYYY-MM');

        // 2. 勤怠詳細ページを開く
        
          // ログインユーザーの勤怠データＩＤを取得する
        $attendance = Attendance::with('user')->where('user_id', $user_id)->where('date', 'like', $this_month . '%')->first();
        $attendance_id = $attendance->id;
        $attendance_name = $attendance->user->name;

          // 勤怠詳細ページを開く
        $response = $this->get(route('attendance.detail', ['id' => $attendance_id]));
        $response->assertStatus(200);
        $response->assertViewIs('attendance-detail');


        // 3. 名前欄を確認する

        $responseContent = $response->getContent();
        $pattern = '/名前.*?\n.*?>.*?>/i';
        preg_match($pattern, $responseContent, $matches1);

        $pattern = '/名前.*?\n.*?>/i';
        preg_match($pattern, $responseContent, $matches2);

          // 名前欄に表示されている名前を、diffを利用して取得。
        $diff1 = collect(explode('>', $matches1[0]));
        $diff2 = collect(explode('>', $matches2[0]));
        $diff = $diff1->diff($diff2);

          // 勤怠詳細ページの名前欄を変数に格納。
        $viewDisplayName = explode('<',$diff[2])[0];
          // 名前欄の表示が、データベースから直接取得した名称と一致することを検証。
        $this->assertEquals($attendance_name, $viewDisplayName);
    }

    public function test_GetDateDetailDisplayCheck()
    {
        // 10-2. 勤怠詳細画面の「日付」が選択した日付になっている ことを検証

        $user_id = '4';  // シーダー生成済のユーザー

        // 1. 勤怠情報が登録されたユーザーにログインする

        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();
        $now = Carbon::now();
        $this_month = $now->isoFormat('YYYY-MM');

        // 2. 勤怠詳細ページを開く
          // ログインユーザーの勤怠データＩＤを取得する
        $attendance = Attendance::where('user_id', $user_id)->where('date', 'like', $this_month . '%')->first();
        $attendance_id = $attendance->id;
        $attendance_date = $attendance->date;
          // 勤怠データＩＤを渡して、勤怠詳細ページを開く。
        $response = $this->get(route('attendance.detail', ['id' => $attendance_id]));
        $response->assertStatus(200);
        $response->assertViewIs('attendance-detail');


        // 3. 日付欄を確認する

        $responseContent = $response->getContent();
        $pattern = '/日付.*?\n.*?\n.*?\n/i';
        preg_match($pattern, $responseContent, $matches1);

        $pattern = '/日付.*?\n.*?\n/i';
        preg_match($pattern, $responseContent, $matches2);

          // 日付欄の表示を、diffを利用して取得。
        $diff1 = collect(explode("\n", $matches1[0]));
        $diff2 = collect(explode("\n", $matches2[0]));
        $diff = $diff1->diff($diff2);

        $viewDisplayYear = trim($diff[2]);


        $pattern = '/日付.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n/i';
        preg_match($pattern, $responseContent, $matches3);

        $pattern = '/日付.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n/i';
        preg_match($pattern, $responseContent, $matches4);

        $diff3 = collect(explode("\n", $matches3[0]));
        $diff4 = collect(explode("\n", $matches4[0]));
        $diff = $diff3->diff($diff4);

        $viewDisplayDate = trim($diff[6]);

          // 勤怠データＩＤのデータベース値の日付（年、および月日）とview表示の一致を検証。
            // データベースの日付の形式をview表示に合わせる
        $expect_year = Carbon::parse($attendance_date)->isoFormat('YYYY年');
        $expect_date = Carbon::parse($attendance_date)->isoFormat('M月D日');

        $this->assertEquals($expect_year, $viewDisplayYear);
        $this->assertEquals($expect_date, $viewDisplayDate);

    }

    public function test_GetAttendanceTimeDetailDisplayCheck()
    {
        // 10-3. 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している ことを検証

        $user_id = '4';  // シーダー生成済のユーザー

        // 1. 勤怠情報が登録されたユーザーにログインする

        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();
        $now = Carbon::now();
        $this_month = $now->isoFormat('YYYY-MM');

        // 2. 勤怠詳細ページを開く
          // ログインユーザーの勤怠データＩＤを取得する
        $attendance = Attendance::where('user_id', $user_id)->where('date', 'like', $this_month . '%')->first();
        $attendance_id = $attendance->id;
        $database_attendanced_at = $attendance->attendanced_at; // 検証用に取得
        $database_leaved_at = $attendance->leaved_at;           // 検証用に取得
          // 勤怠データＩＤを渡して、勤怠詳細ページを開く。
        $response = $this->get(route('attendance.detail', ['id' => $attendance_id]));
        $response->assertStatus(200);
        $response->assertViewIs('attendance-detail');

        // 3. 出勤・退勤欄を確認する

        $responseContent = $response->getContent();
        $pattern = '/出勤・退勤.*?\n.*?\n.*?\n.*?\n/i';
        preg_match($pattern, $responseContent, $matches1);

        $pattern = '/出勤・退勤.*?\n.*?\n.*?\n/i';
        preg_match($pattern, $responseContent, $matches2);

          // 出勤・退勤欄の出勤時刻表示を、diffを利用して取得。
        $diff1 = collect(explode('"', $matches1[0]));
        $diff2 = collect(explode('"', $matches2[0]));
        $diff = $diff1->diff($diff2);

        $viewDisplayAttendancedAt = $diff[7]; // 出勤時刻のview表示を取得

        $pattern = '/出勤・退勤.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n/i';
        preg_match($pattern, $responseContent, $matches3);

        $pattern = '/出勤・退勤.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n/i';
        preg_match($pattern, $responseContent, $matches4);

        $diff3 = collect(explode('"', $matches3[0]));
        $diff4 = collect(explode('"', $matches4[0]));
        $diff = $diff3->diff($diff4);

        $viewDisplayLeavedAt = $diff[17];

          // 勤怠データＩＤのデータベース値とview表示の一致を検証。
            // データベースからの取得値のフォーマットを整える
        $database_attendanced_at_form = Carbon::parse($database_attendanced_at)->format('H:i');
        $database_leaved_at_form = Carbon::parse($database_leaved_at)->format('H:i');

        $this->assertEquals($database_attendanced_at_form, $viewDisplayAttendancedAt);
        $this->assertEquals($database_leaved_at_form, $viewDisplayLeavedAt);

    }

    public function test_GetBreakTimeDetailDisplayCheck()
    {
        // 10-4. 「休憩」にて記されている時間がログインユーザーの打刻と一致している ことを検証

        $user_id = '4';  // シーダー生成済のユーザー

        // 1. 勤怠情報が登録されたユーザーにログインする

        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();
        $now = Carbon::now();
        $this_month = $now->isoFormat('YYYY-MM');

        // 2. 勤怠詳細ページを開く
          // ログインユーザーの勤怠データＩＤを取得する
        $attendance = Attendance::where('user_id', $user_id)->where('date', 'like', $this_month . '%')->first();
        $attendance_id = $attendance->id;

        $database_break_times = BreakTime::where('attendance_id', $attendance_id)->get();  // 検証用に取得。

          // 勤怠データＩＤを渡して、勤怠詳細ページを開く。
        $response = $this->get(route('attendance.detail', ['id' => $attendance_id]));
        $response->assertStatus(200);
        $response->assertViewIs('attendance-detail');

        // 3. 休憩欄を確認する

        $responseContent = $response->getContent();

          // view表示の休憩開始時刻の取得
        $pattern = '/>休憩.*?\n.*?\n.*?\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches1);

        $pattern = '/>休憩.*?\n.*?\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches2);

            // 休憩欄の開始時刻の表示を、diffを利用して取得。
        $diff1 = [];
        foreach ($matches1[0] as $match) {
            $diff1 = array_merge($diff1, explode('"', $match));
        }

        $diff2 = [];
        foreach ($matches2[0] as $match) {
            $diff2 = array_merge($diff2, explode('"', $match));
        }
        $diffs = collect($diff1)->diff(collect($diff2));

        $viewDisplayStartedAt = [];
        $count = max(array_keys($diffs->toArray()));

            // 休憩時間は複数あり得ることから、view表示の休憩開始時間を配列で取得。
        for($i=1;$i<=$count;$i++) {
            if ( $i % 9 == 0) {
                $viewDisplayStartedAt[] = $diffs[$i-2];
            }
        }
        
          // 同様にview表示の休憩終了時刻の取得
        $pattern = '/>休憩.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches3);

        $pattern = '/>休憩.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches4);

            // 休憩欄の開始時刻の表示を、diffを利用して取得。
        $diff3 = [];
        foreach ($matches3[0] as $match) {
            $diff3 = array_merge($diff3, explode('"', $match));
        }

        $diff4 = [];
        foreach ($matches4[0] as $match) {
            $diff4 = array_merge($diff4, explode('"', $match));
        }
        $diffs = collect($diff3)->diff(collect($diff4));

        $viewDisplayEndedAt = [];
        $count = max(array_keys($diffs->toArray()));

            // 開始時間同様にview表示の休憩終了時間を配列で取得。
        for($i=1;$i<=$count;$i++) {
            if ( $i % 19 == 0) {
                $viewDisplayEndedAt[] = $diffs[$i-2];
            }
        }


          // 検証のため、break_timesテーブルの値を取得する

        $database_started_at = [];
        $database_ended_at = [];

        foreach($database_break_times as $break_time) {
            $database_started_at[] = Carbon::parse($break_time['started_at'])->format('H:i');
            $database_ended_at[] = Carbon::parse($break_time['ended_at'])->format('H:i');
        }

          // 休憩開始時刻、休憩終了時刻で、それぞれデータベース値とview表示値が一致することを検証。
        $this->assertEquals($database_started_at, $viewDisplayStartedAt);
        $this->assertEquals($database_ended_at, $viewDisplayEndedAt);

    }
}

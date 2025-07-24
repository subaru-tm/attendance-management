<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Application;
use Carbon\Carbon;
use Tests\TestCase;

class GetAttendanceListForAdminTest extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = DatabaseSeeder::class;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_GetAllStaffAttendanceCheck()
    {
        // 12-1. その日になされた全ユーザーの勤怠情報が正確に確認できる ことを検証
        $user_id = '3';  // シーダー生成済の管理者

        // 1. 管理者ユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        // 2. 勤怠一覧画面を開く
        $response = $this->get(route('admin.list'));
        $response->assertStatus(200);
          // 画面に表示されている内容（名前、出勤、退勤、休憩、合計の５項目）を取得。
        $responseContent = $response->getContent();
        $pattern = '/<td class="list-table__item.*?\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches);

          // attendancesテーブルにある当日データを全て取得。
        $now = Carbon::now();
        $today = $now->isoFormat('YYYY-MM-DD');
        $database_attendances = Attendance::with('user')->where('date', $today)->get();

          // attendancesテーブルにある当日データが全てviewに表示されているかを検証。
        $i = 1;
        foreach ($database_attendances as $attendance) {
              // テーブルレコードの項目値をview表示対象のみview表示形式に合わせて変数に格納。
            $attendanced_at = Carbon::parse($attendance->attendanced_at)->format('H:i'); // 出勤時刻がnullはあり得ない
            if ($attendance->leaved_at == null) {
                    $leaved_at = '</td>';   // nullの場合、view表示に合わせた値に変換。
            } else {
                $leaved_at = Carbon::parse($attendance->leaved_at)->format('H:i');
            }
            if ($attendance->total_break_time == null) {
                $total_break_time = '</td>'; // nullの場合、view表示に合わせた値に変換。
            } else {
                $total_break_time = Carbon::parse($attendance->total_break_time)->format('H:i');
            }
            if ($attendance->total_attendance_time == null) {
                $total_attendance_time = '</td>';
            } else {
                $total_attendance_time = Carbon::parse($attendance->total_attendance_time)->format('H:i');
            }

            $this->assertEquals($attendance->user->name, trim(explode("\n", $matches[0][$i-1])[1])); // 名前を検証
            $this->assertEquals($attendanced_at, trim(explode("\n", $matches[0][$i])[1])); // 出勤時刻を検証
            $this->assertEquals($leaved_at, trim(explode("\n", $matches[0][$i+1])[1]));    // 退勤時刻を検証
            $this->assertEquals($total_break_time, trim(explode("\n", $matches[0][$i+2])[1]));      // 休憩時間を検証
            $this->assertEquals($total_attendance_time, trim(explode("\n", $matches[0][$i+3])[1])); // 合計勤務時間を検証

            $i=$i+6; // 次のレコード用に$iを加算。viewでは6項目（詳細ボタン含む）が1レコードとしてセットのため6加算で整合が取れる。
        }
    }

    public function test_GetDateAtNowCheck()
    {
        // 12-2. 遷移した際に現在の日付が表示される ことを検証
        $user_id = '3';  // シーダー生成済の管理者

        // 1. 管理者ユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        // 2. 勤怠一覧画面を開く
        $response = $this->get(route('admin.list'));
        $response->assertStatus(200);
          // 画面に表示されている内容を取得。
        $responseContent = $response->getContent();
        $pattern = '/<h1>.*?日/i';
        preg_match_all($pattern, $responseContent, $matches_h1);

        $response_tittle_date = explode(">", $matches_h1[0][0])[1];
          // "YYYY年MM月DD日の勤務"の表示箇所で年月日部分のみ取得。

        $pattern = '/<div class="this_day__display-format">.*?div>/i';
        preg_match_all($pattern, $responseContent, $matches_display);

        $response_date_display = explode("<", explode(">", $matches_display[0][0])[1])[0];
          // "YYYY/MM/DD"の表示箇所を取得。tagの<>を区切り文字として切り分けて取り除いている

        // 現在時刻の日付と一致することを検証。
        $now = Carbon::now();
        $this->assertEquals($now->isoFormat('YYYY年M月D日'), $response_tittle_date);
        $this->assertEquals($now->isoFormat('YYYY/MM/DD'), $response_date_display);
    }

    public function test_GetDateAtYesterdayCheck()
    {
        // 12-3. 「前日」を押下した時に前の日の勤怠情報が表示される ことを検証
        $user_id = '3';  // シーダー生成済の管理者

        // 1. 管理者ユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        // 2. 勤怠一覧画面を開く
        $response = $this->get(route('admin.list'));
        $response->assertStatus(200);

        $responseContent = $response->getContent();
        $pattern = '/<button type="submit".*?>/i';
        preg_match_all($pattern, $responseContent, $matches_day_link);

        $yesterday_link_value = explode('"', $matches_day_link[0][0])[5];

        // 3. 「前日」ボタンを押す
          // 上記で取得した「前日」ボタンリンクの内容をリクエストとして渡してボタン押下を再現。
        $response = $this->get(route('admin.list', ['today' => $yesterday_link_value]));
        $response->assertStatus(200);

        $responseContent = $response->getContent();
        $pattern = '/<h1>.*?日/i';
        preg_match_all($pattern, $responseContent, $matches_h1);

        $response_tittle_date = explode(">", $matches_h1[0][0])[1];
          // "YYYY年MM月DD日の勤務"の表示箇所で年月日部分のみ取得。

        $pattern = '/<div class="this_day__display-format">.*?div>/i';
        preg_match_all($pattern, $responseContent, $matches_display);

        $response_date_display = explode("<", explode(">", $matches_display[0][0])[1])[0];
          // "YYYY/MM/DD"の表示箇所を取得。tagの<>を区切り文字として切り分けて取り除いている

        // 「前日」の日付と一致することを検証。
        $this_day = Carbon::parse($yesterday_link_value);
        $this->assertEquals($this_day->isoFormat('YYYY年M月D日'), $response_tittle_date);
        $this->assertEquals($this_day->isoFormat('YYYY/MM/DD'), $response_date_display);
    }

    public function test_GetDateAtTomorrowCheck()
    {
        // 12-4. 「翌日」を押下した時に次の日の勤怠情報が表示される ことを検証
        $user_id = '3';  // シーダー生成済の管理者

        // 1. 管理者ユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        // 2. 勤怠一覧画面を開く
        $response = $this->get(route('admin.list'));
        $response->assertStatus(200);

        $responseContent = $response->getContent();
        $pattern = '/<button type="submit".*?>/i';
        preg_match_all($pattern, $responseContent, $matches_day_link);

        $tomorrow_link_value = explode('"', $matches_day_link[0][1])[5];

        // 3. 「翌日」ボタンを押す
          // 上記で取得した「翌日」ボタンリンクの内容をリクエストとして渡してボタン押下を再現。
        $response = $this->get(route('admin.list', ['today' => $tomorrow_link_value]));
        $response->assertStatus(200);

        $responseContent = $response->getContent();
        $pattern = '/<h1>.*?日/i';
        preg_match_all($pattern, $responseContent, $matches_h1);

        $response_tittle_date = explode(">", $matches_h1[0][0])[1];
          // "YYYY年MM月DD日の勤務"の表示箇所で年月日部分のみ取得。

        $pattern = '/<div class="this_day__display-format">.*?div>/i';
        preg_match_all($pattern, $responseContent, $matches_display);

        $response_date_display = explode("<", explode(">", $matches_display[0][0])[1])[0];
          // "YYYY/MM/DD"の表示箇所を取得。tagの<>を区切り文字として切り分けて取り除いている

        // 「翌日」の日付と一致することを検証。
        $this_day = Carbon::parse($tomorrow_link_value);
        $this->assertEquals($this_day->isoFormat('YYYY年M月D日'), $response_tittle_date);
        $this->assertEquals($this_day->isoFormat('YYYY/MM/DD'), $response_date_display);
    }
}

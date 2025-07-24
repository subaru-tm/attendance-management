<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Application;
use App\Models\BreakTime;
use App\Models\CorrectBreakTime;
use Carbon\Carbon;
use Tests\TestCase;

class CorrectAttendanceForAdminTest extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = DatabaseSeeder::class;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_allDisplayWaitingApplicationCheck()
    {
        // 15-1. 承認待ちの修正申請が全て表示されている ことを検証。
        $admin_user_id = '3';  // シーダー生成済の管理者

        // 1. 管理者ユーザーにログインする
        $admin_user = User::find($admin_user_id);
        $this->actingAs($admin_user)->assertAuthenticated();

        // 2. 修正申請一覧ページを開き、承認待ちのタブを開く
        $response = $this->get(route('correction.list', ['tab' => 'waiting']));
        $response->assertStatus(200);
        $response->assertViewIs('application-list');

        $responseContent = $response->getContent();
        $pattern = '/<td class="list-table__item">\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches);
        $database_applications = Application::with('attendance.user')->where('approval_status', '0')->get();

        $i = 0;
        $expect_status = '承認待ち';
        foreach ($database_applications as $application) {
            $expect_name = $application->attendance->user->name;
            $expect_date = Carbon::parse($application->attendance->date)->isoFormat('YYYY/MM/DD');
            $expect_remarks = $application->remarks;
            $expect_application_date = Carbon::parse($application->application_date)->isoFormat('YYYY/MM/DD');
            $this->assertEquals($expect_status, trim(explode("\n", $matches[0][$i])[1])); // ステータス表示の検証
            $this->assertEquals($expect_name, trim(explode("\n", $matches[0][$i])[4]));  // 名前の検証
            $this->assertEquals($expect_date, trim(explode("\n", $matches[0][$i])[7]));  // 対象日付の検証
            $this->assertEquals($expect_remarks, trim(explode("\n", $matches[0][$i])[10]));  // 備考の検証
            $this->assertEquals($expect_application_date, trim(explode("\n", $matches[0][$i])[13]));  // 申請日の検証
            $i++;
        }
    }

    public function test_allDisplayApprovedApplicationCheck()
    {
        // 15-2. 承認済みの修正申請が全て表示されている ことを検証。
        $admin_user_id = '3';  // シーダー生成済の管理者

        // 1. 管理者ユーザーにログインする
        $admin_user = User::find($admin_user_id);
        $this->actingAs($admin_user)->assertAuthenticated();

        // 2. 修正申請一覧ページを開き、承認済みのタブを開く
        $response = $this->get(route('correction.list', ['tab' => 'approved']));
        $response->assertStatus(200);
        $response->assertViewIs('application-list');

        $responseContent = $response->getContent();
        $pattern = '/<td class="list-table__item">\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches);
        $database_applications = Application::with('attendance.user')->where('approval_status', '1')->get();

        $i = 0;
        $expect_status = '承認済み';
        foreach ($database_applications as $application) {
            $expect_name = $application->attendance->user->name;
            $expect_date = Carbon::parse($application->attendance->date)->isoFormat('YYYY/MM/DD');
            $expect_remarks = $application->remarks;
            $expect_application_date = Carbon::parse($application->application_date)->isoFormat('YYYY/MM/DD');
            $this->assertEquals($expect_status, trim(explode("\n", $matches[0][$i])[1])); // ステータス表示の検証
            $this->assertEquals($expect_name, trim(explode("\n", $matches[0][$i])[4]));  // 名前の検証
            $this->assertEquals($expect_date, trim(explode("\n", $matches[0][$i])[7]));  // 対象日付の検証
            $this->assertEquals($expect_remarks, trim(explode("\n", $matches[0][$i])[10]));  // 備考の検証
            $this->assertEquals($expect_application_date, trim(explode("\n", $matches[0][$i])[13]));  // 申請日の検証
            $i++;
        }
    }

    public function test_displayDetailCheck()
    {
        // 15-3. 修正申請の詳細内容が正しく表示されている ことを検証
        $admin_user_id = '3';  // シーダー生成済の管理者

        // 1. 管理者ユーザーにログインする
        $admin_user = User::find($admin_user_id);
        $this->actingAs($admin_user)->assertAuthenticated();

          // 表示する修正申請（承認待ちとする）のIDを取得する
        $application = Application::with('attendance.user')->where('approval_status', '0')->first();
        $application_id = $application->id;

        // 2. 修正申請の詳細画面を開く
        $response = $this->get(route('application.detail', [ 'attendance_correct_request' => $application_id ]));
        $response->assertStatus(200);
        $response->assertViewIs('application-detail');

        // レスポンス内容(view表示)のユーザー名、日付、出勤・退勤時刻、休憩時刻をそれぞれDB値との一致を検証。
        $responseContent = $response->getContent();
          // view表示のユーザー名を取得し、データベース値との一致を検証。
        $pattern = '/名前<.*?\n.*?\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches);
        $response_user_name = trim(explode("\n", $matches[0][0])[2]);
        $database_application_name = $application->attendance->user->name;
        $this->assertEquals($database_application_name, $response_user_name);  // ユーザー名の検証

          // view表示の日付を取得し、データベース値との一致を検証。
        $pattern = '/日付<.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches);
        $response_year = trim(explode("\n", $matches[0][0])[2]);
        $response_month_day = trim(explode("\n", $matches[0][0])[6]);

        $database_date = Carbon::parse($application->attendance->date);
        $database_year = $database_date->isoFormat('YYYY年');      // view表示の形式に変換。
        $database_month_day = $database_date->isoFormat('M月D日'); // view表示の形式に変換。

        $this->assertEquals($database_year, $response_year);           // 年の検証
        $this->assertEquals($database_month_day, $response_month_day); // 月日の検証

          // view表示の出勤・退勤を取得し、データベースの値との一致を検証。
        $pattern = '/出勤・退勤<.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches);
        $response_attendanced_at = explode('"', $matches[0][0])[7];
        $response_leaved_at = explode('"', $matches[0][0])[19];

        $database_attendanced_at = Carbon::parse($application->correct_attendanced_at)->format('H:i');
        $database_leaved_at = Carbon::parse($application->correct_leaved_at)->format('H:i');

        $this->assertEquals($database_attendanced_at, $response_attendanced_at);
        $this->assertEquals($database_leaved_at, $response_leaved_at);

          // view表示の休憩時刻を取得し、データベースの値との一致を検証。
        $pattern = '/休憩.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches);

        $response_started_at = explode('"', $matches[0][0])[7];
        $response_ended_at = explode('"', $matches[0][0])[19];
          // 本来は休憩時間が複数回あり得る使用のため、繰り返し取得をすべきだが、テストデータを
          // 休憩１回として絞り込んで生成しているため、テストコードのステップ数短縮化のため１回に集約。

        $database_correct_break_time = CorrectBreakTime::where('application_id', $application_id)->first();
        $database_started_at = Carbon::parse($database_correct_break_time->started_at)->format('H:i');
        $database_ended_at = Carbon::parse($database_correct_break_time->ended_at)->format('H:i');

        $this->assertEquals($database_started_at, $response_started_at);
        $this->assertEquals($database_ended_at, $response_ended_at);
    }

    public function test_approveApplicationCheck()
    {
        // 15-4. 修正申請の承認処理が正しく行われる ことを検証
        $admin_user_id = '3';  // シーダー生成済の管理者

        // 1. 管理者ユーザーにログインする
        $admin_user = User::find($admin_user_id);
        $this->actingAs($admin_user)->assertAuthenticated();

        // 承認する修正申請（承認待ちのステータス）のIDを取得する
        $application = Application::with('attendance.user')->where('approval_status', '0')->first();
        $application_id = $application->id;
        $attendance_id = $application->attendance_id; // attendancesテーブル更新の検証に使用。

        // 2. 修正申請の詳細画面で「承認」ボタンを押す
          // まず修正申請の詳細画面を開く
        $response = $this->get(route('application.detail', [ 'attendance_correct_request' => $application_id ]));
        $response->assertStatus(200);

          // 承認ボタン押下によるactionをview表示から取得。
        $responseContent = $response->getContent();
        $pattern = '/<form action=.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches);
        $approve_button_push_action = explode('"', $matches[0][1])[1];

          // 承認ボタン押下時の処理を実行。
        $response = $this->patch($approve_button_push_action);
        $response->assertStatus(302);

          // applicationsテーブルのapproval_statusが承認済みになっていることを検証。
        $approved_application = Application::find($application_id);
        $approved_application_approval_status = $approved_application->approval_status;
        $expect_approval_status = '1';  // 承認済みのステータス値
        $this->assertEquals($expect_approval_status, $approved_application_approval_status);
        $database_attendance = Attendance::find($attendance_id);

          // 勤怠情報:attendancesテーブル（出勤・退勤の時刻、備考）の更新を検証。
        $this->assertEquals($application->correct_attendanced_at, $database_attendance->attendanced_at);
        $this->assertEquals($application->correct_leaved_at, $database_attendance->leaved_at);
        $this->assertEquals($application->remarks, $database_attendance->remarks);

          // 勤怠情報:break_timesテーブルが、correct_break_timeの値で更新されたことを検証。
        $correct_break_time = CorrectBreakTime::where('application_id', $application_id)->first();
        $break_time = BreakTime::where('attendance_id', $attendance_id)->first();
            // 本来休憩は複数回あり得るが、テストデータを休憩１回に絞っているため、
            // ここでも休憩１回を前提として、テストコードの縮小を図る

        $this->assertEquals($correct_break_time->started_at, $break_time->started_at);
        $this->assertEquals($correct_break_time->ended_at, $break_time->ended_at);
    }
}
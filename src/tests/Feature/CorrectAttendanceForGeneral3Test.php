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

class CorrectAttendanceForGeneral3Test extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = DatabaseSeeder::class;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_allApprovedDisplayCheck()
    {
        // 11-7. 「承認済み」に管理者が承認した修正申請が全て表示されている ことを検証
        $user_id = '4';  // シーダー生成済の一般ユーザー

        // 1. 勤怠情報が登録されたユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();
        $test1_date = '2025-07-07';
        $test2_date = '2025-07-08';
        $test3_date = '2025-07-09';

        // 2. 勤怠詳細を修正し保存処理をする
          // 全て表示されることの検証のため、３つの修正申請を保存する
          // ログインユーザーの勤怠データＩＤを取得する
        $attendance = Attendance::with('user')->where('user_id', $user_id)->where('date', $test1_date)->first();
        $attendance_id1 = $attendance->id;
        $attendance_user_name1 = $attendance->user->name;
          // 修正内容（その１）
        $attendanced_at = "08:00";
        $leaved_at = "17:00";
        $started_at[] = "12:00";
        $started_at[] = "14:00";
        $ended_at[] = "12:45";
        $ended_at[] = "14:45";
        $remarks1 = "テスト４のため";
          // 保存する（その１）
        $response = $this->post(route('application', [
            'id' => $attendance_id1,
            'attendanced_at' => $attendanced_at,
            'leaved_at' => $leaved_at,
            'started_at' => $started_at,
            'ended_at' => $ended_at,
            'remarks' => $remarks1,
        ]));
        $response->assertStatus(302); // リダイレクトステータス

          // ２つ目
        $attendance = Attendance::with('user')->where('user_id', $user_id)->where('date', $test2_date)->first();
        $attendance_id2 = $attendance->id;
        $attendance_user_name2 = $attendance->user->name;
          // 修正内容（その２）
        $attendanced_at = "11:00";
        $leaved_at = "20:00";
        $started_at[] = "13:30";
        $started_at[] = "18:00";
        $ended_at[] = "14:00";
        $ended_at[] = "18:30";
        $remarks2 = "テスト５のため";
          // 保存する（その２）
        $response = $this->post(route('application', [
            'id' => $attendance_id2,
            'attendanced_at' => $attendanced_at,
            'leaved_at' => $leaved_at,
            'started_at' => $started_at,
            'ended_at' => $ended_at,
            'remarks' => $remarks2,
        ]));
        $response->assertStatus(302); // リダイレクトステータス

          // ３つ目
        $attendance = Attendance::with('user')->where('user_id', $user_id)->where('date', $test3_date)->first();
        $attendance_id3 = $attendance->id;
        $attendance_user_name3 = $attendance->user->name;
          // 修正内容（その３）
        $attendanced_at = "10:00";
        $leaved_at = "19:00";
        $started_at[] = "13:00";
        $ended_at[] = "14:00";
        $remarks3 = "テスト６のため";
          // 保存する（その３）
        $response = $this->post(route('application', [
            'id' => $attendance_id3,
            'attendanced_at' => $attendanced_at,
            'leaved_at' => $leaved_at,
            'started_at' => $started_at,
            'ended_at' => $ended_at,
            'remarks' => $remarks3,
        ]));
        $response->assertStatus(302); // リダイレクトステータス

        // 3. 申請一覧画面を開く

          // 「承認済み」として{tab}にapprovedを渡す
        $response = $this->get(route('correction.list', ['tab' => 'approved']));
        $response->assertStatus(200);
        $response->assertViewIs('application-list');
          // まだ承認されていないので、承認済には何も表示されない
        
        // 4. 管理者が承認した修正申請が全て表示されていることを確認
        $application1 = Application::where('attendance_id', $attendance_id1)->first();
        $application_id1 = $application1->id;
        $application2 = Application::where('attendance_id', $attendance_id2)->first();
        $application_id2 = $application2->id;
        $application3 = Application::where('attendance_id', $attendance_id3)->first();
        $application_id3 = $application3->id;

          // 今回の３つの申請を全て承認するため、管理者でログインする
        $admin_user_id = '3';  // 管理者
        $admin_user = User::find($admin_user_id);
        $this->actingAs($admin_user)->assertAuthenticated();
          // ３つの申請を全て承認する
        $response = $this->patch(route('application.update', ['attendance_correct_request' => $application_id1]));
        $response->assertStatus(302);
        $response = $this->patch(route('application.update', ['attendance_correct_request' => $application_id2]));
        $response->assertStatus(302);
        $response = $this->patch(route('application.update', ['attendance_correct_request' => $application_id3]));
        $response->assertStatus(302);

          // 改めて一般ユーザーにてログインして申請一覧画面を開く
        $this->actingAs($user)->assertAuthenticated();
        $response = $this->get(route('correction.list', ['tab' => 'approved']));
        $response->assertStatus(200);
        $response->assertViewIs('application-list');

        $response->assertSee($attendance_user_name1); // 今回申請したユーザーの名前が表示されている
        $response->assertSee($attendance_user_name2);
        $response->assertSee($attendance_user_name3);
        $response->assertSee(Carbon::parse($test1_date)->isoFormat('YYYY/MM/DD')); // 今回申請した申請日付が表示されている
        $response->assertSee(Carbon::parse($test2_date)->isoFormat('YYYY/MM/DD'));
        $response->assertSee(Carbon::parse($test3_date)->isoFormat('YYYY/MM/DD'));
        $response->assertSee($remarks1); // 今回申請した備考欄の記述が表示されている
        $response->assertSee($remarks2);
        $response->assertSee($remarks3);
    }

    public function test_detailButtonCheck()
    {
        // 11-8. 各申請の「詳細」を押下すると申請詳細画面に遷移する ことを検証
        $user_id = '4';  // シーダー生成済の一般ユーザー

        // 1. 勤怠情報が登録されたユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

          // 修正対象の勤怠データ
        $attendance_date = '2025-07-10';
        $attendance = Attendance::with('user')->where('user_id', $user_id)->where('date', $attendance_date)->first();
        $attendance_id = $attendance->id;
        $attendance_user_name = $attendance->user->name;

        // 2. 勤怠詳細を修正し保存処理をする
        $attendanced_at = "11:00";
        $leaved_at = "20:00";
        $started_at[] = "15:00";
        $started_at[] = "18:30";
        $ended_at[] = "15:45";
        $ended_at[] = "18:45";
        $remarks = "詳細ボタン確認のため";

          // 保存する
        $response = $this->post(route('application', [
            'id' => $attendance_id,
            'attendanced_at' => $attendanced_at,
            'leaved_at' => $leaved_at,
            'started_at' => $started_at,
            'ended_at' => $ended_at,
            'remarks' => $remarks,
        ]));
        $response->assertStatus(302);

        // 3. 申請一覧画面を開く
        $response = $this->get(route('correction.list', ['tab' => 'waiting']));
        $response->assertStatus(200);
        $response->assertViewIs('application-list');

        // 4. 「詳細」ボタンを押す
          // 申請一覧のviewで保持されている「詳細」ボタンのタグ部分を取得。
        $responseContent = $response->getContent();
        $pattern = '/<a href=".*?詳細.*?>/i';
        preg_match_all($pattern, $responseContent, $matches);

        $matches_explode = explode('"', $matches[0][0]);
        $detail_button_link = $matches_explode[1]; // "/attendance/{id}"の箇所を取得。idは具体的なIDになっている
          // 「詳細」ボタンを押したのと同じアクションを実行・・・詳細画面を開く
        $response = $this->get($detail_button_link);
        $response->assertStatus(200);
        $response->assertViewIs("attendance-detail"); // 一般ユーザーの場合は勤怠詳細画面と同じ

        $response->assertSee($attendance_user_name);
        $expect = '承認待ちのため修正はできません。'; // 申請中のため詳細画面では当メッセージが表示される
        $response->assertSee($expect);
          // 他の修正項目は承認されるまで反映されないため検証対象外とする
    }
}

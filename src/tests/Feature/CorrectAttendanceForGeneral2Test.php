<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class CorrectAttendanceForGeneral2Test extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = DatabaseSeeder::class;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_allApplicationDisplayCheck()
    {
        // 11-6. 「承認待ち」にログインユーザーが行った申請が全て表示されていること を検証
        $user_id = '4';  // シーダー生成済の一般ユーザー

        // 1. 勤怠情報が登録されたユーザーにログインする

        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();
        $test1_date = '2025-07-02';
        $test2_date = '2025-07-03';
        $test3_date = '2025-07-04';

        // 2. 勤怠詳細を修正し保存処理をする
          // 全て表示されることの検証のため、３つの修正申請を保存する
          // ログインユーザーの勤怠データＩＤを取得する
        $attendance = Attendance::with('user')->where('user_id', $user_id)->where('date', $test1_date)->first();
        $attendance_id = $attendance->id;
        $attendance_user_name1 = $attendance->user->name;

          // 修正内容（その１）
        $attendanced_at = "08:00";
        $leaved_at = "17:00";
        $started_at[] = "12:00";
        $started_at[] = "14:00";
        $ended_at[] = "12:45";
        $ended_at[] = "14:45";
        $remarks1 = "テスト１のため";

          // 保存する（その１）
        $response = $this->post(route('application', [
            'id' => $attendance_id,
            'attendanced_at' => $attendanced_at,
            'leaved_at' => $leaved_at,
            'started_at' => $started_at,
            'ended_at' => $ended_at,
            'remarks' => $remarks1,
        ]));
        $response->assertStatus(302); // リダイレクトステータス

          // ２つ目
        $attendance = Attendance::with('user')->where('user_id', $user_id)->where('date', $test2_date)->first();
        $attendance_id = $attendance->id;
        $attendance_user_name2 = $attendance->user->name;

          // 修正内容（その２）
        $attendanced_at = "11:00";
        $leaved_at = "20:00";
        $started_at[] = "13:30";
        $started_at[] = "18:00";
        $ended_at[] = "14:00";
        $ended_at[] = "18:30";
        $remarks2 = "テスト２のため";

          // 保存する（その２）
        $response = $this->post(route('application', [
            'id' => $attendance_id,
            'attendanced_at' => $attendanced_at,
            'leaved_at' => $leaved_at,
            'started_at' => $started_at,
            'ended_at' => $ended_at,
            'remarks' => $remarks2,
        ]));
        $response->assertStatus(302); // リダイレクトステータス

          // ３つ目
        $attendance = Attendance::with('user')->where('user_id', $user_id)->where('date', $test3_date)->first();
        $attendance_id = $attendance->id;
        $attendance_user_name3 = $attendance->user->name;

          // 修正内容（その３）
        $attendanced_at = "10:00";
        $leaved_at = "19:00";
        $started_at[] = "13:00";
        $ended_at[] = "14:00";
        $remarks3 = "テスト３のため";

          // 保存する（その３）
        $response = $this->post(route('application', [
            'id' => $attendance_id,
            'attendanced_at' => $attendanced_at,
            'leaved_at' => $leaved_at,
            'started_at' => $started_at,
            'ended_at' => $ended_at,
            'remarks' => $remarks3,
        ]));
        $response->assertStatus(302); // リダイレクトステータス

        // 3. 申請一覧画面を確認する
          // 「承認待ち」として{tab}にwaitingを渡す
        $response = $this->get(route('correction.list', ['tab' => 'waiting']));
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

}

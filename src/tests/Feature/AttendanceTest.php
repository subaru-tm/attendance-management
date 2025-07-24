<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = DatabaseSeeder::class;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_AttendanceButtonCheck()
    {
        // 6-1. 出勤ボタンが正しく機能することを検証。

        $user_id = '5';  // シーダー生成済（勤務外の状態）のユーザー

        // 1. ステータスが勤務外のユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();


        // 2. 画面に「出勤」ボタンが表示されていることを確認する
        $response = $this->get(route('attendance'));
        $response->assertStatus(200);

        $expect = '勤務外';
        $response->assertSee($expect); // 「勤務外」のステータスであることを念のため確認。

        $expect = '出勤';
        $response->assertSee($expect); // 「出勤」ボタンの表示を検証。

        // 3. 出勤の処理を行う

        $response = $this->post(route('attendance.store'), ['update_status' => '1']);
        $response->assertStatus(302);  // リダイレクトステータスになる
        $response->assertRedirect('/attendance');

          // 「出勤中」ステータス検証のためにview表示を取得。
        $expect = '出勤中';
        $response = $this->get(route('attendance'));  
        $response->assertStatus(200);
        $response->assertSee($expect); // 「出勤中」ステータスを検証。

    }

    public function test_AttendanceButtonOnlyOneByDayCheck()
    {
        // 6-2. 出勤は一日一回のみできることを検証。

        $user_id = '3';  // シーダー生成済（勤務済の状態）のユーザー

        // 1. ステータスが退勤済のユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();


        // 2. 「出勤」ボタンが表示されてないことを確認する
        $response = $this->get(route('attendance'));
        $response->assertStatus(200);

        $expect = '退勤済';
        $response->assertSee($expect); // 「退勤済」のステータスであることを念のため確認。

        $expect = '出勤';
        $response->assertDontSee($expect); // 「出勤」ボタンが表示されないことを検証。

    }

    public function test_AttendancedTimeListDisplayCheck()
    {
        // 6-3. 出勤時刻が勤怠一覧画面で確認できることを検証する
        $user_id = '5';  // シーダー生成済（勤務外の状態）のユーザー

        // 1. ステータスが勤務外のユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        $response = $this->get(route('attendance'));
        $response->assertStatus(200);

        $expect = '勤務外';
        $response->assertSee($expect); // 「勤務外」のステータスであることを念のため確認。

        $expect = '出勤';
        $response->assertSee($expect); // 「出勤」ボタンの表示を検証。

        // 2. 出勤の処理を行う

        $response = $this->post(route('attendance.store'), ['update_status' => '1']);
        $response->assertStatus(302);  // リダイレクトステータスになる
        $response->assertRedirect('/attendance');

        $now = Carbon::now();  // 検証のために現在時刻を取得。
        $today = $now->isoFormat('YYYY-MM-DD');


        // 3. 勤怠一覧画面から出勤の日付を確認する
        $response = $this->get(route('attendance.list'), ['id' => $user_id]);
        $response->assertStatus(200);

        $responseData = (array)$response->original->getData();
        $attendanceLists = $responseData['attendanceLists'];

          // レスポンスデータの中から、日付が当日のレコードの勤務開始時刻を取得。
        foreach ($attendanceLists as $attendance) {
            if ( $attendance['date'] == $today ) {
                $attendanced_at = $attendance['attendanced_at'];
                break;
            }
        }

          // 取得した勤務開始時刻と、先ほど取得した時刻 $now との一致を検証。
        $this->assertEquals($now, $attendanced_at);

    }
}

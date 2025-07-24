<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class LeaveTest extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = DatabaseSeeder::class;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_LeaveButtonCheck()
    {
        // 8-1. 退勤ボタンが正しく機能することを検証。

        $user_id = '1';  // シーダー生成済（勤務中の状態）のユーザー

        // 1. ステータスが勤務中のユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();



        // 2. 画面に「退勤」ボタンが表示されていることを確認する
        $response = $this->get(route('attendance'));
        $response->assertStatus(200);

        $expect = '出勤中';
        $response->assertSee($expect);      // 「出勤中」のステータスであることを念のため確認。

        $expect = '退勤';
        $response->assertSee($expect); // 「退勤」ボタンの表示を検証。

        // 3. 退勤の処理を行う

        $response = $this->patch(route('attendance.update'), ['update_status' => '3']);
        $response->assertStatus(302);  // リダイレクトステータスになる
        $response->assertRedirect('/attendance');

          // 「退勤済」ステータス検証のためにview表示を取得。
        $expect = '退勤済';
        $response = $this->get(route('attendance'));  
        $response->assertStatus(200);
        $response->assertSee($expect); // 「退勤済」ステータスを検証。
    }

    public function test_LeavedTimeListDisplayCheck()
    {
        // 8-2. 退勤時刻が勤怠一覧画面で確認できることを検証する
        $user_id = '5';  // シーダー生成済（勤務外の状態）のユーザー

        // 1. ステータスが勤務外のユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        $response = $this->get(route('attendance'));
        $response->assertStatus(200);

        $expect = '勤務外';
        $response->assertSee($expect); // 「勤務外」のステータスであることを念のため確認。

        // 2. 出勤と退勤の処理を行う
          // 出勤処理
        $response = $this->post(route('attendance.store'), ['update_status' => '1']);
        $response->assertStatus(302);  // リダイレクトステータスになる
        $response->assertRedirect('/attendance');

          // 退勤処理
        $response = $this->patch(route('attendance.update'), ['update_status' => '3']);
        $response->assertStatus(302);  // リダイレクトステータスになる
        $response->assertRedirect('/attendance');

          // 検証用に現在時刻、現在日付を取得。
        $now = Carbon::now();
        $today = $now->isoFormat('YYYY-MM-DD');


        // 3. 勤怠一覧画面から退勤の日付を確認する
        $response = $this->get(route('attendance.list'), ['id' => $user_id]);
        $response->assertStatus(200);

        $responseData = (array)$response->original->getData();
        $attendanceLists = $responseData['attendanceLists'];

          // レスポンスデータの中から、日付が当日のレコードの退勤時刻を取得。
        foreach ($attendanceLists as $attendance) {
            if ( $attendance['date'] == $today ) {
                $leaved_at = $attendance['leaved_at'];
                break;
            }
        }

          // 取得した退勤時刻と、先に取得した時刻 $nowとの一致を検証。
        $this->assertEquals($now, $leaved_at);

    }


}
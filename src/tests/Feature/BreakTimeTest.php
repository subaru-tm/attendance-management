<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class BreakTimeTest extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = DatabaseSeeder::class;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_BreakTimeButtonCheck()
    {
        // 7-1. 休憩ボタンが正しく機能することを検証。

        $user_id = '1';  // シーダー生成済（出勤中の状態）のユーザー

        // 1. ステータスが出勤中のユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();


        // 2. 画面に「休憩入」ボタンが表示されていることを確認する
        $response = $this->get(route('attendance'));
        $response->assertStatus(200);

        $expect = '出勤中';
        $response->assertSee($expect); // 「出勤中」のステータスであることを念のため確認。

        $expect = '休憩入';
        $response->assertSee($expect); // 「休憩入」ボタンの表示を検証。

        // 3. 休憩の処理を行う

        $response = $this->patch(route('attendance.update'), ['update_status' => '2']);
        $response->assertStatus(302);  // リダイレクトステータスになる
        $response->assertRedirect('/attendance');

          // 「休憩中」ステータス検証のためにview表示を取得。
        $expect = '休憩中';
        $response = $this->get(route('attendance'));  
        $response->assertStatus(200);
        $response->assertSee($expect); // 「休憩中」ステータスを検証。

    }

    public function test_BreakTimeButtonAsManyByDayCheck()
    {
        // 7-2. 休憩は一日に何回でもできることを検証。

        $user_id = '1';  // シーダー生成済（出勤中の状態）のユーザー

        // 1. ステータスが出勤中のユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();


        $response = $this->get(route('attendance'));
        $response->assertStatus(200);
        $expect = '出勤中';
        $response->assertSee($expect); // 「出勤中」のステータスであることを念のため確認。

        // 2. 休憩入と休憩戻の処理を行う
          // 休憩入処理
        $response = $this->patch(route('attendance.update'), ['update_status' => '2']);
        $response->assertStatus(302);  // リダイレクトステータスになる
        $response->assertRedirect('/attendance');
          // 休憩戻処理
        $response = $this->patch(route('attendance.update'), ['update_status' => '1']);
        $response->assertStatus(302);  // リダイレクトステータスになる
        $response->assertRedirect('/attendance');

        // 3. 「休憩入」ボタンが表示されることを確認する
        $expect = '休憩入';
        $response = $this->get(route('attendance'));  
        $response->assertStatus(200);
        $response->assertSee($expect); // 「休憩入」ボタンの表示を検証。

    }

    public function test_BreakTimeBackButtonCheck()
    {
        // 7-3. 休憩ボタンが正しく機能することを検証。

        $user_id = '1';  // シーダー生成済（出勤中の状態）のユーザー

        // 1. ステータスが出勤中のユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        $response = $this->get(route('attendance'));
        $response->assertStatus(200);

        $expect = '出勤中';
        $response->assertSee($expect); // 「出勤中」のステータスであることを念のため確認。

        $expect = '休憩入';
        $response->assertSee($expect); // 「休憩入」ボタンの表示を検証。


        // 2. 休憩入の処理を行う
        $response = $this->patch(route('attendance.update'), ['update_status' => '2']);
        $response->assertStatus(302);  // リダイレクトステータスになる
        $response->assertRedirect('/attendance');

          // ボタンの表示検証のためview表示を取得。
        $expect = '休憩戻';
        $response = $this->get(route('attendance'));  
        $response->assertStatus(200);
        $response->assertSee($expect); // 「休憩戻」ボタンの表示を検証。


        // 3. 休憩戻の処理を行う
        $response = $this->patch(route('attendance.update'), ['update_status' => '1']);
        $response->assertStatus(302);  // リダイレクトステータスになる
        $response->assertRedirect('/attendance');

          // ステータス検証のためにview表示を取得。
        $expect = '出勤中';
        $response = $this->get(route('attendance'));  
        $response->assertStatus(200);
        $response->assertSee($expect); // 「休憩中」ステータスを検証。

    }

    public function test_BreakTimeBackButtonAsManyByDayCheck()
    {
        // 7-4. 休憩戻は一日に何回でもできることを検証。

        $user_id = '1';  // シーダー生成済（出勤中の状態）のユーザー

        // 1. ステータスが出勤中のユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        $response = $this->get(route('attendance'));
        $response->assertStatus(200);
        $expect = '出勤中';
        $response->assertSee($expect); // 「出勤中」のステータスであることを念のため確認。

        // 2. 休憩入と休憩戻の処理を行い、再度休憩入の処理を行う
          // 休憩入処理
        $response = $this->patch(route('attendance.update'), ['update_status' => '2']);
        $response->assertStatus(302);  // リダイレクトステータスになる
        $response->assertRedirect('/attendance');
          // 休憩戻処理
        $response = $this->patch(route('attendance.update'), ['update_status' => '1']);
        $response->assertStatus(302);  // リダイレクトステータスになる
        $response->assertRedirect('/attendance');
          // 休憩入処理
        $response = $this->patch(route('attendance.update'), ['update_status' => '2']);
        $response->assertStatus(302);  // リダイレクトステータスになる
        $response->assertRedirect('/attendance');
  
        // 3. 「休憩戻」ボタンが表示されることを確認する
        $expect = '休憩戻';
        $response = $this->get(route('attendance'));  
        $response->assertStatus(200);
        $response->assertSee($expect); // 「休憩戻」ボタンの表示を検証。

    }

    public function test_BreakTimeListDisplayCheck()
    {
        // 7-5. 休憩時刻が勤怠一覧画面で確認できることを検証する
        $user_id = '1';  // シーダー生成済（出勤中の状態）のユーザー

        // 1. ステータスが勤務中のユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        $response = $this->get(route('attendance'));
        $response->assertStatus(200);
        $expect = '出勤中';
        $response->assertSee($expect);      // 「出勤中」のステータスであることを念のため確認。

        // 2. 休憩入と休憩戻の処理を行う

        $break_started_at = Carbon::now();  // 検証用に休憩開始時刻を取得。
          // 休憩入処理
        $response = $this->patch(route('attendance.update'), ['update_status' => '2']);
        $response->assertStatus(302);       // リダイレクトステータスになる
        $response->assertRedirect('/attendance');

        sleep(15);                          // 休憩時間記録のため、60秒待機。
        $break_ended_at = Carbon::now();    // 検証用に休憩終了時刻を取得。
        // 休憩戻処理
        $response = $this->patch(route('attendance.update'), ['update_status' => '1']);
        $response->assertStatus(302);       // リダイレクトステータスになる
        $response->assertRedirect('/attendance');

        // 勤怠一覧での当日検索のため、本日日付を取得。
        $now = Carbon::now();
        $today = $now->isoFormat('YYYY-MM-DD');

        // 3. 勤怠一覧画面から休憩の日付を確認する
        $response = $this->get(route('attendance.list'), ['id' => $user_id]);
        $response->assertStatus(200);

        $responseData = (array)$response->original->getData();
        $attendanceLists = $responseData['attendanceLists'];

          // レスポンスデータの中から、日付が当日のレコードの休憩時間を取得。
        foreach ($attendanceLists as $attendance) {
            if ( $attendance['date'] == $today ) {
                $total_break_time = $attendance['total_break_time'];
                break;
            }
        }

        // 検証用に取得した休憩開始、休憩終了時刻を引き算して、休憩時間の想定値を算出。
        $expect_diff = $break_started_at->diff($break_ended_at);
        $expect_break_time = Carbon::parse('0:0:0')->add($expect_diff)->format('H:i:s');

        // 休憩時間の想定値と、勤怠一覧画面（レスポンスデータ）の休憩時間を比較検証する。
        $this->assertEquals($expect_break_time, $total_break_time);

          // それぞれの表示形式がここでは "00:00:15"となり、秒単位で休憩時間を記録しているため、
          // 「休憩時間が正確に記録されている」と評価できる。

    }
}

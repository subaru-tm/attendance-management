<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class GetAttendanceListForGeneralTest extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = DatabaseSeeder::class;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_AllDisplayMyAttendanceCheck()
    {
        // 9-1. 自分が行った勤怠情報が全て表示されている ことを検証。

        $user_id = '4';  // シーダー生成済のユーザー

        // 1. 勤怠情報が登録されたユーザーにログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

        // 3. 自分の勤怠情報が全て表示されていることを確認する
        $responseData = (array)$response->original->getData();
        $attendanceLists = $responseData['attendanceLists'];

        $now = Carbon::now();
        $today = $now->isoFormat('YYYY-MM-DD');
        $this_month = $now->isoFormat('YYYY-MM');

          // attendancesテーブルにある自分の勤怠データ（登録したもの）を取得。
        $attendanceDatabase = Attendance::with('user')->where('user_id', $user_id)->where('date', 'like', $this_month . '%')->orderBy('date', 'asc')->get();
        $attendanceOriginals = $attendanceDatabase->toArray();

        // attendancesテーブルにある自分の勤怠データ（登録したもの）が、
        // 当月分が全て勤怠一覧画面に表示されていることを検証する。
        foreach ($attendanceOriginals as $attendance) {
            foreach ($attendanceLists as $attendanceList) {
                // List側では、空白行の日付もあるため、空白行を無視して値がある日付のみ検証する
                if ( $attendance['date'] == $attendanceList['date']) {
                    $this->assertEquals($attendance, $attendanceList);
                    break;
                }else{
                    continue;
                }
            }
        }

    }

    public function test_DisplayMonthAtNowCheck()
    {
        // 9-2. 勤怠一覧画面に遷移した際に現在の月が表示されることを検証

        $user_id = '4';  // シーダー生成済のユーザー

        // 1. ユーザーにログインする

        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

          // 検証用の想定値として現在月を表示形式を整えて取得。
        $now = Carbon::now();
        $this_month_form = $now->isoFormat('YYYY/MM');

          // viewに現在月が表示されていることを検証。
        $response->assertSee($this_month_form);

    }

    public function test_DisplayOneMonthAgoCheck()
    {
        // 9-3. 「前月」を押下した時に表示月の前月情報が表示されることを検証

        $user_id = '4';  // シーダー生成済のユーザー
          // 検証用に前月を取得。
        $now = Carbon::now();
        $one_month_ago = $now->copy()->subMonth(1)->isoFormat('YYYY-MM');
        $one_month_ago_form = $now->copy()->subMonth(1)->isoFormat('YYYY/MM');

        // 1. 勤怠情報が登録されたユーザーにログインする

        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

          // 「前月」リンクにて設定されている前月情報を取得する
        $responseContent = $response->getContent();
            // viewのbuttontタグで、前月リンクのものをclass名で検索する
        $pattern = '/<button class="one_month_ago_link".*?value=.*?>/i';
        preg_match($pattern, $responseContent, $matches1);

        $pattern = '/<button class="one_month_ago_link".*?value=/i';
        preg_match($pattern, $responseContent, $matches2);

            // valueに前月日付が取得されているため、value値の部分をdiffを利用して差分取得。
        $diff1 = collect(explode('"', $matches1[0]));
        $diff2 = collect(explode('"', $matches2[0]));
        $diff = $diff1->diff($diff2);

            // 念のため事前に取得した前月と比較して一致を確認。

        $responseOneMonthAgo = Carbon::parse($diff[7])->isoFormat('YYYY-MM');
        $this->assertEquals($one_month_ago, $responseOneMonthAgo);

        // 3. 「前月」ボタンを押す
          // 上記の「前月」buttonタグに設定されていた前月情報をコントローラに渡すことで、
          // 「前月」ボタン押下と同じ挙動を再現。

        $response = $this->get(route('attendance.list', ['month' => $diff[7]]));
        $response->assertStatus(200);

          // viewに前月が表示されていることを検証。
        $response->assertSee($one_month_ago_form);

          // 勤怠一覧画面に表示されている勤怠リストの値を取得
        $responseData = (array)$response->original->getData();
        $attendanceLists = $responseData['attendanceLists'];

          // attendancesテーブルにある前月の勤怠データ（登録したもの）を取得。
        $attendanceDatabase = Attendance::with('user')->where('user_id', $user_id)->where('date', 'like', $one_month_ago . '%')->orderBy('date', 'asc')->get();
        $attendanceOriginals = $attendanceDatabase->toArray();

          // 前月分が全ての勤怠データが勤怠一覧画面に表示されていることを検証
        foreach ($attendanceOriginals as $attendance) {
            foreach ($attendanceLists as $attendanceList) {
                // List側では、空白行の日付もあるため、空白行を無視して値がある日付のみ検証する
                if ( $attendance['date'] == $attendanceList['date']) {
                    $this->assertEquals($attendance, $attendanceList);
                    break;
                }else{
                    continue;
                }
            }
        }

    }

    public function test_DisplayOneMonthLaterCheck()
    {
        // 9-4. 「翌月」を押下した時に表示月の翌月情報が表示されることを検証

        $user_id = '4';  // シーダー生成済のユーザー
          // 検証用に翌月情報を取得。
        $now = Carbon::now();
        $one_month_later = $now->copy()->addMonth(1)->isoFormat('YYYY-MM');
        $one_month_later_form = $now->copy()->addMonth(1)->isoFormat('YYYY/MM');

        // 1. 勤怠情報が登録されたユーザーにログインする

        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

          // 「翌月」リンクにて設定されている翌月情報を取得する
        $responseContent = $response->getContent();
          // viewのbuttontタグで、翌月リンクのものをclass名で検索する
        $pattern = '/<button class="one_month_later_link".*?value=.*?>/i';
        preg_match($pattern, $responseContent, $matches1);

        $pattern = '/<button class="one_month_later_link".*?value=/i';
        preg_match($pattern, $responseContent, $matches2);

          // valueに翌月日付が取得されているため、value値の部分をdiffを利用して差分取得。
        $diff1 = collect(explode('"', $matches1[0]));
        $diff2 = collect(explode('"', $matches2[0]));
        $diff = $diff1->diff($diff2);

          // 念のため事前に取得した前月と比較して一致を確認。

        $responseOneMonthLater = Carbon::parse($diff[7])->isoFormat('YYYY-MM');
        $this->assertEquals($one_month_later, $responseOneMonthLater);

        // 3. 「翌月」ボタンを押す
          // 上記の「翌月」buttonタグに設定されていた翌月情報をコントローラに渡すことで、
          // 「翌月」ボタン押下と同じ挙動を再現。

        $response = $this->get(route('attendance.list', ['month' => $diff[7]]));
        $response->assertStatus(200);

          // viewに翌月が表示されていることを検証。
        $response->assertSee($one_month_later_form);

          // 翌月の勤怠情報は登録されていないため、エラーハンドリングとして、
          // 勤怠データがない場合に「ご指定の月の勤務データがありません」の表示を検証する
        $expect = 'ご指定の月の勤務データがありません';
        $response->assertSee($expect);

    }

    public function test_GetDetailCheck()
    {
        // 9-5. 「詳細」を押下すると、その日の勤怠詳細画面に遷移する ことを検証

        $user_id = '4';  // シーダー生成済のユーザー

        // 1. 勤怠情報が登録されたユーザーにログインする

        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

        $now = Carbon::now();
        $this_month = $now->isoFormat('YYYY-MM');

        // 3. 「詳細」ボタンを押下する

          // ログインユーザーの勤怠データＩＤを取得する
        $attendance = Attendance::where('user_id', $user_id)->where('date', 'like', $this_month . '%')->first();
        $attendance_id = $attendance->id;
        $attendance_date = $attendance->date;
          // パスパラメータとして勤怠データＩＤを渡すことで、「詳細」ボタン押下を再現。
        $response = $this->get(route('attendance.detail', ['id' => $attendance_id]));
        $response->assertStatus(200);
        $response->assertViewIs('attendance-detail');

          // 勤怠データＩＤのデータベース値の日付（年、および月日）がviewに表示されていることをもって検証とする
        $expect_year = Carbon::parse($attendance_date)->isoFormat('YYYY年');
        $expect_date = Carbon::parse($attendance_date)->isoFormat('M月D日');
        $response->assertSee($expect_year);
        $response->assertSee($expect_date);
    }

}

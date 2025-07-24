<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class GetStaffListForAdmin2Test extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = DatabaseSeeder::class;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_getOneMonthAgoCheck()
    {
        //14-3. 「前月」を押下した時に表示月の前月の情報が表示される ことを検証
            $admin_user_id = '3';  // シーダー生成済の管理者
            $user_id = '4';        // シーダー生成済の一般ユーザー
            $user = User::find($user_id);
            $user_name = $user->name;

            // 1. 管理者ユーザーにログインする
            $admin_user = User::find($admin_user_id);
            $this->actingAs($admin_user)->assertAuthenticated();

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list.byStaff', ['id' => $user_id]));
        $response->assertStatus(200);

          // 「前月」ボタンのリンクをレスポンスから取得する
        $responseContent = $response->getContent();
        $pattern = '/class="one_month_ago_link".*?>/i';
        preg_match_all($pattern, $responseContent, $matches);

        $one_month_ago_value = explode('"', $matches[0][0])[7];

        // 3. 「前月」ボタンを押す
        $response = $this->get(route('attendance.list.byStaff', [
            'id' => $user_id,
            'month' => $one_month_ago_value,
        ]));

        $response->assertSee($user_name);  // ユーザー名の登場を念のため確認。
        $response->assertSee(Carbon::parse($one_month_ago_value)->isoFormat('YYYY/MM')); // 表示年月を検証。

          // attendancesテーブル情報との一致を検証
        $responseData = $response->original->getData();

        $responseAttendanceLists = $responseData['attendanceLists'];
        $responseThisMonth = Carbon::parse($one_month_ago_value)->format('Y-m');  // DB検索用日付

          // attendancesテーブル情報の取得。
        $database_attendances = Attendance::where('user_id', $user_id)->where('date', 'like', $responseThisMonth .'%')->get();
        $response_list_count = count(array_keys($responseAttendanceLists));

          // データベース値とレスポンスデータを１件ずつ突合して検証。
        foreach ($database_attendances as $database) {
            for ( $i=0; $i<=$response_list_count; $i++ ) {
                if ($responseAttendanceLists[$i]['date'] <> $database->date) {
                    // view側は空欄の日付もあるため、その場合はスキップ。
                    continue;
                } elseif ( $responseAttendanceLists[$i]['date'] == $database->date ) {
                    // 日付が一致した場合に、勤務開始・終了、休憩時間、勤務合計時間の４項目が一致することを検証。
                    $this->assertEquals($database->attendanced_at, $responseAttendanceLists[$i]['attendanced_at']);
                    $this->assertEquals($database->leaved_at, $responseAttendanceLists[$i]['leaved_at']);
                    $this->assertEquals($database->total_break_time, $responseAttendanceLists[$i]['total_break_time']);
                    $this->assertEquals($database->total_attendance_time, $responseAttendanceLists[$i]['total_attendance_time']);
                    $check_date[] = $responseAttendanceLists[$i]['date'];
                    break;
                }
            }
        }
    }

    public function test_getOneMonthLaterCheck()
    {
        //14-4. 「翌月」を押下した時に表示月の翌月の情報が表示される ことを検証
        $admin_user_id = '3';  // シーダー生成済の管理者
        $user_id = '4';        // シーダー生成済の一般ユーザー
        $user = User::find($user_id);
        $user_name = $user->name;

        // 1. 管理者ユーザーにログインする
        $admin_user = User::find($admin_user_id);
        $this->actingAs($admin_user)->assertAuthenticated();

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list.byStaff', ['id' => $user_id]));
        $response->assertStatus(200);

          // 「翌月」ボタンのリンクをレスポンスから取得する
        $responseContent = $response->getContent();
        $pattern = '/class="one_month_later_link".*?>/i';
        preg_match_all($pattern, $responseContent, $matches);

        $one_month_later_value = explode('"', $matches[0][0])[7];

        // 3. 「翌月」ボタンを押す
        $response = $this->get(route('attendance.list.byStaff', [
            'id' => $user_id,
            'month' => $one_month_later_value,
        ]));

        $response->assertSee($user_name);  // ユーザー名の登場を確認。
        $response->assertSee(Carbon::parse($one_month_later_value)->isoFormat('YYYY/MM')); // 表示年月を検証。

          // 翌月のデータはシーダーでも生成していないため、viewの表示データがないことを検証。
        $responseData = $response->original->getData();
        $this->assertFalse(isset($responseData['attendanceLists']));
    }

    public function test_pushDetailButtonCheck()
    {
        //14-5. 「詳細」を押下すると、その日の勤怠詳細画面に遷移する ことを検証
        $admin_user_id = '3';  // シーダー生成済の管理者
        $user_id = '4';        // シーダー生成済の一般ユーザー
        $user = User::find($user_id);
        $user_name = $user->name;

        // 1. 管理者ユーザーにログインする
        $admin_user = User::find($admin_user_id);
        $this->actingAs($admin_user)->assertAuthenticated();

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list.byStaff', ['id' => $user_id]));
        $response->assertStatus(200);

          // リストから対象を選び、「詳細」ボタンのリンク等の内容を取得。
        $responseContent = $response->getContent();
        $pattern = '/<td class="list-table__item">\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?"list-table__item-link" href=.*?詳細/i';
        preg_match_all($pattern, $responseContent, $matches);

          // リストの上から３番目の勤怠データを選ぶこととする
        $list_sellect_date = trim(explode("\n", $matches[0][2])[1]);  // MM/DD(曜日)形式の日付を取得。
        $list_sellect_attendanced_at = trim(explode("\n", $matches[0][2])[4]); // 勤務開始時刻を取得。
        $list_sellect_leaved_at = trim(explode("\n", $matches[0][2])[7]);      // 勤務終了時刻を取得。
        $list_sellect_button_link = explode('"', explode("\n", $matches[0][2])[16])[3]; // 「詳細」ボタンリンクを取得。

          // 日付を比較可能なようにCarbon形式に変換。
        $now = Carbon::now();
        $list_sellcet_date_carbon = Carbon::parse($now->format('Y') . '-' . explode("(", str_replace("/", "-", $list_sellect_date))[0]);

        // 3. 「詳細」ボタンを押す
        $response = $this->get($list_sellect_button_link);
        $response->assertStatus(200);
        $response->assertSee($user_name);  // ユーザー名の登場を検証。

        $response->assertSee($list_sellcet_date_carbon->isoFormat('YYYY年')); // 表示年を検証。
        $response->assertSee($list_sellcet_date_carbon->isoFormat('M月D日')); // 表示月日を検証。

          // 勤怠一覧ページで取得した勤怠開始・終了が一致することを検証。

        $responseContent = $response->getContent();

            // view表示の出勤時間を取得し、勤怠一覧での出勤時間と一致することを検証。
        $pattern = '/name="attendanced_at"\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches);
        $response_attendanced_at = explode('"', $matches[0][0])[3]; 
        $this->assertEquals($list_sellect_attendanced_at, $response_attendanced_at);

            // view表示の退勤時間を取得し、勤怠一覧での退勤時間と一致することを検証。
        $pattern = '/name="leaved_at"\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches);
        $response_leaved_at = explode('"', $matches[0][0])[3];
        $this->assertEquals($list_sellect_leaved_at, $response_leaved_at);
    }
}

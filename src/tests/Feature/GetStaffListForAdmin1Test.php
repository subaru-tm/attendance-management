<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class GetStaffListForAdmin1Test extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = DatabaseSeeder::class;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_GetAllStaffProfileCheck()
    {
        // 14-1. 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる ことを検証
        $user_id = '3';  // シーダー生成済の管理者

        // 1. 管理者でログインする
        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        // 2. スタッフ一覧ページを開く
        $response = $this->get(route('staff.list'));
        $response->assertStatus(200);
        $responseContent = $response->getContent();
        $pattern = '/<td class="list-table__item">\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches);

        $i = 0;
        $response_names = [];  // 名前を格納する配列の初期化
        $response_emails = []; // メールアドレスを格納する配列の初期化

          // view表示の名前とメールアドレスを配列に格納。
        foreach($matches[0] as $item) {
            if($i % 3 == 0) {
                $response_names[] = trim(explode("\n", $item)[1]);
            }
            if($i % 3 == 1) {
                $response_emails[] = trim(explode("\n", $item)[1]);
            }
            $i++;
        }

          // usersテーブルを全権取得し、1件ずつ上記と突合して検証とする
        $users = User::all();
        $j = 0;
        foreach ($users as $user) {
            $database_name = $user->name;
            $database_email = $user->email;
            $this->assertEquals($database_name, $response_names[$j]);
            $this->assertEquals($database_email, $response_emails[$j]);
            $j++;
        }
    }

    public function test_GetAttendanceListCheck()
    {
        // 14-2. ユーザーの勤怠情報が正しく表示される ことを検証
        $admin_user_id = '3';  // シーダー生成済の管理者
        $user_id = '4';        // シーダー生成済の一般ユーザー
        $user = User::find($user_id);
        $user_name = $user->name;

        // 1. 管理者でログインする
        $admin_user = User::find($admin_user_id);
        $this->actingAs($admin_user)->assertAuthenticated();

          // 一度スタッフ一覧ページを開き、選択するユーザーの「詳細」ボタンリンクを取得する
        $response = $this->get(route('staff.list'));
        $response->assertStatus(200);

        $responseContent = $response->getContent();
        $pattern = '/<a href=.*?詳細/i';
        preg_match_all($pattern, $responseContent, $matches);

          // 「詳細」ボタンを押す対象ユーザーをuser_id = '4'とする。
          //  配列内のキー'3'(表示順に0始まりの4番目)に相当。
        $detail_button_link = explode('"', $matches[0][$user_id - 1])[1];

        // 2. 選択したユーザーの勤怠一覧ページを開く
        $response = $this->get($detail_button_link);
        $response->assertStatus(200);

          // view表示のタイトル箇所に"［ユーザー名]さんの勤怠"と表示されるため、
          // ユーザー名が表示されることで、ユーザーの一致を検証。
        $response->assertSee($user_name);

          // また、attendancesテーブル情報との一致にて「勤怠情報が正確に表示」を検証
        $responseData = $response->original->getData();

        $responseAttendanceLists = $responseData['attendanceLists'];
        $responseThisMonth = str_replace("/", "-", $responseData['this_month_display']);
          // DB検索用に日付を取得。('YYYY/MM'形式から'YYYY-MM'に変換。クエリ処理のため)

          // attendancesテーブル情報の取得。
        $database_attendances = Attendance::where('user_id', $user_id)->where('date', 'like', $responseThisMonth .'%')->get();
        $response_list_count = count(array_keys($responseAttendanceLists));

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
                    break;
                }
            }
        }
    }
}

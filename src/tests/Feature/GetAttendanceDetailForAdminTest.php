<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Tests\TestCase;

class GetAttendanceDetailForAdminTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_displaySelectedDetailCheck()
    {
        // 13-1. 勤怠詳細画面に表示されるデータが選択したものになっている ことを検証。

        $admin_user_id = '3';  // シーダー生成済の管理者

        // 1. 管理者ユーザーにログインする
        $admin_user = User::find($admin_user_id);
        $this->actingAs($admin_user)->assertAuthenticated();

          // 勤怠一覧画面を開き、対象の勤怠データを選択する
        $user_id = '4'; // 表示する勤怠データのユーザー
        $response = $this->get(route('attendance.list.byStaff', ['id' => $user_id]));
        $response->assertStatus(200);

        $responseContent = $response->getContent();
        $pattern = '/<a class="list-table__item-link" href=.*?詳細.*?>/i';
        preg_match_all($pattern, $responseContent, $matches);

        $attendance_detail_link = explode('"', $matches[0][8])[3];
        $attendance_id = explode('/', $attendance_detail_link)[2]; // パスパラメータのattendance_idも取得。

        // 2. 勤怠詳細ページを開く
        $response = $this->get($attendance_detail_link);
        $responseContent = $response->getContent();

        $pattern = '/"detail-table__header.*?名前.*?\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches);
        $response_display_name = explode('<', explode('>', $matches[0][0])[3])[0]; // 名前の取得。

        $pattern = '/"detail-table__header.*?日付.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n.*?\n/i';
        preg_match_all($pattern, $responseContent, $matches);

        $response_display_year = trim(explode("\n", $matches[0][0])[2]);  // 年を取得。
        $response_display_date = trim(explode("\n", $matches[0][0])[6]);  // 月日を取得。

          // attendancesテーブルの値を取得し、上記と一致することを検証。
        $database_attendance = Attendance::with('user')->find($attendance_id);  // 詳細ボタン押下の際のidをキーとする
        $database_user_name = $database_attendance->user->name;
        $database_attendance_date = $database_attendance->date;
        $database_attendance_year = Carbon::parse($database_attendance_date)->isoFormat('YYYY年');
        $database_attendance_month_day = Carbon::parse($database_attendance_date)->isoFormat('M月D日');

        $this->assertEquals($database_user_name, $response_display_name);
        $this->assertEquals($database_attendance_year, $response_display_year);
        $this->assertEquals($database_attendance_month_day, $response_display_date);
    }

     /**
     * @test
     * @dataProvider dataproviderValidation
     */
    public function correctValidationCheck(array $keys, array $values, array $messages, bool $expect)
    {
        // バリデーションチェックは検証項目が同じであるため、データプロバイダーを使用する（13-2 ～13-5 が対象）
        $admin_user_id = '3';  // シーダー生成済の管理者
        $user_id = '4'; // シーダー生成済の一般ユーザー（勤怠情報登録済）

        // 1. 管理者ユーザーにログインする

        $admin_user = User::find($admin_user_id);
        $this->actingAs($admin_user)->assertAuthenticated();

        $test_date = '2025-07-11';

        // 2. 勤怠詳細ページを開く
        
          // ユーザーの勤怠データを取得する
        $attendance = Attendance::where('user_id', $user_id)->where('date', $test_date)->first();
        $attendance_id = $attendance->id;

          // 勤怠詳細ページを開く
        $response = $this->get(route('attendance.detail', ['id' => $attendance_id]));
        $response->assertStatus(200);
        $response->assertViewIs('attendance-detail');

        // 3. 登録データを設定する

        $dataList = ['id' => $attendance_id] + array_combine($keys, $values);

        // 4. 保存処理をする

        $response = $this->patch(route('admin.update', $dataList));
        $response->assertStatus(302);
        $responseMessage = $response->exception->errors();
        $responseCode = $response->exception->getCode();
          // エラーメッセージとコードを検証。
        $this->assertSame($messages, $responseMessage);
        $this->assertEquals($expect, $responseCode);

    }

    public function dataproviderValidation()
    {
        return [
            '13-2. 出勤時間が退勤時間より後になっている場合、「出勤時間もしくは退勤時間が不適切な値です」を表示' => [
                ['attendanced_at', 'leaved_at', 'started_at[]', 'ended_at[]', 'remarks'],
                ['19:00', '17:00', null, null, 'test'],
                ['attendanced_at' => ['出勤時間もしくは退勤時間が不適切な値です']],
                false
            ],
            '13-3. 休憩開始時間が退勤時間より後になっている場合、「休憩時間が勤務時間外です」を表示' => [
                ['attendanced_at', 'leaved_at', 'started_at[]', 'ended_at[]', 'remarks'],
                ['09:00', '18:00', '19:00', '18:00', 'test'],
                ['started_at.0' => ['休憩時間が勤務時間外です']],
                  // テストケース一覧の期待挙動と異なりますが、コーチと相談して「機能要件」に合わせたテストにしています
                false
            ],
            '13-4. 休憩終了時間が退勤時間より後になっている場合、「休憩時間が勤務時間外です」を表示' => [
                ['attendanced_at', 'leaved_at', 'started_at[]', 'ended_at[]', 'remarks'],
                ['09:00', '18:00', '17:30', '18:30', 'test'],
                ['ended_at.0' => ['休憩時間が勤務時間外です']],
                  // テストケース一覧の期待挙動と異なりますが、コーチと相談して「機能要件」に合わせたテストにしています
                false
            ],
            '13-5. 備考欄が未記入の場合、「備考を記入してください」を表示' => [
                ['attendanced_at', 'leaved_at', 'started_at[]', 'ended_at[]', 'remarks'],
                ['09:00', '18:00', '12:00', '13:00', null],
                ['remarks' => ['備考を記入してください']],
                false
            ],
        ];
    }

}

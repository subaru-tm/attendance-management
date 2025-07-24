<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Application;
use Carbon\Carbon;
use Tests\TestCase;

class CorrectAttendanceForGeneral1Test extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = DatabaseSeeder::class;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /**
     * @test
     * @dataProvider dataproviderValidation
     */
    public function correctValidationCheck(array $keys, array $values, array $messages, bool $expect)
    {
        // バリデーションチェックは検証項目が同じであるため、データプロバイダーを使用する（11-1 ～11-4 が対象）
        $user_id = '4';  // シーダー生成済のユーザー

        // 1. 勤怠情報が登録されたユーザーにログインする

        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        $now = Carbon::now();
        $this_month = $now->isoFormat('YYYY-MM');

        // 2. 勤怠詳細ページを開く
        
          // ログインユーザーの勤怠データＩＤを取得する
        $attendance = Attendance::where('user_id', $user_id)->where('date', 'like', $this_month . '%')->first();
        $attendance_id = $attendance->id;

          // 勤怠詳細ページを開く
        $response = $this->get(route('attendance.detail', ['id' => $attendance_id]));
        $response->assertStatus(200);
        $response->assertViewIs('attendance-detail');

        // 3. 登録データを設定する

        $dataList = ['id' => $attendance_id] + array_combine($keys, $values);

        // 4. 保存処理をする

        $response = $this->post(route('application',$dataList));
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
            '11-1. 出勤時間が退勤時間より後になっている場合、「出勤時間もしくは退勤時間が不適切な値です」を表示' => [
                ['attendanced_at', 'leaved_at', 'started_at[]', 'ended_at[]', 'remarks'],
                ['19:00', '17:00', null, null, 'test'],
                ['attendanced_at' => ['出勤時間もしくは退勤時間が不適切な値です']],
                false
            ],
            '11-2. 休憩開始時間が退勤時間より後になっている場合、「休憩時間が勤務時間外です」を表示' => [
                ['attendanced_at', 'leaved_at', 'started_at[]', 'ended_at[]', 'remarks'],
                ['09:00', '18:00', '19:00', '18:00', 'test'],
                ['started_at.0' => ['休憩時間が勤務時間外です']],
                  // テストケース一覧の期待挙動と異なりますが、コーチと相談して「機能要件」に合わせたテストにしています
                false
            ],
            '11-3. 休憩終了時間が退勤時間より後になっている場合、「休憩時間が勤務時間外です」を表示' => [
                ['attendanced_at', 'leaved_at', 'started_at[]', 'ended_at[]', 'remarks'],
                ['09:00', '18:00', '17:30', '18:30', 'test'],
                ['ended_at.0' => ['休憩時間が勤務時間外です']],
                  // テストケース一覧の期待挙動と異なりますが、コーチと相談して「機能要件」に合わせたテストにしています
                false
            ],
            '11-4. 備考欄が未記入の場合、「備考を記入してください」を表示' => [
                ['attendanced_at', 'leaved_at', 'started_at[]', 'ended_at[]', 'remarks'],
                ['09:00', '18:00', '12:00', '13:00', null],
                ['remarks' => ['備考を記入してください']],
                false
            ],
        ];
    }

    public function test_correctApplicationCheck()
    {
        // 11-5. 修正申請処理が実行される ことを検証
        $user_id = '4';  // シーダー生成済の一般ユーザー
        $admin_user_id ='3'; // シーダー生成済の管理者

        // 1. 勤怠情報が登録されたユーザーにログインする

        $user = User::find($user_id);
        $this->actingAs($user)->assertAuthenticated();

        $now = Carbon::now();
        $this_month = $now->isoFormat('YYYY-MM');

        // 2. 勤怠詳細を修正し保存処理をする
        
          // ログインユーザーの勤怠データＩＤを取得する
        $attendance = Attendance::with('user')->where('user_id', $user_id)->where('date', 'like', $this_month . '%')->first();
        $attendance_id = $attendance->id;
        $attendance_user_name = $attendance->user->name;

          // 勤怠詳細ページを開く
        $response = $this->get(route('attendance.detail', ['id' => $attendance_id]));
        $response->assertStatus(200);
        $response->assertViewIs('attendance-detail');

          // 修正内容
        $attendanced_at = "10:00";
        $leaved_at = "20:00";
        $started_at[] = "11:00";
        $started_at[] = "14:30";
        $ended_at[] = "11:45";
        $ended_at[] = "14:45";
        $remarks = "急用のため";

          // 保存する
        $response = $this->post(route('application', [
            'id' => $attendance_id,
            'attendanced_at' => $attendanced_at,
            'leaved_at' => $leaved_at,
            'started_at' => $started_at,
            'ended_at' => $ended_at,
            'remarks' => $remarks,
        ]));
        $response->assertStatus(302); // リダイレクトステータス

        // 3. 管理者ユーザーで承認画面と申請一覧画面を確認する

        $admin_user = User::find($admin_user_id);
        $this->actingAs($admin_user)->assertAuthenticated();
          // 対象の申請IDを取得する
        $application = Application::where('attendance_id', $attendance_id)->first();
        $application_id = $application->id;
        $application_date =$application->application_date;

          // 承認画面を確認
        $response = $this->get(route('application.detail', ['attendance_correct_request' => $application_id ]));
        $response->assertStatus(200);
        $response->assertViewIs('application-detail');

            // 内容の表示に加え、「承認」ボタンが表示されているはず。
        $expect = '承認';
        $response->assertSee($expect);

          // 申請一覧画面を確認　（「承認待ち」として{tab}にwaitingを渡す）
        $response = $this->get(route('correction.list', ['tab' => 'waiting']));
        $response->assertStatus(200);
        $response->assertViewIs('application-list');

        $response->assertSee($attendance_user_name); // 今回申請したユーザーの名前が表示されている
        $response->assertSee(Carbon::parse($application_date)->isoFormat('YYYY/MM/DD')); // 今回申請した申請日付が表示されている
        $response->assertSee($remarks); // 今回申請した備考欄の記述が表示されている

    }



}

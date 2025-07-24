<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class StatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = DatabaseSeeder::class;
    /**
     * @return void
     */
    public function test_beforeAttendanceCheck()
    {
        // 5-1. 勤務外の場合、画面上に表示されるステータスが「勤務外」となることを検証
        $now = Carbon::now();
        $today = $now->isoFormat('YYYY-MM-DD');

        // 0. まずは勤務外のユーザーを探す。
        //    シーダーで生成されたユーザーのIDのうち、当日の出勤データ(attendancesテーブル)が存在しないIDを探索。
        //    当日の出勤データがある(出勤中の)ユーザーIDを特定し、差分を取ることで、勤務外ユーザを特定。

        $user_ids = [1, 2, 3, 4]; // シーダー生成のうち、使用可能なユーザーID（メール認証済）

        $today_attendances = Attendance::where('date', $today)->whereIn('user_id', $user_ids);
        $missing_ids = collect($user_ids)->diff($today_attendances->pluck('user_id'));

        $user_id = $missing_ids->first();
        $user = User::find($user_id);

        // 1. ステータスが勤務外のユーザーにログインする
        $this->actingAs($user)->assertAuthenticated();

        // 2. 勤怠打刻画面を開く
        $response = $this->get(route('attendance'));
        $response->assertStatus(200);

        // 3. 画面に表示されているステータスを確認する
        $expect = '勤務外';
        $response->assertSee($expect);

    }
    
    /**
     * @test
     * @dataProvider dataproviderValidation
     */
    public function StatusDisplayCheck(string $status, string $expect)
    {
        // 以降のステータスは、attendancesテーブルのデータを使用して表示しているため定型的な検証が可能。
        // このため、データプロバイダを使用して検証する。

        // 1. 対象ステータスのユーザーにログインする
        $now = Carbon::now();
        $today = $now->isoFormat('YYYY-MM-DD');
        $attendance = Attendance::where('status', $status)->where('date', $today)->first();

        $user = User::find($attendance['user_id']);
        $this->actingAs($user)->assertAuthenticated();

        // 2. 勤怠打刻画面を開く
        $response = $this->get(route('attendance'));
        $response->assertStatus(200);

        // 3. 画面に表示されているステータスを確認する
        $response->assertSee($expect);
    }

    public function dataproviderValidation()
    {
        return [
            '5-2. 出勤中の場合、ステータス「出勤中」を表示' => [
                '1',
                '出勤中',
            ],
            '5-3. 休憩中の場合、ステータス「休憩中」を表示' => [
                '2',
                '休憩中',
            ],
            '5-4. 退勤済の場合、ステータス「退勤済」を表示' => [
                '3',
                '退勤済',
            ],

        ];
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class GetTimeTest extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = DatabaseSeeder::class;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_GetTimeCheck()
    {
        // 0. 勤怠打刻画面を開くため、先にログインする
        //    （ログインしないとミドルウェアでログイン画面に誘導されるため）

        $user = User::first();
        $this->actingAs($user)->assertAuthenticated();

        // 1. 勤怠打刻画面を開く

        $response = $this->get(route('attendance'));
        $response->assertStatus(200);


        // 2. 画面に表示されている日時情報を確認する

          // 比較するために現在時刻をviewでの表示形式に合わせて先に用意。
        $now = Carbon::now();
        $formattedTime = $now->format('H:i');

          // viewの内容を取得し、上記の時刻と同じ内容・形式で表示されていることを検証。
        $response->assertSee($formattedTime);

    }
}

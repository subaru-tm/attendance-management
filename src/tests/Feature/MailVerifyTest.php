<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Auth\Notifications\VerifyEmail;


class MailVerifyTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_emailVerificationCanBeSentCheck()
    {
        // 16-1. 会員登録後、認証メールが送信される ことを検証

        Notification::fake(); // メール送信をモック

        // 1. 会員登録をする
        $response = $this->post('/register', [
            'name' => 'mail_testuser',
            'email' => 'mail_test@test.com',
            'password' => 'testpass',
            'password_confirmation' => 'testpass',
        ]);
        $response->assertRedirect('/email/verify');
        $user = Auth::user();

        // 2. 認証メールを送信する
        $user->sendEmailVerificationNotification();

          // 認証メールが送信されたことを検証
        Notification::assertSentTo(
            [$user],  // 会員登録したユーザー宛て
            CustomVerifyEmail::class,
            function ($notification, $channels) use ($user) {
                $mailable = $notification->toMail($user);

                // メールタイトルが正しいか検証
                $this->assertEquals($mailable->subject, 'メールアドレス確認のお願い');

                // 署名付きURLが正しいか検証
                $verificationUrl = URL::temporarySignedRoute(
                    'verification.verify',
                    now()->addMinutes(config('auth.verification.expire', 60)),
                    [
                        'id' => $user->getKey(),
                        'hash' => sha1($user->getEmailForVerification()),
                    ]);
                $this->assertStringContainsString($verificationUrl, $mailable->actionUrl); // actionUrlにURLが含まれているか確認
        
                return true;
            }
        );
    }

    public function test_verificationUrlCheck()
    {
        //16-2. メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する ことを検証

        Notification::fake(); // メール送信をモック

        // 会員登録をする
        $response = $this->post('/register', [
            'name' => 'mail_test2user',
            'email' => 'mail_test2@test.com',
            'password' => 'testpass',
            'password_confirmation' => 'testpass',
        ]);
        $response->assertRedirect('/email/verify');
        $user = Auth::user();

        // 1. メール認証誘導画面を表示する
        $response = $this->get('/email/verify');
        $response->assertStatus(200);
        $response->assertSee('認証はこちらから');

        // 2. 「認証はこちらから」ボタンを押下
        $responseContent = $response->getContent();

        // 3. メール認証サイトを表示する
        $pattern = '/<div class="verification-url">\n.*?認証はこちらから/i';
        preg_match_all($pattern, $responseContent, $matches);

        $response_verification_button_link = explode('"', $matches[0][0])[3];


        //16-3. メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する ことを検証
        // 1.メール認証を完了する
        $response = $this->get($response_verification_button_link);
        $response->assertStatus(302);
        $response->assertRedirect(route('attendance'));


        // 2. 勤怠登録画面を表示する
          // view内容を取得し、勤怠登録画面であること、
          // および、象徴的な内容としてステータス「勤務外」が表示されることを検証
        $response = $this->get(route('attendance'));
        $response->assertStatus(200);
        $response->assertViewIs('attendance');
        $response->assertSee('勤務外');
    }
}

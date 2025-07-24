<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\RegisterRequest;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /**
     * @test
     * @dataProvider dataproviderValidation
     */
    public function registerValidationCheck(array $keys, array $values, array $messages, bool $expect)
    {
        $dataList = array_combine($keys, $values);

        $request = new RegisterRequest;
        $rules = $request->rules();
        $validator = Validator::make($dataList, $rules);
        $validator = $validator->setCustomMessages($request->messages());
        $result = $validator->passes();
        $this->assertEquals($expect, $result);
        $this->assertSame($messages, $validator->errors()->messages());

    }

    public function dataproviderValidation()
    {
        return [
            '1-1. 名前が未入力の場合、「お名前を入力してください」を表示' => [
                ['name', 'email', 'password', 'password_confirmation'],
                [null, 'test@test.com', 'testpass', 'testpass'],
                ['name' => ['お名前を入力してください']],
                false
            ],
            '1-2. メールアドレスが未入力の場合、「メールアドレスを入力してください」を表示' => [
                ['name', 'email', 'password', 'password_confirmation'],
                ['testuser', null, 'testpass', 'testpass'],
                ['email' => ['メールアドレスを入力してください']],
                false
            ],
            '1-3. パスワードが8文字未満の場合、「パスワードは8文字以上で入力してください」を表示' => [
                ['name', 'email', 'password', 'password_confirmation'],
                ['testuser', 'test@test.com', 'test', 'test'],
                ['password' => ['パスワードは8文字以上で入力してください']],
                false
            ],
            '1-4. パスワードが一致しない場合、「パスワードと一致しません」を表示' => [
                ['name', 'email', 'password', 'password_confirmation'],
                ['testuser', 'test@test.com', 'testpass', 'aaaaaaaa'],
                ['password' => ['パスワードと一致しません']],
                false
            ],
            '1-5. パスワードが未入力の場合、「パスワードを入力してください」を表示' => [
                ['name', 'email', 'password', 'password_confirmation'],
                ['testuser', 'test@test.com', null, 'testpass'],
                ['password' => ['パスワードを入力してください']],
                false
            ],
        ];
    }

    public function test_normalRegisterCheck() {
        // 1-6.フォームに内容が入力されていた場合、データが正常に保存される
        $response = $this->post('/register', [
            'name' => 'testuser',
            'email' => 'test@test.com',
            'password' => 'testpass',
            'password_confirmation' => 'testpass',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', [
            'name' => 'testuser',
            'email' => 'test@test.com',
        ]);
    }
}

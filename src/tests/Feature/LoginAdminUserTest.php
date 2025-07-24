<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\LoginRequest;
use Tests\TestCase;

class LoginAdminUserTest extends TestCase
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
    public function loginValidationCheck(array $keys, array $values, array $messages, bool $expect)
    {
        $dataList = array_combine($keys, $values);

        $response = $this->get(route('admin.login'));

        $response = $this->post(route('admin.login.store'), $dataList);

        $response->assertRedirect(route('admin.login'));
        $response->assertStatus(302);

        $errors = Session::get('errors');

        $this->assertSame($messages, $errors->messages());
    }

    public function dataproviderValidation()
    {
        return [
            '3-1. メールアドレスが未入力の場合、「メールアドレスを入力してください」を表示' => [
                ['email', 'password'],
                [null, 'test3pass'],
                ['email' => ['メールアドレスを入力してください']],
                false
            ],
            '3-2. パスワードが未入力の場合、「パスワードを入力してください」を表示' => [
                ['email', 'password'],
                ['test3@test.com', null],
                ['password' => ['パスワードを入力してください']],
                false
            ],
            '3-3. 登録内容と一致しない場合、「ログイン情報が登録されていません」を表示' => [
                ['email', 'password'],
                ['aaa@com', 'testpass'],
                ['email' => ['ログイン情報が登録されていません']],
                false
            ],
        ];
    }

}

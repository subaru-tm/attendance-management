# Attendance-management

## 環境構築
- Dockerビルド
  - git clone git@github.com:coachtech-material/laravel-docker-template.git
  - mv laravel-docker-template/ attendance-management/
  - docker-compose up -d --build
- Laravel環境構築
  - doker-compose exec php bash
  - composer install
  - cp .env.example .env  // 環境変数を設定
  - php artisan key:generate
  - composer require laravel/fortify // fortifyインストール
    - php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
  - composer require laravel/ui // メール認証のためlaravel/uiをインストール
    - php artisan ui bootstrap --auth
  - php artisan storage:link // シンボリックリンク作成(viewヘッダ画像等のため)

## 開発環境
- 勤怠登録画面: http://localhost/attendance
- phpMyAdmin : http://localhost:8080
- 会員登録画面: http://localhost/register
- ログイン画面
  - 一般ユーザー : http://localhost/login
  - 管理者      : http://localhost/admin/login


## 使用技術(実行環境)
- PHP 7.4.9
- Laravel Framework 8.83.8
- MySQL 8.0.26
- nginx 1.21.1
- laravel/fortify 1.19
- laravel/ui 3.4
- laravel/dusk 6.25

## ER図
<img width="1694" height="1206" alt="image" src="https://github.com/user-attachments/assets/f5dd2c9a-16ee-4862-b72d-c2c36130cf32" />

## その他
- 応用要件
  - FN011 メールを用いた認証機能    : 実装済。mailtrapにて実装しています
  - FN012 認証メール再送機能        : 実装済。
  - FN033 申請詳細表示機能(承認待ち) : 実装済。「承認待ち」の申請詳細では、修正不可・メッセージ表示を実装しています
  - FN045 CSV出力機能              : 実装済。
- 応用要件補足
  - メール認証はテストコードでの検証も実装しておりますが、もし画面・メールで検証される場合はmailtrapの下記アカウントをご使用ください。
    - ログイン(メアド):pleiades_tm@yahoo.co.jp
      - (参考)アカウントID：2330889
    - パスワード　：Test1@laravel
  - なお、上記にログインせずとも認証は可能です。
    - 会員登録後、メール認証誘導画面に遷移します。当画面にて「認証はこちらから」ボタンを押下ください。
    - 当ボタンに、認証URLを設定してありますので、当ボタン押下によりメール認証されます。
    - この処理は本質的には「メール認証になっていない」という問題点があるかとは思いますが、模擬案件に限り、評価いただくための便宜上のものとご容赦ください。
- テストコードファイル名と機能の紐付き
  - テストコード作成: php artisan make:test {テストコード名}(下記参照)
  - テストコード実行: php artisan test
  - 各機能に対するテストコード名（~/src/tests/Feature/配下に格納。IDは「テストケース一覧」を引用）
    - ID: 1 認証機能（一般ユーザー）　　　 => RegisterTest
    - ID: 2 ログイン機能（一般ユーザー）　 => LoginGeneralUserTest
    - ID: 3 ログイン機能（管理者）　　　　 => LoginAdminUserTest
    - ID: 4 日時取得機能　　　　　　　　　 => GetTimeTest
    - ID: 5 ステータス確認機能　　　　　　 => StatusDisplayTest
    - ID: 6 出勤機能　　　　　　　　　　　 => AttendanceTest
    - ID: 7 休憩機能　　　　　　　　　　　 => BreakTimeTest
    - ID: 8 退勤機能　　　　　　　　　　　 => LeaveTest
    - ID: 9 勤怠一覧情報取得機能（一般ユーザー） => GetAttendanceListForGeneralTest
    - ID:10 勤怠詳細情報取得機能（一般ユーザー） => GetAttendanceDetailForGeneralTest
    - ID:11 勤怠詳細情報修正機能（一般ユーザー） => CorrectAttendanceForGeneral1Test
        - (ID:11は行数が多くなったため分割)     => CorrectAttendanceForGeneral2Test
        - (同上　　　　　　　　　　　　　　　)   => CorrectAttendanceForGeneral3Test
    - ID:12 勤怠一覧情報取得機能（管理者）　　　 => GetAttendanceListForAdminTest
    - ID:13 勤務詳細情報取得・修正機能（管理者） => GetAttendanceDetailForAdminTest
    - ID:14 ユーザー情報取得機能（管理者）　　　 => GetStaffListForAdminTest
    - ID:15 勤怠情報修正機能（管理者）　　　　　 => CorrectAttendanceForAdminTest
    - ID:16 メール認証機能　　　　　　　　　　　 => MailVerifyTest
- テストコード実行時の留意点
  - 時刻のassertにて秒単位で現在時刻との突合せをしている箇所があり、稀に1秒差でエラーが発生します。
    - 主にテストID:7 BreakTimeTestで発生。
      - (テストID:8 LeaveTestでも１回エラー発生の実績あり）
  - 再実行すればエラー解消しますので、時刻が1秒差の場合は再実行をお願いします。
    - 原因はアプリケーション側の取得時刻と、検証用の取得時刻を別々にしており、端数処理の関係で、稀に秒数がズレるためだと思われます。
    - テスト要件から「時刻が正確に記録されている」ことの検証には秒単位での比較が必要と考えた次第です。

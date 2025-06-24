# Coachtech-attendance-management

## 環境構築
- Dockerビルド
  - git clone git@github.com:coachtech-material/laravel-docker-template.git
  - mv laravel-docker-template/ coachtech-attendance-management/
  - docer-compose up -d --build
- Laravel環境構築
  - doker-compose exec php bash
  - composer install
  - cp .env.example .env  // 環境変数を設定
  - php artisan key:generate
  - composer require laravel/fortify // fortifyインストール
    - php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
  - composer require laravel/ui // メール認証のためlaravel/uiをインストール
    - php artisan ui bootstrap --auth
  - composer require --dev laravel/dusk // laravel duskのインストール
  - php artisan storage:link // シンボリックリンク作成(viewヘッダ画像等のため)

## 開発環境
-  : http://localhost/


## 使用技術(実行環境)
- PHP 7.4.9
- Laravel Framework 8.83.8
- MySQL 8.0.26
- nginx 1.21.1
- laravel/fortify 1.19
- laravel/ui 3.4
- laravel/dusk 6.25

## ER図
![image](https://github.com/user-attachments/assets/d88c1b1b-85cb-4845-b2ff-86bbd670c7fb)


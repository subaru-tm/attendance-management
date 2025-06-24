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
  - 
- laravel duskのインストール
  - composer require --dev laravel/dusk

## 使用技術
- Laravel Framework 8.83.8
- laravel/dusk 6.25

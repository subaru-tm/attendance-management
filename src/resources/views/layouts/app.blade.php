<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH ATTENDANCE MANAGEMENT</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
</head>
<body>
<header class="header">
        <div class="header__inner">
            <a class="header__logo" href="/">
                <img src="{{ asset('storage/logo.svg') }}" alt="" />
            </a>

            <span class="header__nav">
                @if (request()->path() == 'register')
                    <!-- 会員登録画面の場合はナビを表示しない -->
                @elseif (request()->is('*login'))
                    <!-- ログイン画面（一般ユーザー、管理者用共に）の場合はナビを表示しない -->
                @else
                    @if (Auth::check())
                        @if ($user->is_admin)
                            <!-- 管理者がログイン中の場合 -->
                            <a class="header__nav-link" href="/admin/attendance/list">勤怠一覧</a>
                            <a class="header__nav-link" href="/admin/staff/list">スタッフ一覧</a>
                            <a class="header__nav-link" href="/stamp_correction_request/list:waiting">申請一覧</a>
                        @else
                            <!-- 一般ユーザーがログイン中の場合 -->
                            <a class="header__nav-link" href="/attendance">勤怠</a>
                            <a class="header__nav-link" href="/attendance/list">勤怠一覧</a>
                            <a class="header__nav-link" href="/stamp_correction_request/list:waiting">申請</a>
                        @endif
                        @if ($user->is_admin)
                            <form action="/admin/logout" method="GET">
                                @csrf
                                <button class="header__nav-button">ログアウト</button>
                            </form>
                        @else
                            <form action="/logout" method="POST">
                                @csrf
                                <button class="header__nav-button">ログアウト</button>
                            </form>
                        @endif
                    @endif
                @endif
            </span>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>

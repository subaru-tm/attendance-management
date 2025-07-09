@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="card">
        <div class="card-header">
            <h1>{{ __('会員登録') }}</h1>
        </div>
        <div class="card-body">
            <form class="form" action="/register" method="POST">
                @csrf
                <div class="form__group">
                    <label for="name">{{ __('名前') }}</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" />
                    <div class="form__group-alert">
                        @error('name')
                            <strong>{{ $message }}</strong>
                        @enderror
                    </div>
                </div>

                <div class="form__group">
                    <label for="email">{{ __('メールアドレス') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" >
                    <div class="form__group-alert">
                        @error('email')
                            <strong>{{ $message }}</strong>
                        @enderror
                    </div>
                </div>

                <div class="form__group">
                    <label for="password">{{ __('パスワード') }}</label>
                    <input id="password" type="password" name="password" >
                    <div class="form__group-alert">
                        @error('password')
                            @if( $message <> "パスワードと一致しません" )
                                <strong>{{ $message }}</strong>
                            @endif
                        @enderror
                    </div>
                </div>

                <div class="form__group">
                    <label for="password-confirm">{{ __('パスワード確認') }}</label>
                    <input id="password-confirm" type="password" name="password_confirmation" >
                    <div class="form__group-alert">
                        @error('password')
                            @if( $message == "パスワードと一致しません" )
                                <strong>{{ $message }}</strong>
                            @endif
                        @enderror
                    </div>
                </div>

                <div class="form__group">
                    <button type="submit">{{ __('登録する') }}</button>
                </div>
                <div class="form__group-link">
                    <a href="/login">ログインはこちら</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

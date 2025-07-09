@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="card">
        <div class="card-title">
            <h1>{{ __('ログイン') }}</h1>
        </div>
        <form class="form" method="POST" action="/login">
            @csrf
            <div class="form__group">
                <label for="email">{{ __('メールアドレス') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" />
                <div class="form__group-alert">
                    @error('email')
                        <strong>{{ $message }}</strong>
                    @enderror
                </div>
            </div>

            <div class="form__group">
                <label for="password">{{ __('パスワード') }}</label>
                <input id="password" type="password" name="password">
                <div class="form__group-alert">
                    @error('password')
                        <strong>{{ $message }}</strong>
                    @enderror
                </div>
            </div>

            <div class="form__group">
                <button type="submit">{{ __('ログインする') }}</button>
            </div>
            <div class="form__group-link">
                <a href="/register">{{ __('会員登録はこちら') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection

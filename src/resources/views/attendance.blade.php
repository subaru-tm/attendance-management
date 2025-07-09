@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="card">
        <div class="card-header">
            @if(isset($today_attendance->status))
                @switch($today_attendance->status)
                    @case("1")
                        {{ __('出勤中') }}
                        @break
                    @case("2")
                        {{ __('休憩中') }}
                        @break
                    @case("3")
                        {{ __('退勤済') }}
                        @break
                @endswitch
            @else
                {{ __('勤務外') }}
            @endif
        </div>
        <div class="card-body">
            <div class="card-body__date">
                {{ $today_form }}
            </div>
            <div class="card-body__time">
                {{ $now->format('H:i') }}
            </div>
            <div class="card-body__button">
                @if(isset($today_attendance->status))
                    <form method="POST" action="/attendance">
                        @csrf
                        @method('PATCH')
                        <div class="attendancing__button-area">
                        @switch($today_attendance->status)
                            @case("1")
                                <button class="leave_button"
                                        name="update_status"
                                        value="3" >
                                    {{ __('退勤') }}
                                </button>
                                <button class="break_button"
                                        name="update_status"
                                        value="2" >
                                    {{ __('休憩入') }}
                                </button>
                                @break
                            @case("2")
                                <button class="break_button"
                                        name="update_status"
                                        value="1" >
                                    {{ __('休憩戻') }}
                                </button>
                                @break
                            @case("3")
                                <p class="leaved_status_message">
                                    {{ __('お疲れ様でした。') }}
                                </p>
                                @break
                        @endswitch
                        </div>
                    </form>
                @else
                    <form method="POST" action="/attendance">
                        @csrf
                        <button class="attendance_button"
                                name="update_status"
                                value="1" >
                            {{ __('出勤') }}
                        </button> 
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>


@endsection
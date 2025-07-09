@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="card">
        <div class="card-header">
            <h1>{{ $this_day_jp }}の勤怠</h1>
        </div>
        <form action="/admin/attendance/list" method="get">
            @csrf
            <div class="card-day">
                <button type="submit" name="today" value="{{ $yesterday }}">
                    <p class="day__change-arrow">←</p>
                    <p class="day__change-label">前日</p>
                </button>

                <div class="this_day__display">
                    <img src="{{ asset('storage/calender-image.png') }}" alt="" width="25" height="25" />
                    <div class="this_day__display-format">{{ $this_day_slash }}</div>
                </div>

                <button type="submit" name="today" value="{{ $tomorrow }}">
                    <p class="day__change-label">翌日</p>
                    <p class="day__change-arrow">→</p>
                </button>
            </div>
        </form>
        <div class="list-table">
            <table class="list-table__inner">
                <tr class="list-table__row">
                    <th class="list-table__header">
                        <td class="list-table__header-item">名前</td>
                        <td class="list-table__header-item">出勤</td>
                        <td class="list-table__header-item">退勤</td>
                        <td class="list-table__header-item">休憩</td>
                        <td class="list-table__header-item">合計</td>
                        <td class="list-table__header-item">詳細</td>
                    </th>
                </tr>

                @if(isset($message))
                    <div class="nodata-message">
                        {{ $message }}
                    </div>
                @else
                    @foreach($attendances as $attendance)

                        <tr class="list-table__row">
                            <th></th>
                            <td class="list-table__item">
                                {{ $attendance->user->name }}
                            </td>
                            <td class="list-table__item">
                                @if( $attendance['attendanced_at'] <> null )
                                    {{ Carbon\Carbon::parse($attendance['attendanced_at'])->format('H:i') }}
                                @endif
                            </td>
                            <td class="list-table__item">
                                @if( $attendance['leaved_at'] <> null)
                                    {{ Carbon\Carbon::parse($attendance['leaved_at'])->format('H:i') }}
                                @endif
                            </td>
                            <td class="list-table__item">
                                @if( $attendance['total_break_time'] <> null)
                                    {{ Carbon\Carbon::parse($attendance['total_break_time'])->format    ('H:i') }}
                                @endif
                            </td>
                            <td class="list-table__item">
                                @if( $attendance['total_attendance_time'] <> null)
                                    {{ Carbon\Carbon::parse($attendance['total_attendance_time'])->format('H:i') }}
                                @endif
                            </td>
                            <td class="list-table__item">
                                @if( $attendance['id'] <> null)
                                    <a href="/attendance/{{ $attendance['id'] }}">詳細</a>
                                @else
                                    詳細
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
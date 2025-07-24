@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="card">
        <div class="card-header">
            @if (request()->path() == 'attendance/list')
                <h1>勤怠一覧</h1>
            @elseif ( request()->is('admin/attendance/staff/*') )
                <h1>{{ $attendance_user->name }}さんの勤怠</h1>
            @endif
        </div>
        <form 
            @if ( request()->path()  == 'attendance/list' )
                action="/attendance/list"
            @elseif ( request()->is('admin/attendance/staff/*') )
                action="/admin/attendance/staff/{{ $attendance_user->id }}"
            @endif
        method="get">
            @csrf
            <div class="card-month">
                <button class="one_month_ago_link" type="submit" name="month" value="{{ $oneMonthAgo }}">
                    <p class="month__change-arrow">←</p>
                    <p class="month__change-label">前月</p>
                </button>

                <div class="this_month__display">
                    <img src="{{ asset('storage/calender-image.png') }}" alt="" width="25" height="25" />
                    <div class="this_month__display-format">{{ $this_month_display }}</div>
                </div>

                <button class="one_month_Later_link" type="submit" name="month" value="{{ $oneMonthLater }}">
                    <p class="month__change-label">翌月</p>
                    <p class="month__change-arrow">→</p>
                </button>
            </div>
        </form>
        <div class="list-table">
            <table class="list-table__inner">
                <tr class="list-table__row">
                    <th class="list-table__header">
                        <td class="list-table__header-item">日付</td>
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
                    @foreach($attendanceLists as $attendance)
                        <!-- attendancesテーブルに実績がある場合、出力して次の -->
                        <!-- DBのデータを表示するにあたって、形式を整えて出力していく。-->

                        <tr class="list-table__row">
                            <th></th>
                            <td class="list-table__item">
                                {{ Carbon\Carbon::parse($attendance['date'])->isoFormat('MM/DD(ddd)') }}
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
                                    <a class="list-table__item-link" href="/attendance/{{ $attendance['id'] }}">詳細</a>
                                @else
                                    詳細
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
            </table>
        </div>
        @if ( request()->is('admin/attendance/staff/*') )
            <div class="csv-export">
                <form method="GET" action="/admin/attendance/staff/{{ $attendance_user->id }}/export">
                    @csrf
                    <input type="hidden" name="month" value="{{ $oneMonthAgo }}" />
                    <button class="csv-export__button">CSV出力</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
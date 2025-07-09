@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<?php use Carbon\Carbon; ?>
<div class="content">
    <div class="card">
        <div class="card-header">
            <h1>勤怠詳細</h1>
        </div>
        <form action="/stamp_correction_request/approve/{{ $attendance_correct_request }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="detail-table">
                <table class="detail-table__inner">
                    <tr class="detail-table__row">
                        <th class="detail-table__header">名前</th>
                        <td class="detail-table__item" colspan="2">
                            {{ $application->attendance->user->name }}
                        </td>
                    </tr>
                    <tr class="detail-table__row">
                        <th class="detail-table__header">日付</th>
                        <td class="detail-table__item">
                            {{ Carbon::parse($application->attendance->date)->isoFormat('YYYY年') }}
                        </td>
                        <td class="detail-table__dash"></td>
                        <td class="detail-table__item">
                            {{ Carbon::parse($application->attendance->date)->isoFormat('M月D日') }}
                        </td>
                    </tr>
                    <tr class="detail-table__row">
                        <th class="detail-table__header">出勤・退勤</th>
                        <td class="detail-table__item">
                            <input type="text" name="attendanced_at"
                                value="{{ Carbon::parse($application->correct_attendanced_at)->format('H:i') }}"
                                readonly
                                style="border: none;"
                                />
                        </td>
                        <td class="detail-table__dash">～</td>
                        <td class="detail-table__item">
                            <input type="text" name="leaved_at"
                                value="{{ Carbon::parse($application->correct_leaved_at)->format('H:i') }}"
                                readonly
                                style="border: none;" />
                        </td>
                    </tr>

                    <?php $i = 0; ?>
                    @foreach($correct_break_times as $break_time)
                        <?php $i++; ?>
                        <tr class="detail-table__row">
                            <th class="detail-table__header">休憩{{ $i }}</th>
                            <td class="detail-table__item">
                                <input type="text" name="started_at[]" 
                                    value="{{ Carbon::parse($break_time['started_at'])->format('H:i') }}"
                                    readonly
                                    style="border: none;" />
                            </td>
                            <td class="detail-table__dash">～</td>
                            <td class="detail-table__item">
                            <input type="text" name="ended_at[]" 
                                    value="{{ Carbon::parse($break_time['ended_at'])->format('H:i') }}"
                                    readonly
                                    style="border: none;" />
                            </td>
                        </tr>
                    @endforeach

                    <tr class="detail-table__row">
                        <th class="detail-table__header">備考</th>
                        <td class="detail-table__item-remarks" colspan="3">
                            <input type="text" name="remarks" value="{{ $application['remarks'] }}"
                                readonly
                                style="border: none;" />
                        </td>
                    </tr>
                </table>
            </div>
            <div class="form-button">
                @if ($application->approval_status)
                    <p class="form-button__only-status">承認済み</p>
                @else
                    <button type="submit" >承認</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection
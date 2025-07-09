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
        <form method="POST"
            @if ($user['is_admin'])
                action="/attendance/{{ $id }}/update"
            @else
                action="/attendance/{{ $id }}/correct_request"
            @endif
        >
            @csrf
            @if ($user['is_admin'])
                @method('PATCh')
            @endif
            <div class="detail-table">
                <table class="detail-table__inner">
                    <tr class="detail-table__row">
                        <th class="detail-table__header">名前</th>
                        <td class="detail-table__item" colspan="2">{{ $attendance_user['name'] }}</td>
                    </tr>
                    <tr class="detail-table__row">
                        <th class="detail-table__header">日付</th>
                        <td class="detail-table__item">
                            {{ Carbon::parse($attendance['date'])->isoFormat('YYYY年') }}
                        </td>
                        <td class="detail-table__dash"></td>
                        <td class="detail-table__item">
                            {{ Carbon::parse($attendance['date'])->isoFormat('M月D日') }}
                        </td>
                    </tr>
                    <tr class="detail-table__row">
                        <th class="detail-table__header">出勤・退勤</th>
                        <td class="detail-table__item">
                            <input type="text" name="attendanced_at"
                                value="{{ Carbon::parse($attendance['attendanced_at'])->format('H:i') }}"
                                @if($isExistsApplication)
                                    readonly
                                    style="border: none;"
                                @endif />
                        </td>
                        <td class="detail-table__dash">～</td>
                        <td class="detail-table__item">
                            <input type="text" name="leaved_at"
                                value="{{ Carbon::parse($attendance['leaved_at'])->format('H:i') }}"
                                @if($isExistsApplication)
                                    readonly
                                    style="border: none;"
                                @endif />
                        </td>
                    </tr>
                    <tr class="detail-table__row-alert">
                        <td colspan="3">
                            @error('attendanced_at')
                                {{ $message }}
                            @enderror
                        </td>
                    </tr>
                    <?php $i = 0; ?>
                    @foreach($break_times as $break_time)
                        <?php $i++; ?>
                        <tr class="detail-table__row">
                            <th class="detail-table__header">休憩{{ $i }}</th>
                            <td class="detail-table__item">
                                <input type="text" name="started_at[]" 
                                    value="{{ Carbon::parse($break_time['started_at'])->format('H:i') }}"
                                    @if($isExistsApplication)
                                        readonly
                                        style="border: none;"
                                    @endif />
                            </td>
                            <td class="detail-table__dash">～</td>
                            <td class="detail-table__item">
                            <input type="text" name="ended_at[]" 
                                    value="{{ Carbon::parse($break_time['ended_at'])->format('H:i') }}"
                                    @if($isExistsApplication)
                                        readonly
                                        style="border: none;"
                                    @endif />
                            </td>
                        </tr>
                        <tr class="detail-table__row-alert">
                            <td colspan="3">
                                <?php $j = $i-1; ?>
                                @error('started_at.' . $j)
                                    {{ $message }}
                                @enderror
                                @error('ended_at.' . $j)
                                    {{ $message }}
                                @enderror
                            </td>
                        </tr>
                    @endforeach

                    <!-- 休憩を新規で追加するように空白行を一行用意。修正申請中は不要とする -->

<!--                    @if($isExistsApplication <> 'true')
                        <tr class="detail-table__row">
                            <th class="detail-table__header">休憩</th>
                            <td class="detail-table__item">
                                <input type="text" name="started_at[]" />
                            </td>
                            <td class="detail-table__dash">～</td>
                            <td class="detail-table__item">
                                <input type="text" name="ended_at[]" />
                            </td>
                        </tr>
                        <tr class="detail-table__row-alert">
                            <td colspan="3">
                                <?php $j = $i; ?>
                                @error('started_at.' . $j)
                                    {{ $message }}
                                @enderror
                                @error('ended_at.' . $j)
                                    {{ $message }}
                                @enderror
                            </td>
                        </tr>
                    @endif
-->
                    <tr class="detail-table__row">
                        <th class="detail-table__header">備考</th>
                        <td class="detail-table__item-remarks" colspan="3">
                            <input type="text" name="remarks" value="{{ $attendance['remarks'] }}"
                                @if($isExistsApplication)
                                    readonly
                                    style="border: none;"
                                @endif />
                        </td>
                    </tr>
                    <tr class="detail-table__row-alert">
                        <td colspan="3">
                            @error('remarks')
                                {{ $message }}
                            @enderror
                        </td>
                    </tr>
                </table>
            </div>
            <div class="form-button">
                @if($isExistsApplication == 'true')
                    <span>* 承認待ちのため修正はできません。</span>
                @else
                    <button type="submit">修正</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection
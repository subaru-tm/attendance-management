@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/application-list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="card">
        <div class="card-header">
            <h1>申請一覧</h1>
        </div>
        <div class="tab-title">
            <a class="tab-title {{ (Request::is('/stamp_correction_request/list:waiting') ?
                'active' : '') }}"
                href="/stamp_correction_request/list:waiting"
                @if (request()->is('*waiting'))
                    style="font-weight: 700; color: #000000;"
                @endif 
                data-tab="tab1" >
                承認待ち
            </a>
            <a class="tab-title {{ (Request::is('/stamp_correction_request/list:approved') ?
                'active' : '') }}"
                href="/stamp_correction_request/list:approved"
                @if (request()->is('*approved'))
                    style="font-weight: 700; color: #000000;"
                @endif 
                data-tab="tab2" >
                承認済み
            </a>
        </div>
        <div class="list-table">
            <table class="list-table__inner">
                <tr class="list-table__row">
                    <th class="list-table__header">
                        <td class="list-table__header-item">状態</td>
                        <td class="list-table__header-item">名前</td>
                        <td class="list-table__header-item">対象日時</td>
                        <td class="list-table__header-item">申請理由</td>
                        <td class="list-table__header-item">申請日時</td>
                        <td class="list-table__header-item">詳細</td>
                    </th>
                </tr>

                <!-- URLの{tab}の値に応じて、一覧の「状態」項目をセットしておく -->

                <!-- 申請データ付きのattendancesをループして一覧表示する -->
                @foreach($applications as $application)
                    @if($application->approval_status)
                        <?php $approval_status_text = "承認済み" ?>
                    @else
                        <?php $approval_status_text = "承認待ち" ?>
                    @endif

                    <tr class="list-table__row">
                        <th></th>
                        <td class="list-table__item">
                            {{ $approval_status_text }}
                        </td>
                        <td class="list-table__item">
                            {{ $application->attendance->user->name }}
                        </td>
                        <td class="list-table__item">
                            <?php
                                $date = Carbon\Carbon::parse($application->attendance->date)->isoFormat('YYYY/MM/DD');
                            ?>
                            {{ $date }}
                        </td>
                        <td class="list-table__item">
                            {{ $application['remarks'] }}
                        </td>
                        <td class="list-table__item">
                            <?php
                                $application_date = Carbon\Carbon::parse($application->application_date)->isoFormat('YYYY/MM/DD');
                            ?>
                            {{ $application_date }}
                        </td>
                        <td class="list-table__item">
                            @if ($user->is_admin)
                                <a href="/stamp_correction_request/approve/{{ $application['id'] }}">詳細</a>
                            @else
                                <a href="/attendance/{{ $application->attendance->id }}">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff-list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="card">
        <div class="card-header">
            <h1>スタッフ一覧</h1>
        </div>
        <div class="list-table">
            <table class="list-table__inner">
                <tr class="list-table__row">
                    <th class="list-table__header">
                        <td class="list-table__header-item">名前</td>
                        <td class="list-table__header-item">メールアドレス</td>
                        <td class="list-table__header-item">月次勤怠</td>
                    </th>
                </tr>

                @foreach($users as $user)
                    <tr class="list-table__row">
                        <th></th>
                        <td class="list-table__item">
                            {{ $user->name }}
                        </td>
                        <td class="list-table__item">
                            {{ $user->email }}
                        </td>
                        <td class="list-table__item">
                            <a href="/admin/attendance/staff/{{ $user->id }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection
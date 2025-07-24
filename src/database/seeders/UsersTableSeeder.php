<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param =[
            'name' => 'テストユーザー１',
            'email' => 'test1@test.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('test1pass'),
        ];
        DB::table('users')->insert($param);

        $param =[
            'name' => 'テストユーザー２',
            'email' => 'test2@test.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('test2pass'),
        ];
        DB::table('users')->insert($param);

        $param =[
            'name' => '管理者テストユーザー３',
            'email' => 'test3@test.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('test3pass'),
            'is_admin' => '1',
        ];
        DB::table('users')->insert($param);

        $param =[
            'name' => 'テストユーザー４',
            'email' => 'test4@test.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('test4pass'),
        ];
        DB::table('users')->insert($param);

        $param =[
            'name' => 'テストユーザー５',
            'email' => 'test5@test.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('test5pass'),
        ];
        DB::table('users')->insert($param);

        $param =[
            'name' => 'テストユーザー６',
            'email' => 'test6@test.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('test6pass'),
        ];
        DB::table('users')->insert($param);

    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        Attendance::factory()->count(30)->create();
        $this->call(AttendancesTableSeeder::class);
        $this->call(ApplicationsTableSeeder::class);
    }
}

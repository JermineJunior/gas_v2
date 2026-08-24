<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Station::factory(10)->create();

        $this->call(StationTableSeeder::class);
        $this->call(UserTableSeeder::class);
        // يجب أن يأتي بعد إنشاء المستخدمين ليتم ربط الأدوار بهم
        $this->call(RolePermissionSeeder::class);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bayan = User::create([
            'name' => 'bayan software',
            'username' => 'bayan',
            'password' => bcrypt('123'),
            'type' => 0,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'password' => bcrypt('29652'),
            'type' => 1,
        ]);

        $user = User::create([
            'name' => 'User',
            'username' => 'user',
            'password' => bcrypt('29652'),
            'type' => 3,
        ]);

        // إرفاق كل المحطات بالمستخدم
        $bayan->stations()->syncWithoutDetaching(Station::pluck('id')->toArray());
        $admin->stations()->syncWithoutDetaching(Station::pluck('id')->toArray());

        $bayan->assignRole('admin');
        $admin->assignRole('admin');
        //$user->stations()->syncWithoutDetaching(Station::pluck('id')->toArray());
    }
}

<?php

namespace Database\Seeders;

use App\Models\Station;
//use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['محطة العطا', 'محطة وادي السندس'];
        foreach ($names as $name) {
            Station::create([
                'name' => $name
            ]);
        }
    }
}

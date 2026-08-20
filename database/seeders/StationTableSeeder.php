<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $names = ['محطة ابوحمد الطواحين' ,'محطة العبدية الطواحين','محطة عطبرة وادي زينب','محطة  دارمالي الطواحين','محطة وادي العشار','محطة درديب','محطة الفرقا','محطة اربعات','محطة نورايا','محطة كمريب'];
        $names = ['محطة دارمالي الطواحين','محطة العبيدية الطواحين'];
        foreach($names as $name){
            Station::create([
                'name' => $name
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Client;
//use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $busses = ['ابو نخيلة', 'الارسنال', 'الاودهار', 'البدراوي', 'الدامر', 'الرفاعي', 'الصافات', 'الصداقة', 'العزيزية', 'العنابي', 'المتزكل', 'بختان', 'بوادينا', 'شريان', 'عبدالاله', 'عبد الحليم', 'عبد الله', 'قرطاج', 'متاب', 'معتز'];
        foreach ($busses as $bus) {
            Client::create(['name' => $bus, 'type' => '2', 'user_id' => 3]);
        }
    }
}

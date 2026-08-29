<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['name', 'station_id', 'phone_number', 'national_id', 'address', 'date_of_birth'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }
}

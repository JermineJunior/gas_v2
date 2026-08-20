<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Station extends Model
{
    use HasFactory;
    protected $fillable = ['name','order'];

    public function operations()
    {
        return $this->hasMany(Operation::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'station_user', 'station_id', 'user_id');
    }
}

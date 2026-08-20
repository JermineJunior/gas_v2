<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name','type', 'phone','user_id'];

    public function details()
    {
        return $this->hasMany(Detail::class);
    }
}

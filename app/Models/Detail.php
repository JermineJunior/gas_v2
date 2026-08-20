<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detail extends Model
{
    protected $fillable = ['client_id', 'user_id', 'liter', 'price','amount', 'total', 'note', 'date'];

    protected function casts()
    {
        return [
            'date' => 'date',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

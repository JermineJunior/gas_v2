<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    protected $fillable = ['name', 'station_id', 'stock_id', 'max_counter', 'use_rollover'];

    // احتياط: إدراج حقول نوع الوقود المستمدة في التسلسل (مثل تسلسل الـ @json في الواجهة)
    protected $appends = ['fuel_type', 'fuel_type_id'];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function guns(): HasMany
    {
        return $this->hasMany(Gun::class);
    }

    /**
     * نوع الوقود المستمد من البير المرتبط بالماكينة
     * (نفس اصطلاح النوع المستخدم في بقية النظام: 1 = جازولين، 2 = بنزين)
     */
    public function getFuelTypeIdAttribute(): ?int
    {
        return $this->stock ? (int) $this->stock->type : null;
    }

    public function getFuelTypeAttribute(): ?string
    {
        return $this->fuel_type_id === 1 ? 'جازولين' : ($this->fuel_type_id === 2 ? 'بنزين' : null);
    }
}

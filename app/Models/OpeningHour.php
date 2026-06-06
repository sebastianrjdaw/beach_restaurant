<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpeningHour extends Model
{
    protected $fillable = ['weekday', 'opens_at', 'closes_at', 'is_closed', 'label'];

    protected function casts(): array
    {
        return ['is_closed' => 'boolean'];
    }
}

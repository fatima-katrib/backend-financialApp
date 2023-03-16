<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount',
        'schedule',

    ];
    public function Currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
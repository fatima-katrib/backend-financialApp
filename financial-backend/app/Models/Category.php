<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'type_code',
    ];
    public function recurring()
    {


        return $this->hasMany(Recurring::class);
    }
    public function fixedTransaction()
    {
        return $this->hasMany(FixedTransaction::class);
    }
}
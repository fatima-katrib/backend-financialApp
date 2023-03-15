<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',  
    ];
    public function fixedTransaction(){
        return $this->hasMany(FixedTransaction::class);
    }
}
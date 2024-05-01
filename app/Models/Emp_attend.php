<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emp_attend extends Model
{
    use HasFactory;
     protected $fillable = [
        'userId',
        'checkIn',
        'checkOut',
        // 'breakIn',
        // 'breakOut',
        'isBreakIn',
        'late',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'isBreakIn',

    ];

    // function getAtndn()
    // {
    //     return $this->hasMany('App\Models\User', 'id', 'user_id');
    // }
}

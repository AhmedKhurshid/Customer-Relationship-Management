<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'leaveTypeId',
        'userId',
        'sick',
        'annual',
        'casual',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',

    ];
}

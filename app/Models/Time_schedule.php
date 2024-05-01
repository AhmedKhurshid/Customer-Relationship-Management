<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Time_schedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'adminId',
        'type',
        'timeIn',
        'timeOut',
        'late',

    ];
}

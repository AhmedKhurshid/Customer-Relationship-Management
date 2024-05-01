<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_duration extends Model
{
    use HasFactory;
    protected $fillable = [
        'startTime',
        'endTime',
        'taskId',
    ];
}

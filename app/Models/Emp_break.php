<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emp_break extends Model
{
    use HasFactory;
    protected $fillable = [
        'userId',
        'empAttendanceId',
        'breakIn',
        'breakOut',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}

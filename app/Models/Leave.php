<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    function getleave()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
    protected $fillable = [
        'userId',
        'dateFrom',
        'dateTo',
        'reason',
        'leaveType',

    ];
    protected $hidden = [
        'adminId',
        'created_at',
        'updated_at'
    ];
}

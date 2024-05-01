<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'schedule',
        'email',
        'role',
        'status',
        'totalLeaves',
        'remainingLeaves',
        'otpCode',
        'address',
        'contactNo',
        'image',
        'imageStatus',
        'cnic',
        'famContactNo',
        'designation',
        'isEmployee',
        'deviceToken',
        'employeeId',
        'joinDate',
        'password',
    ];
    function getAtndn()
    {
        return $this->hasMany('App\Models\Emp_attend', 'userId', 'id');
    }
    // function getDesignation()
    // {
    //     return $this->belongsTo('App\Models\Designation', 'userId', 'id');
    // }
    // function getAtndn()
    // {
    //     return $this->hasOne('App\Models\Emp_attend', 'user_id', 'id');
    // }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // 'password',
        'remember_token',
        'otpCode',
        'deviceToken',
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}

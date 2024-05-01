<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oauth_access_token extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'client_id',
        'name',
        'scopes',
        'revoked',
        'das'
    ];
}

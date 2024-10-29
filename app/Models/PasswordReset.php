<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    // The table associated with the model
    protected $table = 'password_resets';

    // Disable timestamps management for this model
    public $timestamps = false;

    // The attributes that are mass assignable
    protected $fillable = ['email', 'token', 'created_at'];
}

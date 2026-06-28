<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable // এখানে 'Model' এর জায়গায় 'Authenticatable' হবে
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password'
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}

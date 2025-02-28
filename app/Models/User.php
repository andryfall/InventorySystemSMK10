<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Override the method to define the authentication field (username instead of email).
     *
     * @return string
     */
    public function getAuthIdentifierName(): string
    {
        return 'username';
    }

    /**
     * Override the method to retrieve the authentication identifier (username).
     *
     * @return string
     */
    public function getAuthIdentifier(): string
    {
        return $this->username;
    }

    /**
     * Override the method to define the authentication password field.
     *
     * @return string
     */
    public function getAuthPassword(): string
    {
        return $this->password;
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'last_login',
        'google2fa_secret',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

        /** 

     * Interact with the user's first name.

     *

     * @param  string  $value

     * @return \Illuminate\Database\Eloquent\Casts\Attribute

     */

     protected function google2faSecret(): Attribute
    {
        return new Attribute(
            get: fn($value) => $value ? decrypt($value) : null, // Only decrypt if the value is not null or empty
            set: fn($value) => $value ? encrypt($value) : null  // Only encrypt if the value is not null or empty
        );
    }

    /**
     * Define a one-to-many relationship with the Certificate model.
     * Returns all certificates for the user.
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Get the latest certificate for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestCertificate()
    {
        return $this->hasOne(Certificate::class)->latestOfMany();
    }
}

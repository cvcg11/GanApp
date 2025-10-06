<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    protected function password(): Attribute
    {
        return Attribute::set(function (?string $value) {
            if ($value === null || $value === '') {
                return $value;
            }

            $pepper = config('security.password_pepper_current');
            $algo   = config('security.password_pepper_algo', 'sha256');

            if (app()->environment('production') && empty($pepper)) {
                // En prod, no permitir crear/actualizar sin pepper
                throw new \RuntimeException('PASSWORD_PEPPER no está configurado.');
            }

            $peppered = hash_hmac($algo, $value, (string) $pepper);

            return Hash::make($peppered); // usa Argon2id por config/hashing.php
        });
    }
}

<?php
/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

namespace App\Core\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = SB_PREFIX.'users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    function getJWTCustomClaims(): array
    {
        return [
            'username' => $this->username,
            'companies' => $this->getCompanyIds(),
            'roles' => $this->getRoleIds(),
        ];
    }

    private function getCompanyIds(): array
    {
        $items = UserCompany::where('user_id', $this->getKey())->select('company_id')->get();
        if ($items === null) return [];
        $ret = [];
        foreach ($items as $item) {
            $ret[] = $item->company_id;
        }
        return $ret;
    }

    private function getRoleIds(): ?array
    {
        $items = UserRole::where('user_id', $this->getKey())->select('role_id')->get();
        if ($items === null) return [];
        $ret = [];
        foreach ($items as $item) {
            $ret[] = $item->role_id;
        }
        return $ret;
    }
}

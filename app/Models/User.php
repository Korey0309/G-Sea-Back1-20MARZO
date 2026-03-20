<?php

namespace App\Models;

use App\Models\Workspace;
use App\Models\Role;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function workspaces()
    {
        return $this->belongsToMany(
            Workspace::class,
            'workspace_user'
        )->withPivot('role_id')->withTimestamps();
    }

    public function currentWorkspace()
    {
        return $this->workspaces()->first();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'workspace_user');
    }
}
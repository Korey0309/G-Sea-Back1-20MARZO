<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $table = 'invitations';

    protected $fillable = [
        'email',
        'token',
        'workspace_id',
        'role_id',
        'used',
        'expires_at'
    ];

    protected $casts = [
        'used' => 'boolean',
        'expires_at' => 'datetime'
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
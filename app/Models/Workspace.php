<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Workspace extends Model
{
    protected $table = 'workspaces';

    protected $fillable = [
        'nombre',
        'slug',
        'plan_id',
        'owner_id'
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'workspace_user')
            ->withPivot('role_id')
            ->withTimestamps();
    }
}
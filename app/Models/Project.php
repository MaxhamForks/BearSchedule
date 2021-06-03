<?php

namespace App\Models;

use App\Models\Timeline\Group;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    const ROLES = ['SUBSCRIBER', 'ADMIN', 'EDITOR'];

    protected $fillable = ['name', 'share'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user')->withPivot(['role', 'updated_at', 'created_at']);
    }

    public function apikeys()
    {
        return $this->belongsToMany(ApiKey::class, 'project_api_key');
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function options()
    {
        return $this->hasMany(ProjectOption::class, 'project_id', 'id');
    }

    public function log()
    {
        return $this->hasMany(ProjectLog::class, 'project_id', 'id');
    }

    public function shareUrl()
    {
        if ($this->share === null) {
            return null;
        } else {
            return env('APP_URL') . '/share/' . str_replace('-', '', $this->share) . '/';
        }
    }

    public function option(string $option, ?string $field = null)
    {
        $row = $this->options()->where('option', $option)->first();
        if ($field === null) {
            return $row;
        }
        if ($row === null) {
            return null;
        }
        return $row->{$field};
    }

    public function attachUniqueUsers($ids, array $attributes = [], $touch = true)
    {
        $existing_ids = $this->users()->whereIn('users.id', $ids)->pluck('users.id');
        $this->users()->attach($ids->diff($existing_ids), $attributes, $touch);
    }
}

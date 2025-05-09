<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'parent_id',
    ];

    /**
     * Custom method for multi-tenant role creation.
     */
    public static function findOrCreateWithParent(string $name, $guardName = null, $parentId = 0)
    {
        $guardName = $guardName ?? config('auth.defaults.guard');

        $role = static::where('name', $name)
            ->where('guard_name', $guardName)
            ->where('parent_id', $parentId)
            ->first();

        if ($role) {
            return $role;
        }

        // Bypass Spatie's internal uniqueness check
        return (new static)->newQuery()->create([
            'name' => $name,
            'guard_name' => $guardName,
            'parent_id' => $parentId,
        ]);
    }
} 
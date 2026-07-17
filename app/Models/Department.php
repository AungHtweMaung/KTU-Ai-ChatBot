<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    /**
     * The majors that belong to this department.
     */
    public function majors(): HasMany
    {
        return $this->hasMany(Major::class);
    }

    /**
     * The teachers that belong to this department.
     */
    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }
}

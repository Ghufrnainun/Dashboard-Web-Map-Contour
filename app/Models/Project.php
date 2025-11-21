<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'surveyor_name', 'description'];

    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class);
    }
}

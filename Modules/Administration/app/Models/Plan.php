<?php

namespace Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Administration\Database\Factories\PlanFactory;

class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'description', 'price', 'duration', 'active'];

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'feature_plan')->withPivot('value')->withTimestamps();
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', 1);
    }
}

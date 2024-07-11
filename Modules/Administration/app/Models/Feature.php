<?php

namespace Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Administration\Database\Factories\FeatureFactory;

class Feature extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name','description','active'];


    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'feature_plan')->withPivot('value')->withTimestamps();
    }

}

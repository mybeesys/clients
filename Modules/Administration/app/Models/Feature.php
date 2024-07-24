<?php

namespace Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LucasDotVin\Soulbscription\Models\Feature as ModelsFeature;
use Modules\Administration\Database\Factories\FeatureFactory;

class Feature extends ModelsFeature
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'consumable',
        'name',
        'periodicity_type',
        'periodicity',
        'quota',
        'postpaid', 'description', 'active'
    ];

    public function plans()
    {
        return $this->belongsToMany(config('soulbscription.models.plan'))
            ->using(config('soulbscription.models.feature_plan'))
            ->withPivot(['value','charges']);
    }
}

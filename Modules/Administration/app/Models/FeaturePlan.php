<?php

namespace Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LucasDotVin\Soulbscription\Models\FeaturePlan as ModelsFeaturePlan;
use Modules\Administration\Database\Factories\FeaturePlanFactory;

class FeaturePlan extends ModelsFeaturePlan
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'feature_plan';

    protected $fillable = ['plan_id', 'feature_id', 'value','charges'];
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}

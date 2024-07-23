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
    protected $table = 'payments';

    protected $fillable = ['plan_id', 'feature_id', 'value'];
}

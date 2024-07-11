<?php

namespace Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Administration\Database\Factories\FeaturePlanFactory;

class FeaturePlan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'plan_feature';

    protected $fillable = ['plan_id', 'feature_id', 'value'];
}

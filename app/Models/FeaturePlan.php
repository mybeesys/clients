<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use LucasDotVin\Soulbscription\Models\FeaturePlan as ModelsFeaturePlan;


class FeaturePlan extends ModelsFeaturePlan
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'feature_plan';

    protected $fillable = ['plan_id', 'feature_id', 'amount', 'charges'];

}

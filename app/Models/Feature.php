<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LucasDotVin\Soulbscription\Models\Feature as ModelsFeature;


class Feature extends ModelsFeature
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'features';

    protected $fillable = [
        'consumable',
        'name',
        'periodicity_type',
        'periodicity',
        'quota',
        'postpaid',
        'description',
        'active'
    ];

    public function plans()
    {
        return $this->belongsToMany(config('soulbscription.models.plan'))
            ->using(config('soulbscription.models.feature_plan'))
            ->withPivot(['amount', 'charges'])->withTimestamps();
    }

    public function feature_plans()
    {
        return $this->hasMany(FeaturePlan::class);
    }

    public function getTranslatedNameAttribute()
    {
        $name = app()->getLocale() == 'ar' ? 'name_ar' : 'name';
        return ($this->{$name} ?? $this->name_ar) ?? $this->name;
    }

}

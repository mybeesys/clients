<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralProgramSetting extends Model
{
    protected $fillable = [
        'is_enabled',
        'default_points_per_conversion',
        'points_by_plan',
        'promotional_template_ar',
        'promotional_template_en',
        'monthly_points_cap',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'points_by_plan' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'is_enabled' => true,
            'default_points_per_conversion' => 10,
            'promotional_template_ar' => "جرّب نظام My Bee لإدارة أعمالك باحترافية!\nسجّل عبر رابطي واحصل على أفضل تجربة:\n{link}",
            'promotional_template_en' => "Try My Bee to run your business professionally!\nRegister using my link:\n{link}",
        ]);
    }
}

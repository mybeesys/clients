<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public static function defaultId(): ?int
    {
        $id = static::query()->where('iso_code', 'SA')->value('id');

        if ($id) {
            return (int) $id;
        }

        $id = static::query()->where('name_en', 'Saudi Arabia')->value('id');

        return $id ? (int) $id : null;
    }
}

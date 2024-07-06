<?php

namespace Modules\Company\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Company\Database\Factories\CompanyFactory;

class Company extends Model
{
    use HasFactory;

    protected $table = "companies";
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['logo', 'tax_name', 'ceo_name', 'country_id', 'description', 'name', 'user_id', 'name'];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

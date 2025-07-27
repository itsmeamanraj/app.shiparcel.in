<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierCompany extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status'];

    public function userSelections()
    {
        return $this->hasMany(UserCourierWeightSlab::class);
    }
    
    public function weightSlabs()
    {
        return $this->hasMany(CourierWeightSlab::class, 'courier_company_id');
    }

    public function airRates()
    {
        return $this->hasMany(UserAIRCourierRate::class, 'courier_company_id');
    }

    public function surfaceRates()
    {
        return $this->hasMany(UserSurfaceCourierRate::class, 'courier_company_id');
    }

}

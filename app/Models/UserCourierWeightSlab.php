<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserCourierWeightSlab extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'courier_company_id', 'courier_status', 'express_type_air', 'express_type_surface', 'air_weight_slab_ids', 'surface_weight_slab_ids', 'is_enabled'];

    public function company(): HasOne
    {
        return $this->hasOne(CourierCompany::class, 'id', 'courier_company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courierCompany()
    {
        return $this->belongsTo(CourierCompany::class, 'courier_company_id');
    }
}

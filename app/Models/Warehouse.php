<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    protected $table = 'shiparcel_warehouses';
    protected $fillable = [
        'address_title',
        'sender_name',
        'full_address',
        'phone',
        'pincode',
        'state',
        'city',
        'user_id',
        'pick_address_id'
    ];
}

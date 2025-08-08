<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PincodeCourier extends Model
{
    protected $table = 'pincode_couriers'; // <- add this if necessary

    protected $fillable = ['pincode', 'courier_company_id']; // optional, if using mass assignment
}

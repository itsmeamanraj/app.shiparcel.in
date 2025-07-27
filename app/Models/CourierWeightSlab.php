<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierWeightSlab extends Model
{
    use HasFactory;

    protected $fillable = ['weight', 'status'];

}

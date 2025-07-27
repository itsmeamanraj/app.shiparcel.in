<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentStatus extends Model
{
    use HasFactory;

    protected $table = 'shipment_statuses';

    protected $fillable = [
        'status',
        'status_code'
    ];
}

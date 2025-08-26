<?php

namespace Database\Seeders;

use App\Models\ShipmentStatus;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShipmentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $companies = [
            ['status' => 221, 'status_code' => 'Shipment Created', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 222, 'status_code' => 'Pickup Scheduled / Manifest', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 223, 'status_code' => 'In Transit', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 224, 'status_code' => 'Out For Delivery', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 225, 'status_code' => 'RTO Initiated', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 226, 'status_code' => 'Delivered', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 227, 'status_code' => 'Failed / Undelivered', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 228, 'status_code' => 'Delayed', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 229, 'status_code' => 'Cancelled / Pickup Cancelled', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 230, 'status_code' => 'Lost', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 231, 'status_code' => 'Out For Pickup', 'created_at' => $now, 'updated_at' => $now],
        ];

        ShipmentStatus::insert($companies);
    }
}

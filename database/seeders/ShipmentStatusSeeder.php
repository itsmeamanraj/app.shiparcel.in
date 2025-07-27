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
        $companies = [
            ['status' => 221, 'status_code' => 'Booked', 'created_at' => Carbon::now()],
            ['status' => 222, 'status_code' => 'Manifest', 'created_at' => Carbon::now()],
            ['status' => 223, 'status_code' => 'N/A', 'created_at' => Carbon::now()],
            ['status' => 224, 'status_code' => 'N/A', 'created_at' => Carbon::now()],
            ['status' => 225, 'status_code' => 'N/A', 'created_at' => Carbon::now()],
            ['status' => 226, 'status_code' => 'Delivered', 'created_at' => Carbon::now()],
            ['status' => 227, 'status_code' => 'Failed', 'created_at' => Carbon::now()],
            ['status' => 228, 'status_code' => 'N/A', 'created_at' => Carbon::now()],
            ['status' => 229, 'status_code' => 'Cancelled', 'created_at' => Carbon::now()],
        ];

        ShipmentStatus::insert($companies);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\XpressbeesApiService;
use App\Helpers\EkartApiService;

class UpdateShipmentTracking extends Command
{
    protected $signature = 'app:update-shipment-tracking';
    protected $description = 'Automatically fetch and save shipment tracking for all orders';

    public function handle()
    {
        $this->info("Starting shipment tracking update...");

        $statusMapping = [
            // Ekart statuses
            'shipment_created'   => 221,
            'pickup_scheduled'   => 222,
            'in_transit'         => 223,
            'out_for_delivery'   => 224,
            'rto_initiated'      => 225,
            'delivered'          => 226,
            'failed'             => 227,
            'delayed'            => 228,
            'cancelled'          => 229,
            'lost'               => 230,
            'out_for_pickup'     => 231,
            'drc'                => 222,
            'pickup_cancelled'   => 229, 

            'pkp'                => 222,
            'int'                => 223,
            'ofp'                => 224,
            'ofu'                => 231,
            'rto'                => 225,
            'rtod'               => 225,
            'del'                => 226,
            'und'                => 227,
            'can'                => 229,
        ];

        // Convert mapping keys to lowercase for safety
        $statusMapping = array_change_key_case($statusMapping, CASE_LOWER);

        $orders = DB::table('shiparcel_orders')->whereNotNull('awb_number')->get();
        $this->info("Fetched " . $orders->count() . " orders with AWB.");

        $ekartAwbs = $orders->where('courier_name', 'Ekart')->pluck('awb_number')->toArray();
        $xbeesAwbs = $orders->where('courier_name', 'XpressBees')->pluck('awb_number')->toArray();

        $this->info("Ekart AWBs: " . count($ekartAwbs));
        $this->info("XpressBees AWBs: " . count($xbeesAwbs));

        $ekartData = [];
        $xbeesData = [];

        // Ekart tracking
        foreach ($ekartAwbs as $awb) {
            $this->line("Fetching Ekart AWB: $awb");
            try {
                $result = EkartApiService::trackShipment([$awb]);
                Log::info("Ekart API raw response", ['awb' => $awb, 'response' => $result]);
                $data = $result[$awb] ?? null;

                if (!$data || empty($data['history'])) {
                    $ekartData[$awb] = ['error' => 'No Ekart history data'];
                    $this->warn("No Ekart history data for AWB $awb");
                    continue;
                }

                $latestHistory = end($data['history']);
                $apiStatusRaw = $latestHistory['status'] ?? null;
                $statusDate = $latestHistory['event_date_iso8601'] ?? null;

        
                $apiStatus = strtolower($apiStatusRaw);
                $systemStatusCode = $statusMapping[$apiStatus] ?? null;

                $ekartData[$awb] = [
                    'courier'      => 'Ekart',
                    'status'       => $apiStatusRaw,
                    'status_code'  => $systemStatusCode,
                    'status_date'  => $statusDate,
                    'history'      => $data['history'],
                ];

                $this->info("Ekart AWB $awb status: $apiStatusRaw => $systemStatusCode at $statusDate");
            } catch (\Exception $e) {
                $this->error("Ekart API error for AWB $awb: " . $e->getMessage());
                Log::error("Ekart API exception", ['awb' => $awb, 'error' => $e->getMessage()]);
            }
        }

        // XpressBees tracking
        foreach ($xbeesAwbs as $awb) {
            $this->line("Fetching XpressBees AWB: $awb");
            try {
                $result = XpressbeesApiService::trackShipments([$awb]);
                Log::info("XpressBees API raw response", ['awb' => $awb, 'response' => $result]);
                $data = $result[$awb] ?? ['error' => 'No data returned'];

                if (!isset($data['error']) && !empty($data['ShipmentLogDetails'])) {
                    $latestHistory = $data['ShipmentLogDetails'][0];
                    $apiStatusRaw = $latestHistory['ShipmentStatus'] ?? null;
                    $statusDate = $latestHistory['ShipmentStatusDateTime'] ?? null;

                    $apiStatus = strtolower($apiStatusRaw);
                    $systemStatusCode = $statusMapping[$apiStatus] ?? null;

                    $xbeesData[$awb] = [
                        'courier'              => 'XpressBees',
                        'status'               => $apiStatusRaw,
                        'status_code'          => $systemStatusCode,
                        'status_date'          => $statusDate,
                        'ShipmentLogDetails'   => $data['ShipmentLogDetails'],
                    ];

                    $this->info("XpressBees AWB $awb status: $apiStatusRaw => $systemStatusCode at $statusDate");
                } else {
                    $xbeesData[$awb] = $data;
                    $this->warn("No XpressBees data for AWB $awb");
                }
            } catch (\Exception $e) {
                $this->error("XpressBees API error for AWB $awb: " . $e->getMessage());
                Log::error("XpressBees API exception", ['awb' => $awb, 'error' => $e->getMessage()]);
            }
        }

        // Merge all data
        $allTrackingData = $ekartData + $xbeesData;
        $this->info("Total shipments to process: " . count($allTrackingData));

        foreach ($allTrackingData as $awb => $shipmentData) {
            if (isset($shipmentData['error'])) {
                $this->warn("Skipping AWB $awb due to error: " . $shipmentData['error']);
                continue;
            }

            $last = DB::table('shipments_tracking')
                ->where('awb_number', $awb)
                ->orderBy('id', 'desc')
                ->first();

            if (!$last || $last->status_code != $shipmentData['status_code']) {
                DB::table('shipments_tracking')->insert([
                    'awb_number'    => $awb,
                    'status_code'   => $shipmentData['status_code'],
                    'status_date'   => $shipmentData['status_date'] ? date('Y-m-d H:i:s', strtotime($shipmentData['status_date'])) : now(),
                    'tracking_data' => json_encode($shipmentData),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                $this->info("Inserted/Updated AWB $awb with status {$shipmentData['status_code']}");
            } else {
                $this->info("No update required for AWB $awb");
            }
        }

        $this->info('Shipment tracking update completed successfully.');
    }
}

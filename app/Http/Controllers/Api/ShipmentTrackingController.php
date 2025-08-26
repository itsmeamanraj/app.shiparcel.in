<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentTrackingController extends Controller
{
public function getTracking(Request $request)
{
    // Get input based on which query param exists
    $input = $request->input('order') 
            ?? $request->input('awb') 
            ?? $request->input('mobile'); 

    $input = trim((string) $input);

    if (!$input) {
        return response()->json([
            'status'  => 'error',
            'message' => 'No input provided.',
        ], 400);
    }

    // Find order by AWB / Order ID / Mobile
    $order = DB::table('shiparcel_orders')
        ->where('awb_number', $input)
        ->orWhere('client_order_id', $input)
        ->orWhere('consignee_mobile', $input)
        ->first();

    if (!$order) {
        return response()->json([
            'status'  => 'error',
            'message' => 'No order / AWB found for given input: ' . $input,
        ], 404);
    }

    // Latest tracking record
    $tracking = DB::table('shipments_tracking')
        ->where('awb_number', $order->awb_number)
        ->orderByDesc('id')
        ->first();

    if (!$tracking) {
        return response()->json([
            'status'  => 'error',
            'message' => 'No tracking data found for AWB: ' . $order->awb_number,
        ], 404);
    }

    // Status name from mapping table
    $status = DB::table('shipment_statuses')
        ->where('status', $tracking->status_code)
        ->value('status_code');

    return response()->json([
        'status' => 'success',
        'search_input' => $input,
        'awb'    => $order->awb_number,
        'data'   => [
            'order_id'      => $order->id,
            'consignee_name' => $order->consignee_name,
            'awb_number'    => $tracking->awb_number,
            'status_code'   => strtolower($tracking->status_code),
            'status_name'   => $status ?? null,
            'status_date'   => $tracking->status_date,
            'tracking_data' => $tracking->tracking_data,
            'created_at'    => $tracking->created_at,
            'updated_at'    => $tracking->updated_at,
        ],
    ]);
}


}

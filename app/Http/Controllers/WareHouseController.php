<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateWarehouseRequest;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WareHouseController extends Controller
{
    public function show()
    {
        return view('users.warehouse.create');
    }

    public function create(CreateWarehouseRequest $request)
    {
        $user = Auth::user();

        $data = [
            'address_title' => $request->address_title,
            'sender_name' => $request->sender_name,
            'full_address' => $request->full_address,
            'phone' => $request->phone,
            'pincode' => $request->pincode,
            'city' => $request->city,
            'state' => $request->state,
            'user_id' => $user->id
        ];

        Log::info('data', $data);

        try {
            $warehouse = Warehouse::create($data);

            $user->logActivity($user, 'Warehouse created successfully', 'warehouse_created');

            return redirect()->back()->with('success', 'Warehouse created successfully!');
        } catch (Exception $e) {
            $user->logActivity($user, 'An error occurred while creating warehouse', 'warehouse_created');

            Log::error('Exception:', ['message' => $e->getMessage()]);

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

}

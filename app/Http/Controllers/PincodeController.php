<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\PincodeCourier;
use App\Models\CourierCompany;

use Illuminate\Http\Request;

class PincodeController extends Controller
{
    public function view()
    {      $couriers = CourierCompany::all();
        return view('users.pincode.index', compact('couriers'));
    }
    public function checkPincode(Request $request)
    {
$request->validate([
        'pincodes' => 'required|string',
        'courier_id' => 'required|exists:courier_companies,id',
    ]);

    $inputPincodes = array_map('trim', explode(',', $request->pincodes));
    $courierId = $request->courier_id;

    $serviceable = PincodeCourier::where('courier_id', $courierId)
        ->whereIn('pincode', $inputPincodes)
        ->pluck('pincode')
        ->toArray();

    $nonServiceable = array_diff($inputPincodes, $serviceable);

    return redirect()->route('pincode.form')->with('result', [
        'serviceable' => $serviceable,
        'non_serviceable' => $nonServiceable,
    ]);
}
}
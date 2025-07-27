<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateWalletRequest;
use App\Models\CourierCompany;
use App\Models\CourierWeightSlab;
use App\Models\UserAIRCourierRate;
use App\Models\UserCourierWeightSlab;
use App\Models\UserSurfaceCourierRate;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Show Wallet Form
     */
    public function show(Request $request)
    {
        $mode = $request->input('mode', 'air');

        $transactions = WalletTransaction::where('user_id', Auth::id())
            ->orderBy('issued_date', 'desc')
            ->get();

        $userSlabs = UserCourierWeightSlab::where('user_id', Auth::id())->with('courierCompany')->get();

        $weightSlabs = collect();
        $rates = collect();

        if ($userSlabs->isEmpty()) {
            return view('users.wallet.show', compact('transactions', 'weightSlabs', 'rates', 'mode'))
                ->with('error', 'No courier slabs found.');
        }

        foreach ($userSlabs as $slab) {
            $slabIds = ($mode == 'air') ? json_decode($slab->air_weight_slab_ids, true) ?? []
                : json_decode($slab->surface_weight_slab_ids, true) ?? [];

            if (!empty($slabIds)) {
                $weightSlabs = $weightSlabs->merge(CourierWeightSlab::whereIn('id', $slabIds)->get());
            }

            $rateModel = ($mode == 'air') ? new UserAIRCourierRate : new UserSurfaceCourierRate;
            $rates = $rates->merge($rateModel::where([
                'user_id' => Auth::id(),
                'mode' => 'Forward'
            ])->whereIn('courier_weight_slab_id', $slabIds)->get());
        }

        return view('users.wallet.show', compact('transactions', 'userSlabs', 'weightSlabs', 'rates', 'mode'));
    }

    public function fetchRates(Request $request)
    {
        $mode = $request->input('mode', 'air');
        $weightSlab = $request->input('weight_slab');
        $shippingType = $request->input('shipping_type');

        $userSlabs = UserCourierWeightSlab::where('user_id', Auth::id())->with('courierCompany')->get();

        if ($userSlabs->isEmpty()) {
            return response()->json(['error' => 'No courier slabs found.'], 404);
        }

        $selectedSlabs = collect();
        foreach ($userSlabs as $slab) {
            $slabIds = ($mode == 'air') ? json_decode($slab->air_weight_slab_ids, true) ?? []
                : json_decode($slab->surface_weight_slab_ids, true) ?? [];

            if (is_array($slabIds)) {
                $selectedSlabs = $selectedSlabs->merge($slabIds);
            }
        }

        $rateModel = ($mode == 'air') ? new UserAIRCourierRate : new UserSurfaceCourierRate;

        // Build the query dynamically
        $query = $rateModel::where('user_id', Auth::id())
            ->where('mode', $shippingType)
            ->whereIn('courier_weight_slab_id', $selectedSlabs);

        // Apply weight slab filter only if it exists
        if (!empty($weightSlab)) {
            $query->where('courier_weight_slab_id', $weightSlab);
        }

        $rates = $query->get();

        return view('users.wallet.rate_table', compact('rates', 'mode'))->render();
    }


    /**
     * Create Wallet Amount
     */

    public function store(CreateWalletRequest $request)
    {
        $userId = Auth::id();

        DB::transaction(function () use ($userId, $request) {
            // Create a transaction record
            WalletTransaction::create([
                'user_id' => $userId,
                'invoice_number' => 'INV-' . strtoupper(uniqid()),
                'name' => 'Wallet Top-Up',
                'issued_date' => now(),
                'amount' => $request->amount,
                'status' => 103
            ]);
        });

        return redirect()->back()->with('success', __('message.create_wallet'));
    }
}

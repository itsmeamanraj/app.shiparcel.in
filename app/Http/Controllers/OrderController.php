<?php

namespace App\Http\Controllers;

use App\Helpers\EkartApiService;
use App\Helpers\XpressbeesApiService;

use App\Helpers\ParcelxHelper;
use App\Http\Requests\CancelOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Warehouse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class OrderController extends Controller
{
    /**
     * Show Order Form
     */
    public function show()
    {
        $user = Auth::user();
        $chargeableAmount = $user->chargeable_amount;

        $totalAmount = Wallet::where('user_id', $user->id)->first();
        if (!$totalAmount) {
            session()->flash('error', 'Insufficient Balance Please Recharge Wallet!!');
        } elseif ($totalAmount->amount < $chargeableAmount) {
            session()->flash('error', 'Insufficient Balance Please Recharge Wallet!!');
        }

        $data['warehouses'] = Warehouse::where(['status' => 1, 'user_id' => $user->id])->get();
        $data['couriers'] = DB::table('courier_companies')->where('status', 1)->get();

        return view('users.orders.create', $data);
    }

    /**
     * Save Order Data
     */
    public function create(Request $request)
    {   //dd($_POST);
        $user = Auth::user();
        $chargeableAmount = $user->chargeable_amount;

        // Get the wallet record for the user
        $wallet = Wallet::where('user_id', $user->id)->first();
        $courier_id = (int) $request->input('selected_courier');

        // Check for no wallet, insufficient funds, or negative balance
        if (!$wallet || $wallet->amount <= 0 || $wallet->amount < $chargeableAmount) {
            session()->flash('error', 'Insufficient Balance. Please recharge your wallet!');
            return redirect()->back();
        }


        if (!$request->has('product_name') || empty($request->product_name)) {
            session()->flash('error', 'The products field is required.');
            return redirect()->back();
        }

        // Always fetch pickup warehouse (source address)
        $pickupWarehouse = Warehouse::where([
            'status' => 1,
            'user_id' => $user->id,
            'id' => $request->pickup_address
        ])->first();

        // If return address is enabled, fetch it; else, we'll use pickupWarehouse again
        $returnWarehouse = null;
        if ($request->is_return_address === 'on') {
            $returnWarehouse = Warehouse::where([
                'status' => 1,
                'user_id' => $user->id,
                'id' => $request->return_address
            ])->first();
        }

        if ($courier_id == 1) {

            $destinationAddress = [
                'first_name' => $request->consignee_name,
                'address_line1' => $request->consignee_address1,
                'address_line2' => $request->consignee_address2 ?? '',
                'pincode' => $request->consignee_pincode,
                'city' => $request->consignee_city,
                'state' => $request->consignee_state,
                'primary_contact_number' => $request->consignee_mobile,
                'email_id' => $request->consignee_emailid ?? ''
            ];

            // Destination Address (using pickupWarehouse values)
            $sourceAddress = [
                'first_name' => $pickupWarehouse->sender_name ?? '',
                'address_line1' => $pickupWarehouse->full_address ?? '',
                'address_line2' => $pickupWarehouse->address_title ?? '',
                'pincode' => $pickupWarehouse->pincode ?? '',
                'city' => $request->source_city ?? $destinationAddress['city'],
                'state' => $request->source_state ?? $destinationAddress['state'],
                'primary_contact_number' => $pickupWarehouse->phone ?? '',
                'email_id' => '',
            ];

            // Return Address: if return is enabled, use return warehouse; else, use destinationAddress
            $returnAddress = $request->is_return_address === 'on' ? [
                'first_name' => $returnWarehouse->sender_name ?? '',
                'address_line1' => $returnWarehouse->full_address ?? '',
                'address_line2' => $returnWarehouse->address_title ?? '',
                'pincode' => $returnWarehouse->pincode ?? '',
                'city' => $request->source_city ?? $destinationAddress['city'],
                'state' => $request->source_state ?? $destinationAddress['state'],
                'primary_contact_number' => $returnWarehouse->phone ?? '',
                'email_id' => '',
            ] : $sourceAddress;

            //generate tracking id

            try {
                $base = 1000000001;
                $maxOffset = 999999;
                $randomNumber = $base + rand(0, $maxOffset);

                $paymentMode = $request->payment_mode ?? 'prepaid';
                $modeCode = ($paymentMode === 'Cod') ? 'C' : 'P';
                $Tracking_id = 'HRD' . $modeCode . $randomNumber;

                $apiData = [
                    'client_name' => 'HRD',
                    'goods_category' => 'ESSENTIAL',
                    'services' => [
                        [
                            'service_code' => 'ECONOMY',
                            'service_details' => [
                                [
                                    'service_leg' => 'FORWARD',
                                    'service_data' => [
                                        'service_types' => [
                                            ['name' => 'regional_handover', 'value' => 'true'],
                                            ['name' => 'delayed_dispatch', 'value' => 'false'],
                                        ],
                                        'vendor_name' => 'Ekart',
                                        'amount_to_collect' => (string) ($request->total_amount ?? '0'),
                                        'dispatch_date' => now()->format('Y-m-d H:i:s'),
                                        'customer_promise_date' => null,
                                        'delivery_type' => 'SMALL',
                                        'source' => ['address' => $sourceAddress],
                                        'destination' => ['address' => $destinationAddress],
                                        'return_location' => ['address' => $returnAddress ?? $sourceAddress],
                                    ],
                                    'shipment' => [
                                        'client_reference_id' => $Tracking_id,
                                        'tracking_id' => $Tracking_id,
                                        'shipment_value' => (float) ($request->total_amount ?? 0),
                                        'shipment_dimensions' => [
                                            'length' => ['value' => (float) ($request->shipment_length[0] ?? 0.5)],
                                            'breadth' => ['value' => (float) ($request->shipment_width[0] ?? 0.5)],
                                            'height' => ['value' => (float) ($request->shipment_height[0] ?? 0.5)],
                                            'weight' => ['value' => (float) ($request->shipment_weight[0] ?? 0.5)],
                                        ],
                                        'return_label_desc_1' => null,
                                        'return_label_desc_2' => null,
                                        'shipment_items' => [],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                $productDataForDb = [];
                // Add shipment items
                if ($request->has('product_name') && is_array($request->product_name)) {
                    foreach ($request->product_name as $index => $product_name) {
                        $apiData['services'][0]['service_details'][0]['shipment']['shipment_items'][] = [
                            'product_id' => $request->product_sku[$index] ?? '',
                            'category' => $request->product_category[$index] ?? 'Uncategorized',
                            'product_title' => $product_name,
                            'quantity' => (int) ($request->product_quantity[$index] ?? 1),
                            'cost' => [
                                'total_sale_value' => (float) ($request->product_value[$index] ?? 0),
                                'total_tax_value' => (float) ($request->product_taxper[$index] ?? 0),
                                'tax_breakup' => [
                                    'cgst' => 0,
                                    'sgst' => 0,
                                    'igst' => 0
                                ]
                            ],
                            'seller_details' => [
                                'seller_reg_name' => 'shiparcel',
                                'gstin_id' => null
                            ],
                            'hsn' => $request->product_sku[$index] ?? '',
                            'ern' => null,
                            'discount' => null,
                            'item_attributes' => [
                                ['name' => 'order_id', 'value' => $request->order_id ?? ''],
                                ['name' => 'invoice_id', 'value' => $Tracking_id ?? ''],
                            ],
                            'handling_attributes' => [
                                ['name' => 'isFragile', 'value' => 'false'],
                                ['name' => 'isDangerous', 'value' => 'false'],
                            ]
                        ];

                        $productDataForDb[] = [
                            'product_sku' => $request->product_sku[$index] ?? '',
                            'product_name' => $product_name,
                            'product_value' => (float) ($request->product_value[$index] ?? 0),
                            'product_hsnsac' => $request->product_sku[$index] ?? '', // Assuming SKU is same as HSN/SAC
                            'product_taxper' => (float) ($request->product_taxper[$index] ?? 0),
                            'product_category' => $request->product_category[$index] ?? 'Uncategorized',
                            'product_quantity' => (int) ($request->product_quantity[$index] ?? 1),
                            'product_description' => '', // If not available in form, leave blank or map if exists
                        ];
                    }
                }
                Log::info('Ekart API Request:', $apiData);

                $url = 'https://api.ekartlogistics.com/v2/shipments/create';
                $response = EkartApiService::sendRequest($url, $apiData);

                if ($response->successful()) {
                    // dd($apiData);
                    $responseData = $response->json();
                    Log::info('API Response:', $responseData);
                    $response = $responseData['response'][0] ?? [];

                    if (!$response['status']) {
                        session()->flash('error', $responseData['responsemsg'][0]);
                        return redirect()->back();
                    }
                    // dd($responseData);

                    $dbData = $apiData;
                    $dbData['shipment_width'] = $request->shipment_width[0] ?? '0.5';
                    $dbData['shipment_height'] = $request->shipment_height[0] ?? '0.5';
                    $dbData['shipment_length'] = $request->shipment_length[0] ?? '0.5';
                    $dbData['shipment_weight'] = $request->shipment_weight[0] ?? '0.5';
                    $dbData['order_amount'] = (float) ($request->total_amount ?? 0);
                    $dbData['payment_mode'] = $request->payment_mode;

                    $dbData['client_order_id']       = $request->order_id;
                    $dbData['consignee_emailid']     = $request->consignee_emailid ?? '';
                    $dbData['consignee_pincode']     = $request->consignee_pincode;
                    $dbData['consignee_mobile']      = $request->consignee_mobile;
                    $dbData['consignee_phone']       = $request->consignee_phone ?? '';
                    $dbData['consignee_address1']    = $request->consignee_address1;
                    $dbData['consignee_address2']    = $request->consignee_address2 ?? '';
                    $dbData['consignee_name']        = $request->consignee_name;


                    // $response = $responseData['response'][0] ?? [];

                    $dbData['ekart_tracking_id'] = $response['tracking_id'] ?? null;
                    $dbData['ekart_shipment_payment_link'] = $response['shipment_payment_link'] ?? null;
                    $dbData['ekart_api_status'] = $response['status'] ?? null;
                    $dbData['ekart_api_status_code'] = $response['status_code'] ?? null;
                    $dbData['ekart_is_parked'] = $response['is_parked'] ?? null;
                    $dbData['ekart_request_id'] = $responseData['request_id'] ?? null;

                    // Optional: User & status fields
                    $dbData['user_id'] = $user->id;
                    $dbData['status'] = 221;


                    $dbData['awb_number'] = $Tracking_id ?? null;
                    $dbData['order_number'] = $request->order_id ?? null;
                    // $dbData['job_id'] = $responseData['data']['job_id'] ?? null;
                    // $dbData['lrnum'] = $responseData['data']['lrnum'] ?? '';
                    // $dbData['waybills_num_json'] = $responseData['data']['waybills_num_json'] ?? null;
                    // $dbData['lable_data'] = $responseData['data']['lable_data'] ?? null;
                    // $dbData['routing_code'] = $responseData['data']['routing_code'] ?? null;
                    $dbData['partner_display_name'] = 'Ekart';
                    $dbData['pick_address_id'] = $request->pickup_address;
                    $dbData['return_address_id'] = $request->return_address ?? $request->pickup_address;
                    $dbData['courier_name'] = 'Ekart';
                    $dbData['user_id'] = $user->id;
                    $dbData['status'] = 221;


                    $order = Order::create($dbData);


                    foreach ($productDataForDb as $product) {
                        $product['order_id'] = $order->id;
                        Product::create($product);
                    }

                    // Deduct wallet charge
                    // $chargeableAmount;
                    // $totalAmount = Wallet::where('user_id', $user->id)->first();
                    // $updatedAmount = $totalAmount->amount - $chargeableAmount;

                    // $totalAmount->update([
                    //     'amount' => $updatedAmount
                    // ]);

                    $totalAmount = Wallet::where('user_id', $user->id)->first();

                    if ($totalAmount) {
                        $updatedAmount = $totalAmount->amount - $chargeableAmount;
                        $totalAmount->update(['amount' => $updatedAmount]);
                    } else {
                        Wallet::create([
                            'user_id' => $user->id,
                            'amount' => -$chargeableAmount
                        ]);
                    }



                    $walletTransactions = WalletTransaction::where([
                        'user_id' => Auth::id(),
                        'status' => 101
                    ])->get();

                    // update status after add amount or update amount
                    foreach ($walletTransactions as $transaction) {
                        $transaction->update(['status' => 102]);
                    }
                    // $user->logActivity($user, 'Order created successfully', 'order_created');

                    session()->flash('success', 'Order placed successfully! Tracking Id: ' . $response['tracking_id']);
                    return redirect()->back();
                } else {
                    $responseBody = $response->json();
                    Log::error('API Error:', ['response' => $responseBody]);

                    session()->flash('error', $responseBody['responsemsg'] ?? 'Unknown error occurred');
                    return redirect()->back();
                }
            } catch (Exception $e) {
                Log::error('Ekart Exception:', ['message' => $e->getMessage()]);
                session()->flash('error', 'An error occurred: ' . $e->getMessage());
                return redirect()->back();
            }
        }else if($courier_id == 2) { // XpressBees
            try {
                $xpressPayload = [
                    "BusinessAccountName" => "ANKUR PARSHAR",
                    "OrderNo" => $request->order_id,
                    "SubOrderNo" => $request->order_id . '-1',
                    "OrderType" => ($request->payment_mode == 'Cod') ? 'COD' : 'PrePaid',
                    "CollectibleAmount" => ($request->payment_mode == 'Cod') ? (string)$request->total_amount : "0",
                    "DeclaredValue" => (string)($request->total_amount ?? "0"),
                    "PickupType" => "Vendor",
                    "Quantity" => count($request->product_name) > 0 ? count($request->product_name) : 1,
                    "ServiceType" => "SD",

                    "DropDetails" => [
                        "Addresses" => [[
                            "Address" => $request->consignee_address1,
                            "City" => $request->consignee_city ?? '',
                            "EmailID" => $request->consignee_emailid ?? '',
                            "Name" => $request->consignee_name,
                            "PinCode" => $request->consignee_pincode,
                            "State" => $request->consignee_state ?? '',
                            "Type" => "Primary",
                        ]],
                        "ContactDetails" => [[
                            "PhoneNo" => $request->consignee_mobile,
                            "Type" => "Primary",
                        ]],
                    ],

                    "PickupDetails" => [
                        "Addresses" => [[
                            "Address" => $pickupWarehouse->full_address ?? '',
                            "City" => $pickupWarehouse->city ?? '',
                            "EmailID" => '',
                            "Name" => $pickupWarehouse->sender_name ?? '',
                            "PinCode" => $pickupWarehouse->pincode ?? '',
                            "State" => $pickupWarehouse->state ?? '',
                            "Type" => "Primary",
                        ]],
                        "ContactDetails" => [[
                            "PhoneNo" => $pickupWarehouse->phone ?? '',
                            "Type" => "Primary",
                        ]],
                        "PickupVendorCode" => env('XPRESSBEES_VENDOR_CODE', 'YOUR_VENDOR_CODE')
                    ],

                    "RTODetails" => [
                        "Addresses" => [[
                            "Address" => ($request->is_return_address === 'on' ? $returnWarehouse->full_address : $pickupWarehouse->full_address) ?? '',
                            "City" => ($request->is_return_address === 'on' ? $returnWarehouse->city : $pickupWarehouse->city) ?? '',
                            "EmailID" => '',
                            "Name" => ($request->is_return_address === 'on' ? $returnWarehouse->sender_name : $pickupWarehouse->sender_name) ?? '',
                            "PinCode" => ($request->is_return_address === 'on' ? $returnWarehouse->pincode : $pickupWarehouse->pincode) ?? '',
                            "State" => ($request->is_return_address === 'on' ? $returnWarehouse->state : $pickupWarehouse->pincode) ?? '',
                            "Type" => "Primary",
                        ]],
                        "ContactDetails" => [[
                            "PhoneNo" => ($request->is_return_address === 'on' ? $returnWarehouse->phone : $pickupWarehouse->phone) ?? '',
                            "Type" => "Primary",
                        ]],
                    ],

                    "ManifestID" => $request->order_id,
                    "IsEssential" => "false",
                    "IsSecondaryPacking" => "false",

                    "PackageDetails" => [
                        "Dimensions" => [
                            "Height" => (string)($request->shipment_height[0] ?? "1"),
                            "Length" => (string)($request->shipment_length[0] ?? "1"),
                            "Width" => (string)($request->shipment_width[0] ?? "1"),
                        ],
                        "Weight" => [
                            "BillableWeight" => (string)($request->shipment_weight[0] ?? "1"),
                            "PhyWeight" => (string)($request->shipment_weight[0] ?? "1"),
                            "VolWeight" => "0"
                        ]
                    ]
                ];

                $invoiceNumber = 'ship' . mt_rand(100000, 999999);

                // Always create GSTMultiSellerInfo
                $gstSellerInfo = [
                    "BuyerGSTRegNumber"     => '',
                    "EWayBillSrNumber"      => "",
                    "InvoiceDate"           => now()->format('d-m-Y'),
                    "InvoiceNumber"         => $invoiceNumber ?? '',
                    "IsSellerRegUnderGST"   => "",
                    "ProductUniqueID"       => "",
                    "SellerAddress"         => '',
                    "SellerGSTRegNumber"    => '',
                    "SellerName"            => '',
                    "SellerPincode"         => '',
                    "SupplySellerStatePlace"=> '',
                    "HSNDetails"            => []
                ];

                // Loop through products for HSNDetails
                if ($request->has('product_name') && is_array($request->product_name)) {
                    foreach ($request->product_name as $index => $product_name) {
                        $taxableValue = (float) ($request->product_value[$index] ?? 0);

                        $gstSellerInfo['HSNDetails'][] = [
                            "ProductCategory" => $request->product_category[$index] ?? 'General',
                            "ProductDesc"     => $request->product_name[$index] ?? $product_name,
                            "CGSTAmount"      => '',
                            "SGSTAmount"      => '',
                            "GSTTaxTotal"     => '',
                            "HSNCode"         => $request->product_hsn[$index] ?? '',
                            "TaxableValue"    => $taxableValue
                        ];
                    }
                }

                $xpressPayload['GSTMultiSellerInfo'][] = $gstSellerInfo;

                // dd($xpressPayload);
                Log::info('XpressBees API Request:', $xpressPayload);

                $response = XpressbeesApiService::sendForwardManifest($xpressPayload);

                Log::info('XpressBees API Response:', $response);

                if (isset($response['error']) && $response['error']) {
                    Log::error('XpressBees API Error:', $response);
                    session()->flash('error', $response['message'] ?? 'XpressBees API failed.');
                    return redirect()->back();
                }

                if (!isset($response['ReturnCode']) || $response['ReturnCode'] != 100) {
                    session()->flash('error', 'XpressBees API Error: ' . ($response['ReturnMessage'] ?? 'Unknown error'));
                    return redirect()->back();
                }
                $dbData = $xpressPayload;

                $dbData['shipment_width'] = $request->shipment_width[0] ?? '0.5';
                $dbData['shipment_height'] = $request->shipment_height[0] ?? '0.5';
                $dbData['shipment_length'] = $request->shipment_length[0] ?? '0.5';
                $dbData['shipment_weight'] = $request->shipment_weight[0] ?? '0.5';
                $dbData['order_amount'] = (float) ($request->total_amount ?? 0);
                $dbData['payment_mode'] = $request->payment_mode ?? 'prepaid';

                $dbData['invoice_number'] = $invoiceNumber ?? '';

                $dbData['client_order_id'] = $request->order_id ?? null;
                $dbData['consignee_emailid'] = $request->consignee_emailid ?? '';
                $dbData['consignee_pincode'] = $request->consignee_pincode ?? '';
                $dbData['consignee_mobile'] = $request->consignee_mobile ?? '';
                $dbData['consignee_phone'] = $request->consignee_phone ?? '';
                $dbData['consignee_address1'] = $request->consignee_address1 ?? '';
                $dbData['consignee_address2'] = $request->consignee_address2 ?? '';
                $dbData['consignee_name'] = $request->consignee_name ?? '';
                $dbData['awb_number'] = $response['AWBNo'] ?? null;
                $dbData['xpressbees_awb_no'] = $response['AWBNo'] ?? null;
                $dbData['xpressbees_api_status_code'] = $response['ReturnCode'] ?? null;
                $dbData['xpressbees_api_status'] = $response['ReturnMessage'] ?? null;
                $dbData['xpressbees_token_number'] = $response['TokenNumber'] ?? null;
                $dbData['xpressbees_is_parked'] = $response['IsParked'] ?? null;
                $dbData['xpressbees_payment_link'] = $response['ShipmentPaymentLink'] ?? null;

                $dbData['user_id'] = $user->id;
                $dbData['status'] = 221;
                $dbData['order_number'] = $request->order_id ?? null;
                $dbData['partner_display_name'] = 'XpressBees';
                $dbData['pick_address_id'] = $request->pickup_address ?? null;
                $dbData['return_address_id'] = $request->return_address ?? $request->pickup_address ?? null;
                $dbData['courier_name'] = 'XpressBees';
                $productDataForDb = [];

                if ($request->has('product_name') && is_array($request->product_name)) {
                    foreach ($request->product_name as $index => $product_name) {
                        $productDataForDb[] = [
                            'product_name' => $product_name,
                            'product_sku' => $request->product_sku[$index] ?? '',
                            'product_value' => (float)($request->product_value[$index] ?? 0),
                            'product_hsnsac' => $request->product_sku[$index] ?? '',
                            'product_taxper' => (float)($request->product_taxper[$index] ?? 0),
                            'product_category' => $request->product_category[$index] ?? 'Uncategorized',
                            'product_quantity' => (int)($request->product_quantity[$index] ?? 1),
                            'product_description' => '',
                        ];
                    }
                }
                // dd($dbData);
                $order = Order::create($dbData);

                foreach ($productDataForDb as $product) {
                    $product['order_id'] = $order->id;
                    Product::create($product);
                }

                $totalAmount = Wallet::where('user_id', $user->id)->first();

                if ($totalAmount) {
                    $updatedAmount = $totalAmount->amount - $chargeableAmount;
                    $totalAmount->update(['amount' => $updatedAmount]);
                } else {
                    Wallet::create([
                        'user_id' => $user->id,
                        'amount' => -$chargeableAmount,
                    ]);
                }

                $walletTransactions = WalletTransaction::where([
                    'user_id' => Auth::id(),
                    'status' => 101,
                ])->get();

                foreach ($walletTransactions as $transaction) {
                    $transaction->update(['status' => 102]);
                }

                session()->flash('success', 'Order placed successfully! AWB: ' . ($dbData['xpressbees_awb_no'] ?? ''));
                return redirect()->back();

            } catch (Exception $e) {
                Log::error('XpressBees Exception:', ['message' => $e->getMessage()]);
                session()->flash('error', 'XpressBees Error: ' . $e->getMessage());
                return redirect()->back();
            }
        }

    }

    /**
     * List Order Data
     */
    public function list(Request $request)
    {
        $search = $request->search;
        $from_date = $request->from_date;
        $to_date = $request->to_date;

        $baseQuery = Order::where('user_id', Auth::user()->id)
            ->select([
                'id',
                'client_order_id',
                'invoice_number',
                'awb_number',
                'consignee_name',
                'order_amount',
                'payment_mode',
                'status',
                'created_at'
            ]);

        if (!empty($search)) {
            $baseQuery->where('awb_number', 'LIKE', "%{$search}%");
        }

        // Apply date filter (date-only comparison)
        if (!empty($from_date) && !empty($to_date)) {
            $baseQuery->whereBetween(DB::raw('DATE(created_at)'), [$from_date, $to_date]);
        }

        $bookedQuery = clone $baseQuery;
        $cancelledQuery = clone $baseQuery;
        $allOrdersQuery = clone $baseQuery;

        $data['bookedOrders'] = $bookedQuery->where('status', 221)->paginate(100, ['*'], 'booked_page');
        $data['cancelledOrders'] = $cancelledQuery->where('status', 229)->paginate(100, ['*'], 'cancelled_page');
        $data['allOrders'] = $allOrdersQuery->paginate(100, ['*'], 'all_page');

        // Before executing the query
        // dd(vsprintf(str_replace('?', "'%s'", $baseQuery->toSql()), $baseQuery->getBindings()));


        return view('users.orders.list', $data);
    }

    /**
     * View Order Data
     */
    public function view($id)
    {
        $order = Order::where(['id' => $id])->with('productsData')->first();
        return view('users.orders.view', compact('order'));
    }

    /**Cancel Order */

    public function cancelOrder(CancelOrderRequest $request)
    {
        $awbNumber = $request->awb_number;
        $order = Order::where('awb_number', $awbNumber)->first();
        $user = Auth::user();

        // Define the API URL for cancellation
        $url = 'https://api.ekartlogistics.com/v3/shipments/rto/create';

        // Prepare the data for the request
        $apiData = [
            'request_details' => [
                'tracking_id' => $awbNumber,
                'reason' => 'Test Cancellation' // You can modify this reason as needed
            ]
        ];

        // Send the request using a helper function
        $response = EkartApiService::sendRequest($url, $apiData, 'PUT');
        $responseData = $response->json(); // Get response as an array

        // Debugging output (optional)
        // dd($responseData);

        if ($response->successful()) {
            $user->logActivity($user, 'Order canceled successfully', 'order_canceled');

            // Update the order status in the database
            $order->update(['status' => '229']);
            return response()->json(['success' => true, 'message' => 'Order canceled successfully']);
        } else {
            $user->logActivity($user, 'Exception: Order cancel Failed', 'order_failed');

            $errorMsg = $responseData['responsemsg'] ?? 'Failed to cancel order';
            return response()->json(['success' => false, 'message' => $errorMsg], 400);
        }
    }

    /**
     * Order Label Data
     * */
    public function orderLabelData(Request $request)
    {
        $awbNumbers = $request->input('awb_numbers', []);

        if (empty($awbNumbers)) {
            return response()->json(['error' => 'No AWB numbers provided.'], 400);
        }

        if (!is_array($awbNumbers)) {
            $awbNumbers = [$awbNumbers];
        }

        $orders = DB::table('shiparcel_orders')
            ->join('users', 'shiparcel_orders.user_id', '=', 'users.id')
            ->join('shiparcel_warehouses as pickup_warehouse', 'shiparcel_orders.pick_address_id', '=', 'pickup_warehouse.id')
            ->join('shiparcel_warehouses as return_warehouse', 'shiparcel_orders.return_address_id', '=', 'return_warehouse.id')
            ->select(
                'shiparcel_orders.id',
                    'shiparcel_orders.client_order_id',
                    'shiparcel_orders.consignee_emailid',
                    'shiparcel_orders.consignee_pincode',
                    'shiparcel_orders.consignee_mobile',
                    'shiparcel_orders.consignee_phone',
                    'shiparcel_orders.consignee_address1',
                    'shiparcel_orders.consignee_address2',
                    'shiparcel_orders.consignee_name',
                    'shiparcel_orders.invoice_number',
                    'shiparcel_orders.express_type',
                    'shiparcel_orders.pick_address_id',
                    'shiparcel_orders.return_address_id',
                    'shiparcel_orders.cod_amount',
                    'shiparcel_orders.tax_amount',
                    'shiparcel_orders.order_amount',
                    'shiparcel_orders.payment_mode',
                    'shiparcel_orders.courier_type',
                    'shiparcel_orders.awb_number',
                    'shiparcel_orders.order_number',
                    'shiparcel_orders.partner_display_name',
                    'shiparcel_orders.courier_code',
                    'shiparcel_orders.pickup_id',
                    'shiparcel_orders.courier_name',
                    'shiparcel_orders.user_id',
                    'shiparcel_orders.status',
                    'shiparcel_orders.created_at',
                    'shiparcel_orders.shipment_weight',

                    // Pickup address fields (aliased)
                    'pickup_warehouse.address_title as pickup_address_title',
                    'pickup_warehouse.sender_name as pickup_sender_name',
                    'pickup_warehouse.full_address as pickup_full_address',
                    'pickup_warehouse.phone as pickup_phone',
                    'pickup_warehouse.pincode as pickup_pincode',
                    'pickup_warehouse.state as pickup_state',
                    'pickup_warehouse.city as pickup_city',

                    // Return address fields (aliased)
                    'return_warehouse.address_title as return_address_title',
                    'return_warehouse.sender_name as return_sender_name',
                    'return_warehouse.full_address as return_full_address',
                    'return_warehouse.phone as return_phone',
                    'return_warehouse.pincode as return_pincode',
                    'return_warehouse.state as return_state',
                    'return_warehouse.city as return_city',

                    // User fields
                    'users.name as customer_name',
                    'users.email as customer_email'
            )
            ->whereIn('shiparcel_orders.awb_number', $awbNumbers)
            ->get();

        if ($orders->isEmpty()) {
            abort(404, 'No orders found for the selected AWB numbers.');
        }

        return view('users.orders.print_label', [
            'orders' => $orders,
            'awbNumbers' => $awbNumbers
        ]);
    }

    public function bulk_order(Request $request)
    {
        if ($request->hasFile('multiple_shipment')) {
            $courier_id = (int) $request->input('selected_courier');
            $user = Auth::user();
            $file = $request->file('multiple_shipment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = public_path('uploads/' . $filename);
            $file->move(public_path('uploads'), $filename);

            $rows = array_map('str_getcsv', file($filePath));
            $header = array_shift($rows);

            $errors = [];
            $errorRows = [];
            $successCount = 0;
            $failedCount = 0;

            // Prepare error file header
            $errorHeader = array_merge($header, ['Error_Reason']);
            $errorRows[] = $errorHeader;

            if($courier_id == '1'){
                foreach ($rows as $rowIndex => $row) {
                    $data = array_combine($header, $row);

                    // Generate tracking id
                    $base = 1000000001;
                    $maxOffset = 999999;
                    $randomNumber = $base + rand(0, $maxOffset);
                    $paymentMode = $data['payment_mode'] ?? 'prepaid';
                    $modeCode = ($paymentMode === 'Cod') ? 'C' : 'P';
                    $Tracking_id = 'HRD' . $modeCode . $randomNumber;

                    // Generate warehouse
                    $pickupWarehouse = Warehouse::where([
                        'status' => 1,
                        'user_id' => $user->id,
                        'id' => $data['pickup_id']
                    ])->first();

                    $returnWarehouse = Warehouse::where([
                        'status' => 1,
                        'user_id' => $user->id,
                        'id' => $data['return_id']
                    ])->first();

                    // Destination Address
                    $sourceAddress = [
                        'first_name' => $pickupWarehouse->sender_name ?? '',
                        'address_line1' => $pickupWarehouse->full_address ?? '',
                        'address_line2' => $pickupWarehouse->address_title ?? '',
                        'pincode' => $pickupWarehouse->pincode ?? '',
                        'city' => $pickupWarehouse->city ?? '',
                        'state' => $pickupWarehouse->state ?? '',
                        'primary_contact_number' => $pickupWarehouse->phone ?? '',
                        'email_id' => '',
                    ];

                    // Return Address
                    $returnAddress = [
                        'first_name' => $returnWarehouse->sender_name ?? '',
                        'address_line1' => $returnWarehouse->full_address ?? '',
                        'address_line2' => $returnWarehouse->address_title ?? '',
                        'pincode' => $returnWarehouse->pincode ?? '',
                        'city' => $pickupWarehouse->city ?? '',
                        'state' => $pickupWarehouse->state ?? '',
                        'primary_contact_number' => $returnWarehouse->phone ?? '',
                        'email_id' => '',
                    ];

                    $chargeableAmount = $user->chargeable_amount;

                    // Get the wallet record for the user
                    $wallet = Wallet::where('user_id', $user->id)->first();

                    // Check for no wallet, insufficient funds, or negative balance
                    if (!$wallet || $wallet->amount <= 0 || $wallet->amount < $chargeableAmount) {
                        $errors[] = 'Insufficient Balance for order at row ' . ($rowIndex + 1) . '. Please recharge your wallet!';
                        continue; // Skip to the next row
                    }

                    $productDataForDb = [];
                    $shipmentItems = [];
                    for ($i = 1; $i <= 3; $i++) {
                        $nameKey = "product_name_$i";
                        if (!empty($data[$nameKey])) {
                            $shipmentItems[] = [
                                'product_id' => $data["product_name_$i"] ?? '',
                                'category' => $data["product_category_$i"] ?? 'Uncategorized',
                                'product_title' => $data["product_name_$i"] ?? '',
                                'quantity' => (int) ($data["product_quantity_$i"] ?? 1),
                                'cost' => [
                                    'total_sale_value' => (float) ($data["product_unit_price_$i"] ?? 0),
                                    'total_tax_value' => 0,
                                    'tax_breakup' => [
                                        'cgst' => 0,
                                        'sgst' => 0,
                                        'igst' => 0,
                                    ]
                                ],
                                'seller_details' => [
                                    'seller_reg_name' => 'shiparcel',
                                    'gstin_id' => null
                                ],
                                'hsn' => $data["product_name_$i"] ?? '',
                                'ern' => null,
                                'discount' => null,
                                'item_attributes' => [
                                    ['name' => 'order_id', 'value' => $data['order_id']],
                                    ['name' => 'invoice_id', 'value' => $data['order_id']],
                                ],
                                'handling_attributes' => [
                                    ['name' => 'isFragile', 'value' => 'false'],
                                    ['name' => 'isDangerous', 'value' => 'false'],
                                ]
                            ];

                            $productDataForDb[] = [
                                'product_sku' => $data["product_name_$i"] ?? '',
                                'product_name' => $data["product_name_$i"] ?? '',
                                'product_value' => (float) ($data["product_unit_price_$i"] ?? 0),
                                'product_category' => $data["product_category_$i"] ?? 'Uncategorized',
                                'product_quantity' => (int) ($data["product_quantity_$i"] ?? 1),
                            ];
                        }
                    }

                    // Prepare full payload
                    $payload = [
                        'client_name' => 'HRD',
                        'goods_category' => 'ESSENTIAL',
                        'services' => [
                            [
                                'service_code' => 'ECONOMY',
                                'service_details' => [
                                    [
                                        'service_leg' => 'FORWARD',
                                        'service_data' => [
                                            'service_types' => [
                                                ['name' => 'regional_handover', 'value' => 'true'],
                                                ['name' => 'delayed_dispatch', 'value' => 'false'],
                                            ],
                                            'vendor_name' => 'Ekart',
                                            'amount_to_collect' => (string) ($data['total_amount'] ?? '0'),
                                            'dispatch_date' => now()->format('Y-m-d H:i:s'),
                                            'customer_promise_date' => null,
                                            'delivery_type' => 'SMALL',
                                            'source' => [
                                                'address' => $sourceAddress
                                            ],
                                            'destination' => [
                                                'address' => [
                                                    'first_name' => $data['destination_name'],
                                                    'primary_contact_number' => $data['destination_mobile'],
                                                    'address_line1' => $data['destination_address1'],
                                                    'address_line2' => $data['destination_address2'],
                                                    'pincode' => $data['destination_pincode'],
                                                    'city' => $data['destination_city'],
                                                    'state' => $data['destination_state']
                                                ]
                                            ],
                                            'return_location' => [
                                                'address' => $returnAddress ?? $sourceAddress
                                            ],
                                        ],
                                        'shipment' => [
                                            'client_reference_id' => $Tracking_id,
                                            'tracking_id' => $Tracking_id,
                                            'shipment_value' => (float) ($data['total_amount'] ?? 0),
                                            'shipment_dimensions' => [
                                                'length' => ['value' => (float) ($data['length'] ?? 0.5)],
                                                'breadth' => ['value' => (float) ($data['width'] ?? 0.5)],
                                                'height' => ['value' => (float) ($data['height'] ?? 0.5)],
                                                'weight' => ['value' => (float) ($data['dead_weight'] ?? 0.5)],
                                            ],
                                            'return_label_desc_1' => null,
                                            'return_label_desc_2' => null,
                                            'shipment_items' => $shipmentItems
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];

                    try {
                        $url = 'https://api.ekartlogistics.com/v2/shipments/create';
                        $response = EkartApiService::sendRequest($url, $payload);

                        if ($response->successful()) {
                            $responseData = $response->json();
                            Log::info('API Response:', $responseData);
                            $response = $responseData['response'][0] ?? [];

                            if (!$response['status']) {
                                $responseBody = $response->json();
                                $errorMsg = $responseBody['responsemsg'] ?? 'Unknown error occurred';
                                $errors[] = 'API Error for row ' . ($rowIndex + 1) . ': ' . $errorMsg;

                                // Add to error rows for CSV
                                $errorRow = array_merge($row, [$errorMsg]);
                                $errorRows[] = $errorRow;
                                $failedCount++;
                                continue; // Skip to the next row
                            }

                            // Save order to the database
                            $dbData = [
                                'user_id' => $user->id,
                                'awb_number' => $Tracking_id,
                                'order_number' => $data['order_id'],
                                'client_order_id' => $data['order_id'],
                                'payment_mode' => $data['payment_mode'],
                                'order_amount' => (float) ($data['total_amount'] ?? 0),
                                'shipment_length' => (float) ($data['length'] ?? 0.5),
                                'shipment_width' => (float) ($data['width'] ?? 0.5),
                                'shipment_height' => (float) ($data['height'] ?? 0.5),
                                'shipment_weight' => (float) ($data['dead_weight'] ?? 0.5),
                                'consignee_name' => $data['destination_name'],
                                'consignee_mobile' => $data['destination_mobile'],
                                'consignee_address1' => $data['destination_address1'],
                                'consignee_address2' => $data['destination_address2'],
                                'consignee_pincode' => $data['destination_pincode'],
                                'pick_address_id' => $data['pickup_id'],
                                'return_address_id' => $data['return_id'] ?? $data['pickup_id'],
                                'courier_name' => 'Ekart',
                                'partner_display_name' => 'Ekart',
                                'ekart_tracking_id' => $response['tracking_id'] ?? null,
                                'ekart_shipment_payment_link' => $response['shipment_payment_link'] ?? null,
                                'ekart_api_status' => $response['status'] ?? null,
                                'ekart_api_status_code' => $response['status_code'] ?? null,
                                'ekart_is_parked' => $response['is_parked'] ?? null,
                                'ekart_request_id' => $responseData['request_id'] ?? null,
                                'status' => 221,
                            ];

                            $order = Order::create($dbData);

                            foreach ($productDataForDb as $product) {
                                $product['order_id'] = $order->id;
                                Product::create($product);
                            }

                            // Update wallet balance
                            $totalAmount = Wallet::where('user_id', $user->id)->first();
                            if ($totalAmount) {
                                $updatedAmount = $totalAmount->amount - $chargeableAmount;
                                $totalAmount->update(['amount' => $updatedAmount]);
                            } else {
                                Wallet::create([
                                    'user_id' => $user->id,
                                    'amount' => -$chargeableAmount
                                ]);
                            }

                            // Update wallet transactions
                            $walletTransactions = WalletTransaction::where([
                                'user_id' => Auth::id(),
                                'status' => 101
                            ])->get();

                            foreach ($walletTransactions as $transaction) {
                                $transaction->update(['status' => 102]);
                            }

                            $user->logActivity($user, 'Order created successfully', 'order_created');
                            $successCount++;
                        } else {
                            $responseBody = $response->json();
                            $errorMsg = $responseBody['responsemsg'] ?? 'Unknown error occurred';
                            $errors[] = 'API Error for row ' . ($rowIndex + 1) . ': ' . $errorMsg;

                            // Add to error rows for CSV
                            $errorRow = array_merge($row, [$errorMsg]);
                            $errorRows[] = $errorRow;
                            $failedCount++;
                            continue;
                        }
                    } catch (Exception $e) {
                        $errorMsg = $e->getMessage();
                        $errors[] = 'Exception for row ' . ($rowIndex + 1) . ': ' . $errorMsg;

                        // Add to error rows for CSV
                        $errorRow = array_merge($row, [$errorMsg]);
                        $errorRows[] = $errorRow;
                        $failedCount++;
                    }
                }
            }elseif($courier_id == '2'){
                foreach ($rows as $rowIndex => $row) {
                    $data = array_combine($header, $row);

                    // Generate tracking id for XpressBees (if needed)
                    $Tracking_id = 'XPR' . mt_rand(100000, 999999);

                    // Get warehouses (same as your existing code)
                    $pickupWarehouse = Warehouse::where([
                        'status' => 1,
                        'user_id' => $user->id,
                        'id' => $data['pickup_id']
                    ])->first();

                    // If return address is enabled, fetch it; else, we'll use pickupWarehouse again
                    $returnWarehouse = Warehouse::where([
                        'status' => 1,
                        'user_id' => $user->id,
                        'id' => $data['return_id']
                    ])->first();

                    // Prepare XpressBees payload
                    $xpressPayload = [
                        "BusinessAccountName" => "ANKUR PARSHAR",
                        "OrderNo" => $data['order_id'],
                        "SubOrderNo" => $data['order_id'] . '-1',
                        "OrderType" => ($data['payment_mode'] === 'Cod') ? 'COD' : 'PrePaid',
                        "CollectibleAmount" => ($data['payment_mode'] === 'Cod') ? (string)$data['total_amount'] : "0",
                        "DeclaredValue" => (string)($data['total_amount'] ?? "0"),
                        "PickupType" => "Vendor",
                        "Quantity" => 1,
                        "ServiceType" => "SD",

                        "DropDetails" => [
                            "Addresses" => [[
                                "Address" => $data['destination_address1'],
                                "City" => $data['destination_city'] ?? '',
                                "EmailID" => '',
                                "Name" => $data['destination_name'],
                                "PinCode" => $data['destination_pincode'],
                                "State" => $data['destination_state'] ?? '',
                                "Type" => "Primary",
                            ]],
                            "ContactDetails" => [[
                                "PhoneNo" => $data['destination_mobile'],
                                "Type" => "Primary",
                            ]],
                        ],

                        "PickupDetails" => [
                            "Addresses" => [[
                                "Address" => $pickupWarehouse->full_address ?? '',
                                "City" => $pickupWarehouse->city ?? '',
                                "EmailID" => '',
                                "Name" => $pickupWarehouse->sender_name ?? '',
                                "PinCode" => $pickupWarehouse->pincode ?? '',
                                "State" => $pickupWarehouse->state ?? '',
                                "Type" => "Primary",
                            ]],
                            "ContactDetails" => [[
                                "PhoneNo" => $pickupWarehouse->phone ?? '',
                                "Type" => "Primary",
                            ]],
                            "PickupVendorCode" => env('XPRESSBEES_VENDOR_CODE', 'YOUR_VENDOR_CODE')
                        ],

                        "RTODetails" => [
                            "Addresses" => [[
                                "Address" => $returnWarehouse->full_address ?? $pickupWarehouse->full_address ?? '',
                                "City" => $returnWarehouse->city ?? $pickupWarehouse->city ?? '',
                                "EmailID" => '',
                                "Name" => $returnWarehouse->sender_name ?? $pickupWarehouse->sender_name ?? '',
                                "PinCode" => $returnWarehouse->pincode ?? $pickupWarehouse->pincode ?? '',
                                "State" => $returnWarehouse->state ?? $pickupWarehouse->state ?? '',
                                "Type" => "Primary",
                            ]],
                            "ContactDetails" => [[
                                "PhoneNo" => $returnWarehouse->phone ?? $pickupWarehouse->phone ?? '',
                                "Type" => "Primary",
                            ]],
                        ],

                        "ManifestID" => $data['order_id'],
                        "IsEssential" => "false",
                        "IsSecondaryPacking" => "false",

                        "PackageDetails" => [
                            "Dimensions" => [
                                "Height" => (string)($data['height'] ?? "1"),
                                "Length" => (string)($data['length'] ?? "1"),
                                "Width" => (string)($data['width'] ?? "1"),
                            ],
                            "Weight" => [
                                "BillableWeight" => (string)($data['dead_weight'] ?? "1"),
                                "PhyWeight" => (string)($data['dead_weight'] ?? "1"),
                                "VolWeight" => "0"
                            ]
                        ]
                    ];

                    // Prepare product data for GST/HSN details
                    $invoiceNumber = 'ship' . mt_rand(100000, 999999);
                    $gstSellerInfo = [
                        "BuyerGSTRegNumber" => '',
                        "EWayBillSrNumber" => "",
                        "InvoiceDate" => now()->format('d-m-Y'),
                        "InvoiceNumber" => $invoiceNumber,
                        "IsSellerRegUnderGST" => "",
                        "ProductUniqueID" => "",
                        "SellerAddress" => '',
                        "SellerGSTRegNumber" => '',
                        "SellerName" => '',
                        "SellerPincode" => '',
                        "SupplySellerStatePlace" => '',
                        "HSNDetails" => []
                    ];

                    $productDataForDb = [];
                    $productCount = 0;

                    // Process products (same as your existing loop)
                    for ($i = 1; $i <= 3; $i++) {
                        $nameKey = "product_name_$i";
                        if (!empty($data[$nameKey])) {
                            $productCount++;

                            $gstSellerInfo['HSNDetails'][] = [
                                "ProductCategory" => $data["product_category_$i"] ?? 'General',
                                "ProductDesc" => $data["product_name_$i"] ?? '',
                                "CGSTAmount" => '',
                                "SGSTAmount" => '',
                                "GSTTaxTotal" => '',
                                "HSNCode" => $data["product_hsn_$i"] ?? '',
                                "TaxableValue" => (float)($data["product_unit_price_$i"] ?? 0)
                            ];

                            $productDataForDb[] = [
                                'product_sku' => $data["product_name_$i"] ?? '',
                                'product_name' => $data["product_name_$i"] ?? '',
                                'product_value' => (float)($data["product_unit_price_$i"] ?? 0),
                                'product_category' => $data["product_category_$i"] ?? 'Uncategorized',
                                'product_quantity' => (int)($data["product_quantity_$i"] ?? 1),
                            ];
                        }
                    }

                    // Update quantity based on actual products
                    $xpressPayload['Quantity'] = $productCount;
                    $xpressPayload['GSTMultiSellerInfo'][] = $gstSellerInfo;

                    // dd($xpressPayload);
                    // Wallet check (same as your existing code)
                    $chargeableAmount = $user->chargeable_amount;
                    $wallet = Wallet::where('user_id', $user->id)->first();
                    if (!$wallet || $wallet->amount <= 0 || $wallet->amount < $chargeableAmount) {
                        $errors[] = 'Insufficient Balance for order at row ' . ($rowIndex + 1) . '. Please recharge your wallet!';
                        continue;
                    }

                    try {
                        // Call XpressBees API
                        $response = XpressbeesApiService::sendForwardManifest($xpressPayload);

                        if (isset($response['error']) && $response['error']) {
                            $responseBody = $response['message'];
                            $errorMsg = $responseBody['ReturnMessage'] ?? 'Unknown error occurred';
                            $errors[] = 'API Error for row ' . ($rowIndex + 1) . ': ' . $errorMsg;

                            // Add to error rows for CSV
                            $errorRow = array_merge($row, [$errorMsg]);
                            $errorRows[] = $errorRow;
                            $failedCount++;
                            continue; // Skip to the next row
                        }

                        if (!isset($response['ReturnCode']) || $response['ReturnCode'] != 100) {
                            $responseBody = $response['message'];
                            $errorMsg = $responseBody['ReturnMessage'] ?? 'Unknown error occurred';
                            $errors[] = 'API Error for row ' . ($rowIndex + 1) . ': ' . $errorMsg;

                            // Add to error rows for CSV
                            $errorRow = array_merge($row, [$errorMsg]);
                            $errorRows[] = $errorRow;
                            $failedCount++;
                            continue; // Skip to the next row
                        }

                        // Prepare database data
                        $dbData = [
                            'user_id' => $user->id,
                            'awb_number' => $response['AWBNo'] ?? null,
                            'order_number' => $data['order_id'],
                            'client_order_id' => $data['order_id'],
                            'payment_mode' => $data['payment_mode'],
                            'order_amount' => (float)($data['total_amount'] ?? 0),
                            'shipment_length' => (float)($data['length'] ?? 0.5),
                            'shipment_width' => (float)($data['width'] ?? 0.5),
                            'shipment_height' => (float)($data['height'] ?? 0.5),
                            'shipment_weight' => (float)($data['dead_weight'] ?? 0.5),
                            'consignee_name' => $data['destination_name'],
                            'consignee_mobile' => $data['destination_mobile'],
                            'consignee_address1' => $data['destination_address1'],
                            'consignee_address2' => $data['destination_address2'],
                            'consignee_pincode' => $data['destination_pincode'],
                            'pick_address_id' => $data['pickup_id'],
                            'return_address_id' => $data['return_id'] ?? $data['pickup_id'],
                            'courier_name' => 'XpressBees',
                            'partner_display_name' => 'XpressBees',
                            'xpressbees_awb_no' => $response['AWBNo'] ?? null,
                            'xpressbees_api_status_code' => $response['ReturnCode'] ?? null,
                            'xpressbees_api_status' => $response['ReturnMessage'] ?? null,
                            'xpressbees_token_number' => $response['TokenNumber'] ?? null,
                            'xpressbees_is_parked' => $response['IsParked'] ?? null,
                            'xpressbees_payment_link' => $response['ShipmentPaymentLink'] ?? null,
                            'status' => 221,
                            'invoice_number' => $invoiceNumber,
                        ];

                        $order = Order::create($dbData);

                        foreach ($productDataForDb as $product) {
                            $product['order_id'] = $order->id;
                            Product::create($product);
                        }

                        // Update wallet (same as your existing code)
                        $totalAmount = Wallet::where('user_id', $user->id)->first();
                        if ($totalAmount) {
                            $updatedAmount = $totalAmount->amount - $chargeableAmount;
                            $totalAmount->update(['amount' => $updatedAmount]);
                        }

                        $walletTransactions = WalletTransaction::where([
                            'user_id' => Auth::id(),
                            'status' => 101
                        ])->get();

                        foreach ($walletTransactions as $transaction) {
                            $transaction->update(['status' => 102]);
                        }

                        $successCount++;

                    } catch (Exception $e) {
                        $errorMsg = $e->getMessage();
                        $errors[] = 'Exception for row ' . ($rowIndex + 1) . ': ' . $errorMsg;

                        // Add to error rows for CSV
                        $errorRow = array_merge($row, [$errorMsg]);
                        $errorRows[] = $errorRow;
                        $failedCount++;
                    }
                }
            }


            $errorFileUrl = null;
            if (!empty($errors)) {
                $errorFilename = 'errors_' . $filename;
                $errorFilePath = public_path('uploads/' . $errorFilename);

                $file = fopen($errorFilePath, 'w');
                foreach ($errorRows as $errorRow) {
                    fputcsv($file, $errorRow);
                }
                fclose($file);

                $errorFileUrl = url('uploads/' . $errorFilename);
            }
            // Prepare final response
            $responseData = [
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors,
                'error_file_url' => $errorFileUrl
            ];

            if ($failedCount > 0) {
                return back()
                    ->with('error', "$failedCount orders failed. $successCount orders succeeded.")
                    ->with('error_details', $errors)
                    ->with('error_file', $errorFileUrl)
                    ->with('success_count', $successCount)
                    ->with('failed_count', $failedCount);
            }

            return back()
                ->with('success', "$successCount orders placed successfully.")
                ->with('success_count', $successCount);

        }
        return back()->with('error', 'No CSV file uploaded.');
    }


    public function exportCsv(Request $request)
    {
        $awbNumbers = explode(',', $request->input('selected_awbs'));
        $orders = Order::with('productsData')->whereIn('awb_number', $awbNumbers)->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=selected_orders.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Order ID',
            'AWB Number',
            'Order Amount',
            'Payment Mode',
            'Consignee Name',
            'Consignee Email',
            'Consignee Phone',
            'Consignee Address',
            'Pincode',
            'Tax',
            'Status',
            'Created At',
            'Product Details', 
            'Courier_name'
        ];

        $callback = function () use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                $productDetails = $order->productsData->map(function ($product) {
                    return ($product->product_name ?? '') . ' (SKU: ' . ($product->product_sku ?? '') . ', Qty: ' . ($product->product_quantity ?? '') . ')';
                })->implode(', ');

                fputcsv($file, [
                    $order->client_order_id,
                    $order->awb_number,
                    $order->order_amount,
                    $order->payment_mode,
                    $order->consignee_name,
                    $order->consignee_emailid,
                    $order->consignee_mobile ?: $order->consignee_phone,
                    $order->consignee_address1,
                    $order->consignee_pincode,
                    $order->tax_amount,
                    $order->status == 221 ? 'Booked' : '',
                    $order->created_at->format('d M Y'),
                    $productDetails,
                    $order->courier_name
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


}

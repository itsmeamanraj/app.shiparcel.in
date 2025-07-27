<html class="no-js" lang="en">
<script id="allow-copy_script" src="chrome-extension://aefehdhdciieocakfobpaaolhipkcpgc/content_scripts/copy.js">
</script>
<!--<![endif]-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="Content-Language" content="en">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="">
    <link rel="stylesheet" crossorigin="anonymous" href="">
    <!-- <link href="" rel="stylesheet"> -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <title>Shiprcel | Print label</title>
    <style type="text/css">
    .h1,
    .h2,
    .h3,
    .h4,
    .h5,
    .h6,
    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    p {
        margin-bottom: 0px;
    }

    h4 {
        font-size: 21px;
        font-family: 'Arial', sans-serif;
        font-weight: 600;
    }

    p,
    h6 {
        font-size: 18px;
        font-family: 'Arial', sans-serif;
        font-weight: 600;
    }

    table.manifest-table {
        border-collapse: collapse;
        width: 55%;
        page-break-inside: avoid;
        color: #000000;
        border: 1px solid #080808;
        margin-bottom: 50px;
    }

    @font-face {
        font-family: B39MHR;
        src: url('https://app.parcelx.in/assets/css/fonts/B39MHR.ttf');
    }

    table.prod-table {
        border-collapse: collapse;
        width: 100%;
        /* margin: auto; */
        border-top: none;
        font-size: 15px;
        color: #000000;
        font-family: 'Roboto', sans-serif;
    }

    table td {
        border: 1px solid black;
    }

    table .prod-table thead:first-child th {
        border-right: 1px solid;
    }

    table tr td:first-child {
        border-left: 0;
    }

    table .prod-table tr:last-child td {
        border-bottom: 0;
    }

    table tr td:last-child {
        border-right: 0;
    }

    .barcode {
        font-family: 'B39MHR';
        font-size: 50px;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .rotate270 {
        -webkit-transform: rotate(270deg);
        -moz-transform: rotate(270deg);
        -o-transform: rotate(270deg);
        -ms-transform: rotate(270deg);
        transform: rotate(270deg);
    }

    .mb5 {
        margin-bottom: 5px;
    }

    table tr td {
        padding-left: 5px;
        padding-right: 5px;
        position: relative;
    }

    body {
        line-height: 2.5;
    }

    @media print {
        @page {
            /* size: auto;   auto is the initial value */
            size: 4in 6in;
            margin: 10px;
            /*this affects the margin in the printer settings*/
            color: #000000 !important;
        }

        .printPage {
            display: none;
        }
    }

    .btn-success {
        background: #ff6801 !important;
        border: 1px solid #ff6801 !important;
    }

    .product_left {
        padding-left: 0px !important;
        padding-right: 0px !important;
    }

    .extype {
        margin-top: -20px;
        text-align: center;
        font-weight: 800;
        font-size: 16px !important;
    }
    </style>
</head>

<body cz-shortcut-listen="true" data-new-gr-c-s-check-loaded="14.1245.0" data-gr-ext-installed="">
    <button type="button" class="btn btn-sm btn-success printPage" onclick="window.print();"
        style="position: fixed; top: 10%; right: 2%;"><i class="fa fa-print"></i> Print</button>
    <table class="manifest-table">
        <tbody>
            <tr style="vertical-align: baseline;">
                <td class="d-flex align-items-center justify-content-between py-3 border-0">
                    <h3 class="w-75">
                        {{ $orderData->customer_name ?? '' }}</h3>

                </td>
                <td class="text-center">
                    <img src="{{ asset('assets/images/ekart.png') }}" alt="{{ $orderData->courier_name ?? '' }}"
                        style="width: 100%;">

                    <p style="font-size: 14px; margin-bottom:0px" class="extype"><b></b> {{ $orderData->express_type ?? '' }} </p>
                </td>
            </tr>
            <tr>
                <td class="text-center">
                    <!--<span class="barcode" style="color: #000000;">*34572714100784*</span>-->
                    <img id="barcodebarcode_1" style="width: 50% !important; height: 100px; margin: 10px"
                        src="data:image/png;base64,{{ DNS1D::getBarcodePNG($orderData->awb_number, 'C128') }}"
                        alt="barcode" /><br>
                        {{ $orderData->awb_number ?? '' }}
                </td>

                <td class="text-center">

                    <br>
                    <h4><b>({{ $orderData->payment_mode ?? ''  }})</b></h4>
                </td>

            </tr>
            <tr style="line-height: 20px;">
                <td class="address-cell" style="width: 70%;">
                    <p>Deliver To:
                    </p>
                    <h4 class="mb5">{{ $orderData->consignee_name ?? ''  }}</h4>
                    <p class="mb5">
                        {{ $orderData->consignee_mobile ?? ''  }} </p>

                    <p style="word-break: break-word !important;"><span><b>Address:</b></span>
                        {{ $orderData->consignee_address1  ?? ''  }} </p>
                    <p>{{ $orderData->consignee_address2 ?? ''  }}</p>
                    <p>Pin - {{ $orderData->consignee_pincode ?? ''  }}</p>
                </td>

                <td>
                    <!-- <p>Order Details</p> -->
                    <!-- <h6 class="mb5">GSTIN: </h6> -->
                    <h6 class="mb5"><b>Order Id:</b> {{ $orderData->client_order_id ?? ''  }}</h6>
                    {{-- <h6 class="mb5">
                        <b>Ref./Invoice#:</b><br>
                        PX13659825
                    </h6> --}}
                    <h6 class="mb5">Date: {{ \Carbon\Carbon::parse($orderData->created_at)->format('d-m-Y') }}</h6>
                    <h6 class="mb5">Weight: {{ $orderData->shipment_weight ?? ''  }} kg</h6>

                    <h6 class="mb5">Invoice Value: Rs. {{ $orderData->order_amount ?? ''  }} </h6>
                </td>
            </tr>
            <tr style="padding-top: 2px;">
                <td class="product_left" colspan="2">
                    @php
                        $products = DB::table('products')
                            ->where('products.order_id', $orderData->id)
                            ->select('products.product_name', 'products.product_sku', 'products.product_quantity', 'products.product_value')
                            ->get();

                        $totalQty = 0;
                        $totalPrice = 0;
                    @endphp
                    <table class="prod-table">
                        <thead>
                            <tr>
                                <th colspan="2">Product Name</th>
                                <th>SKU</th>
                                <th>Qty</th>
                                <th style="border-right: 0; width:16%; text-align:right;" class="hide_colume">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td colspan="2">{{ $product->product_name ?? ''  }}</td>
                                    <td>{{ $product->product_sku ?? ''  }}</td>
                                    <td>{{ $product->product_quantity ?? '' }}</td>
                                    <td class="text-right hide_colume">{{ $product->product_value ?? '' }}</td>
                                </tr>
                                @php
                                    $totalQty += $product->product_quantity;
                                    $totalPrice += $product->product_value * $product->product_quantity;
                                @endphp
                            @endforeach

                            <tr class="hide_colume">
                                <td colspan="2" class="text-right"><p>Total</p></td>
                                <td></td>
                                <td><p>{{ $totalQty ?? '' }}</p></td>
                                <td class="text-right"><p>Rs.{{ $totalPrice ?? '' }}</p></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr style="line-height: 1.5;">
                <td colspan="2">

                    <h6>If not delivered, Return to:</h6>

                    <p style="font-size: 14px; margin-bottom:0px"><b>Warehouse Name:</b> {{ $orderData->return_sender_name ?? '' }}
                    </p>
                    <p style="font-size: 14px;">{{ $orderData->return_full_address ?? '' }} {{ $orderData->return_state ?? ''  }} {{ $orderData->return_city ?? '' }} {{ $orderData->return_pincode ?? '' }} <br> Phone: {{ $orderData->return_phone ?? '' }}  </p>
                </td>

            </tr>
        </tbody>
    </table>






</body>
<grammarly-desktop-integration data-grammarly-shadow-root="true"></grammarly-desktop-integration>

</html>

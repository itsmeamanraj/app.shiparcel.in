@extends('layouts.admin')

@section('title', 'Order Details')

@section('content')
<div class="dashboard-main-body">
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4>Order Details - #{{ $order->client_order_id }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Order Info -->
                    <div class="col-md-6">
                        <h5>Order Information</h5>
                        <p><strong>Invoice Number:</strong> {{ $order->invoice_number }}</p>
                        <p><strong>AWB Number:</strong> {{ $order->awb_number ?? 'N/A' }}</p>
                        <p><strong>Order Amount:</strong> ₹{{ number_format($order->order_amount, 2) }}</p>
                        <p><strong>Payment Mode:</strong> {{ ucfirst($order->payment_mode) }}</p>
                        <p><strong>Created At:</strong> {{ $order->created_at->format('d M Y, h:i A') }}</p>
                    </div>

                    <!-- Consignee Details -->
                    <div class="col-md-6">
                        <h5>Consignee Details</h5>
                        <p><strong>Name:</strong> {{ $order->consignee_name }}</p>
                        <p><strong>Email:</strong> {{ $order->consignee_emailid }}</p>
                        <p><strong>Phone:</strong> {{ $order->consignee_mobile }}</p>
                        <p><strong>Address:</strong> {{ $order->consignee_address1 }}, {{ $order->consignee_address2 }}</p>
                        <p><strong>Pincode:</strong> {{ $order->consignee_pincode }}</p>
                    </div>
                </div>

                <!-- Product List -->
                <div class="mt-4">
                    <h5>Products</h5>
                    @if($order->productsData && count($order->productsData) > 0)
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Name</th>
                                <th>Quantity</th>
                                <th>Value</th>
                                <th>Tax (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->productsData as $product)
                            <tr>
                                <td>{{ $product->product_sku }}</td>
                                <td>{{ $product->product_name }}</td>
                                <td>{{ $product->product_quantity }}</td>
                                <td>₹{{ number_format($product->product_value, 2) }}</td>
                                <td>{{ $product->product_taxper }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p>No products found for this order.</p>
                    @endif

                </div>

                <!-- Back Button -->
                <div class="mt-3">
                    <a href="{{ route('list.order') }}" class="btn btn-primary">Back to Orders</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
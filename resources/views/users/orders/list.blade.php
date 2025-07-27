@extends('layouts.admin')

@section('title', 'Create Shipment')

@section('content')
<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">List User Shipment</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">List Orders</li>
        </ul>
    </div>
    <div class="card mb-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <span class="text-md fw-medium text-secondary-light mb-0">AWB Number:</span>
                <form class="navbar-search">
                    <input type="text" class="bg-base h-40-px w-auto" value="{{request()->search}}" name="search" placeholder="Search">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                    <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8">
                        Submit
                    </button>
                    <a href="{{route('list.order')}}" class="btn btn-danger text-sm btn-sm px-12 py-12 radius-8">
                        Reset
                    </a>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">List User Shipment</h5>
            <p>
                @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            </p>
            <!-- Alert Container -->
            <div id="alertContainer"></div>
        </div>

        <div class="container">
            <div class="card h-100 p-0 radius-12 overflow-hidden">
                <div class="card-header border-bottom-0 pb-0 pt-0 px-0">
                    <ul class="nav border-gradient-tab nav-pills mb-0 border-top-0" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-all-tab" data-bs-toggle="pill" data-bs-target="#pills-all" type="button" role="tab" aria-controls="pills-all" aria-selected="true">
                                Booked
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-ui-design-tab" data-bs-toggle="pill" data-bs-target="#pills-ui-design" type="button" role="tab" aria-controls="pills-ui-design" aria-selected="false" tabindex="-1">
                                Cancelled
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="rate-chart-tab" data-bs-toggle="pill" data-bs-target="#rate-chart" type="button" role="tab" aria-controls="rate-chart" aria-selected="false" tabindex="-1">
                                All Orders
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-24">
                    <div class="tab-content" id="pills-tabContent">
                        <!-- booked -->
                        <div class="tab-pane fade show active" id="pills-all" role="tabpanel" aria-labelledby="pills-all-tab">
                            <div class="table-responsive">
                                <table class="table basic-border-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>AWB Number</th>
                                            <th>Customer Name</th>
                                            <th>Issued Date</th>
                                            <th>Amount</th>
                                            <th>Payment Mode</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bookedOrders as $order)
                                        <tr>
                                            <td>#{{ $order->id }}</td>
                                            <td>{{ $order->awb_number ?? 'N/A' }}</td>
                                            <td>{{ $order->consignee_name }}</td>
                                            <td>{{ $order->created_at->format('d M Y') }}</td>
                                            <td>₹{{ number_format($order->order_amount, 2) }}</td>
                                            <td>{{ ucfirst($order->payment_mode) }}</td>
                                            <td>
                                                @if($order->status ==221)<span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">{{'Booked'}}</span>@else<span class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">{{'Cancelled'}}</span>@endif

                                            </td>
                                            <td>
                                                <a href="{{ route('orders.view', $order->id) }}" title="View Order" class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center">
                                                    <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                                </a>
                                                <a href="javascript:void(0)" title="" class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                                </a>
                                                <!-- Button to Open Modal -->
                                                <a href="javascript:void(0)" title="Cancel Order" class="w-32-px h-32-px bg-info-focus text-info-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                                    onclick="showCancelConfirmModal('{{ $order->awb_number }}')">
                                                    <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                                </a>
                                                <a href="javascript:void(0)" title="Download label" class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                                    onclick="openLabelData('{{ $order->awb_number }}')">
                                                    <iconify-icon icon="material-symbols:cloud-download"></iconify-icon>
                                                </a>
                                                <!-- Bootstrap Modal Start -->
                                                <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title" id="cancelOrderLabel">Confirm Cancellation</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to cancel this order? This action cannot be undone.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                                                <button type="button" class="btn btn-danger" onclick="cancelOrder()">Yes, Cancel</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Bootstrap Modal End -->
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $bookedOrders->links() }}
                            </div>
                        </div>

                        <!-- canceled -->
                        <div class="tab-pane fade" id="pills-ui-design" role="tabpanel" aria-labelledby="pills-ui-design-tab">
                            <div class="table-responsive">
                                <table class="table basic-border-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>AWB Number</th>
                                            <th>Customer Name</th>
                                            <th>Issued Date</th>
                                            <th>Amount</th>
                                            <th>Payment Mode</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cancelledOrders as $order)
                                        <tr>
                                            <td>#{{ $order->id }}</td>
                                            <td>{{ $order->awb_number ?? 'N/A' }}</td>
                                            <td>{{ $order->consignee_name }}</td>
                                            <td>{{ $order->created_at->format('d M Y') }}</td>
                                            <td>₹{{ number_format($order->order_amount, 2) }}</td>
                                            <td>{{ ucfirst($order->payment_mode) }}</td>
                                            <td>
                                                @if($order->status ==221)<span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">{{'Booked'}}</span>@else<span class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">{{'Cancelled'}}</span>@endif

                                            </td>
                                            <td>
                                                <a href="{{ route('orders.view', $order->id) }}" title="View Order" class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center">
                                                    <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                                </a>
                                                <a href="javascript:void(0)" title="" class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                                </a>
                                                <!-- Button to Open Modal -->
                                                <a href="javascript:void(0)" title="Cancel Order" class="w-32-px h-32-px bg-info-focus text-info-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                                    onclick="showCancelConfirmModal('{{ $order->awb_number }}')">
                                                    <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                                </a>
                                                <a href="javascript:void(0)" title="Download label" class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                                    onclick="openLabelData('{{ $order->awb_number }}')">
                                                    <iconify-icon icon="material-symbols:cloud-download"></iconify-icon>
                                                </a>
                                                <!-- Bootstrap Modal Start -->
                                                <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title" id="cancelOrderLabel">Confirm Cancellation</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to cancel this order? This action cannot be undone.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                                                <button type="button" class="btn btn-danger" onclick="cancelOrder()">Yes, Cancel</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Bootstrap Modal End -->
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $cancelledOrders->links() }}
                            </div>
                        </div>

                        <!-- all orders -->
                        <div class="tab-pane fade" id="rate-chart" role="tabpanel" aria-labelledby="rate-chart-tab">
                            <div class="table-responsive">
                                <table class="table basic-border-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>AWB Number</th>
                                            <th>Customer Name</th>
                                            <th>Issued Date</th>
                                            <th>Amount</th>
                                            <th>Payment Mode</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allOrders as $order)
                                        <tr>
                                            <td>#{{ $order->id }}</td>
                                            <td>{{ $order->awb_number ?? 'N/A' }}</td>
                                            <td>{{ $order->consignee_name }}</td>
                                            <td>{{ $order->created_at->format('d M Y') }}</td>
                                            <td>₹{{ number_format($order->order_amount, 2) }}</td>
                                            <td>{{ ucfirst($order->payment_mode) }}</td>
                                            <td>
                                                @if($order->status ==221)<span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">{{'Booked'}}</span>@else<span class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">{{'Cancelled'}}</span>@endif

                                            </td>
                                            <td>
                                                <a href="{{ route('orders.view', $order->id) }}" title="View Order" class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center">
                                                    <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                                </a>
                                                <a href="javascript:void(0)" title="" class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                                </a>
                                                <!-- Button to Open Modal -->
                                                <a href="javascript:void(0)" title="Cancel Order" class="w-32-px h-32-px bg-info-focus text-info-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                                    onclick="showCancelConfirmModal('{{ $order->awb_number }}')">
                                                    <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                                </a>
                                                <a href="javascript:void(0)" title="Download label" class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                                    onclick="openLabelData('{{ $order->awb_number }}')">
                                                    <iconify-icon icon="material-symbols:cloud-download"></iconify-icon>
                                                </a>
                                                <!-- Bootstrap Modal Start -->
                                                <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title" id="cancelOrderLabel">Confirm Cancellation</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to cancel this order? This action cannot be undone.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                                                <button type="button" class="btn btn-danger" onclick="cancelOrder()">Yes, Cancel</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Bootstrap Modal End -->
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $allOrders->links() }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<script>
    var ORDER_CANCEL_URL = "{{ route('order.cancel') }}";
    var ORDER_LABEL_URL = "{{ route('order.label-data') }}";
    // var CSRF_TOKEN = "{{ csrf_token() }}";
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

</script>
<script src="{{ asset('assets/js/order/app.js') }}"></script>
@endsection
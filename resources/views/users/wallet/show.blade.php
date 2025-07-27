@extends('layouts.admin')

@section('title', 'Create Warehouse')

@section('content')
<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Wallet</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="index.html" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Wallet</li>
        </ul>
    </div>

    <div class="card h-100 p-0 radius-12 overflow-hidden">
        <div class="card-header border-bottom-0 pb-0 pt-0 px-0">
            <ul class="nav border-gradient-tab nav-pills mb-0 border-top-0" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-all-tab" data-bs-toggle="pill" data-bs-target="#pills-all"
                        type="button" role="tab" aria-controls="pills-all" aria-selected="true">
                        Add Money
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-ui-design-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-ui-design" type="button" role="tab" aria-controls="pills-ui-design"
                        aria-selected="false" tabindex="-1">
                        Recharge History
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rate-chart-tab" data-bs-toggle="pill" data-bs-target="#rate-chart"
                        type="button" role="tab" aria-controls="rate-chart" aria-selected="false" tabindex="-1">
                        Rate Chart
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-24">
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-all" role="tabpanel" aria-labelledby="pills-all-tab"
                    tabindex="0">
                    <div class="row gy-4">
                        @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                        @endif

                        <form class="row gy-3 needs-validation" novalidate id="walletForm"
                            action="{{ route('wallet.store') }}" method="POST">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label">Enter your amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" id="amount"
                                    placeholder="for example: 1000" required>
                                <div class="invalid-feedback">
                                    Please enter a valid amount.
                                </div>
                            </div>

                            <div class="col-md-6 d-flex align-items-end gap-2">
                                <input type="text" name="promo_code" class="form-control" id="promo_code"
                                    placeholder="Enter Promocode..">
                                <button class="btn btn-success" type="button" onclick="applyPromo()">Apply</button>
                            </div>


                            <div class="col-md-4 col-sm-6">
                                <div class="hover-scale-img border radius-16 overflow-hidden">
                                    <img src="{{ asset('assets/images/qr/images.jpg') }}" alt=""
                                        class="hover-scale-img__img w-100 h-100 object-fit-cover">
                                    <div class="py-16 px-24">
                                        <!-- <h6 class="mb-4">This QR for payment</h6> -->
                                        <!-- <p class="mb-0 text-sm text-secondary-light">scan me</p> -->
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <button class="btn btn-primary-600" type="submit">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="tab-pane fade" id="pills-ui-design" role="tabpanel" aria-labelledby="pills-ui-design-tab"
                    tabindex="0">
                    <div class="row gy-4">
                        <!-- table start -->
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Bordered Tables</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table basic-border-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Invoice</th>
                                                    <th>Name</th>
                                                    <th>Issued Date</th>
                                                    <th>Updated Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($transactions as $transaction)
                                                <tr>
                                                    <td>
                                                        <a href="javascript:void(0)"
                                                            class="text-primary-600">#{{ $transaction->invoice_number }}</a>
                                                    </td>
                                                    <td>{{ $transaction->name }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($transaction->issued_date)->format('d M Y') }}
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($transaction->updated_at)->format('d M Y') }}
                                                    </td>
                                                    <td>₹{{ number_format($transaction->amount, 2) }}</td>
                                                    <td>
                                                        <!-- <span class="badge {{ $transaction->status === 'Completed' ? 'bg-success' : 'bg-warning' }}">
                                                                {{ $transaction->status }}
                                                            </span> -->
                                                        <span>
                                                            {{ $transaction->status_label }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="javascript:void(0)" class="text-primary-600">View
                                                            More</a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No transactions found.
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div><!-- card end -->
                        </div>
                        <!-- table end -->
                    </div>
                </div>
                <div class="tab-pane fade" id="rate-chart" role="tabpanel" aria-labelledby="rate-chart-tab" tabindex="0">
                    <!-- start -->
                    <div class="row gy-4">
                        <div class="card">
                            <div class="card-header bg-light border-bottom pb-2">
                                <form id="filter-form">
                                    <!-- Mode Selection -->
                                    <div class="mb-3">
                                        <label class="fw-bold">Mode:</label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mode" value="air" checked>
                                                <label class="form-check-label">Air</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mode" value="surface">
                                                <label class="form-check-label">Surface</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Weight Slab Filter -->
                                    <div class="mt-3">
                                        <span class="fw-bold text-primary">Select Weight Slab:</span>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            @foreach ($weightSlabs as $slab)
                                            <div class="form-check">
                                                <input class="form-check-input weight-filter" type="radio" name="weight_slab" value="{{ $slab->id }}">
                                                <label class="form-check-label">{{ $slab->weight }} KG</label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Shipping Type Filter -->
                                    <div class="mt-3">
                                        <span class="fw-bold text-primary">Shipping Type:</span>
                                        <div class="d-flex gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input shipping-filter" type="radio" name="shipping_type" value="forward" checked>
                                                <label class="form-check-label">Forward</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input shipping-filter" type="radio" name="shipping_type" value="rto">
                                                <label class="form-check-label">RTO</label>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Rate Card Table -->
                        <div class="col-12">
                            <div id="rate-card-table">
                                @include('users.wallet.rate_table', ['rates' => $rates, 'mode' => $mode])
                            </div>
                        </div>
                    </div>
                    <!-- end -->
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    $(document).ready(function() {
        function updateRateCard() {
            let mode = $('input[name="mode"]:checked').val();
            let weightSlab = $('input[name="weight_slab"]:checked').val();
            let shippingType = $('input[name="shipping_type"]:checked').val();

            console.log(weightSlab, 'weightSlab');

            $.ajax({
                url: "{{ route('wallet.fetchRates') }}",
                type: "GET",
                data: {
                    mode: mode,
                    weight_slab: weightSlab,
                    shipping_type: shippingType
                },
                success: function(response) {
                    $('#rate-card-table').html(response);
                }
            });
        }

        $('input[name="mode"]').change(function() {
            let mode = $(this).val();

            $.ajax({
                url: "{{ route('wallet') }}",
                type: "GET",
                data: {
                    mode: mode
                },
                success: function(response) {
                    $('.weight-slabs-container').html(response.weightSlabsHtml);
                    updateRateCard();
                }
            });
        });

        $(document).on('change', 'input[name="weight_slab"], input[name="shipping_type"]', function() {
            updateRateCard();
        });
    });
</script>
@endpush
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let form = document.getElementById('walletForm');

        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        });
    });

    function applyPromo() {
        let promoCode = document.getElementById('promo_code').value;
        if (promoCode) {
            alert("Promo code applied: " + promoCode);
        } else {
            alert("Please enter a promo code.");
        }
    }
</script>

@endsection
@extends('layouts.admin')

@section('title', 'Create Shipment')

@section('content')
<div class="dashboard-main-body">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Pincode Serviceability</h6>
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

    {{-- Alert with Close Button --}}
    @if(session('result'))
        <div class="card mb-4">
            <div class="card-body d-flex flex-wrap align-items-center gap-3">
                @if(count(session('result')['serviceable']) > 0)
                    <div class="alert alert-success position-relative mb-0 px-3 py-2 d-flex align-items-center gap-2">
                         Pincode Serviceable:
                        <span>{{ implode(', ', session('result')['serviceable']) }}</span>
                    </div>
                @endif

                @if(count(session('result')['non_serviceable']) > 0)
                    <div class="alert alert-danger position-relative mb-0 px-3 py-2 d-flex align-items-center gap-2">
                        Pincode Non-Serviceable:
                        <span>{{ implode(', ', session('result')['non_serviceable']) }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Form Section --}}
    <div class="card mb-12">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <form class="d-flex align-items-center flex-wrap gap-3" method="POST" action="{{ route('pincode.check') }}">
                @csrf

                <span class="text-md fw-medium text-secondary-light mb-0">Check Serviceability:</span>

                <input 
                    type="text" 
                    name="pincodes" 
                    class="form-control bg-base h-40-px w-auto px-3" 
                    placeholder="Enter pincodes (e.g. 110001,560034)" 
                    required
                >

                <select 
                    name="courier_id" 
                    class="form-control bg-base h-40-px w-auto px-3" 
                    required
                >
                    <option value="">-- Select Courier --</option>
                    @foreach($couriers as $courier)
                        <option value="{{ $courier->id }}">{{ $courier->name }}</option>
                    @endforeach
                </select>

                    <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-12 radius-8 d-flex align-items-center gap-1">
                        <iconify-icon icon="ion:search-outline" class="me-1"></iconify-icon>
                        <span>Search</span>
                    </button>

            </form>
        </div>
    </div>

</div>

<script src="{{ asset('assets/js/order/app.js') }}"></script>
@endsection

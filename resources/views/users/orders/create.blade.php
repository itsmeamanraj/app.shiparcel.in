@extends('layouts.admin')

@section('title', 'Create Warehouse')

@section('content')
<div class="dashboard-main-body">

    <div class="card">
        <div class="card-body">
            <h6 class="mb-4 text-xl">Create Single Order</h6>
            <p class="text-neutral-500">Fill up your details and proceed next steps.</p>
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
            <!-- Form Wizard Start -->
            <div class="form-wizard">
                <form class="form" method="POST" id="form-builder" action="{{route('create.order')}}">
                    @csrf
                    <div class="form-wizard-header overflow-x-auto scroll-sm pb-8 my-32">
                        <ul class="list-unstyled form-wizard-list style-two">
                            <li class="form-wizard-list__item active">
                                <div class="form-wizard-list__line">
                                    <span class="count">1</span>
                                </div>
                                <span class="text text-xs fw-semibold">Pickup Address </span>
                            </li>
                            <li class="form-wizard-list__item">
                                <div class="form-wizard-list__line">
                                    <span class="count">2</span>
                                </div>
                                <span class="text text-xs fw-semibold">Consignee Details</span>
                            </li>
                            <li class="form-wizard-list__item">
                                <div class="form-wizard-list__line">
                                    <span class="count">3</span>
                                </div>
                                <span class="text text-xs fw-semibold">Shipment Details</span>
                            </li>
                            <li class="form-wizard-list__item">
                                <div class="form-wizard-list__line">
                                    <span class="count">4</span>
                                </div>
                                <span class="text text-xs fw-semibold">Package Details</span>
                            </li>
                        </ul>
                    </div>

                    <fieldset class="wizard-fieldset show">
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <label class="form-label">Select Pickup Address<span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select class="form-control wizard-required" name="pickup_address" data-size="7"
                                        data-live-search="true" tabindex="null" required>
                                        <option value="">Select</option>
                                        @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">
                                            {{ $warehouse->address_title }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <div class="form-group ">
                                    <div class="form-switch switch-primary d-flex align-items-center gap-3">
                                        <input class="form-check-input" name="is_return_address" type="checkbox"
                                            role="switch" id="toggleReturnAddress">
                                        <label class="form-check-label line-height-1 fw-medium text-secondary-light"
                                            for="toggleReturnAddress">Return
                                            Address (if any)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3" id="returnAddressDropdown">
                            <div class="col-sm-6">
                                <label class="form-label">Select Return Address*</label>
                                <div class="position-relative">
                                    <select class="form-control" name="return_address" data-size="7"
                                        data-live-search="true" tabindex="null">
                                        <option value="">Select</option>
                                        @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">
                                            {{ $warehouse->address_title }}
                                        </option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group text-end">
                                <button type="button"
                                    class="form-wizard-next-btn btn btn-primary-600 px-32">Next</button>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="wizard-fieldset">
                        <div class="row gy-3">
                            <div class="row">
                                <div class="col mb-3">
                                    <label class="form-label">Consignee Name<span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control wizard-required" id="consignee_name"
                                            name="consignee_name" placeholder="Enter consingnee Name" required>
                                        <div class="wizard-form-error"></div>
                                    </div>
                                </div>
                                <div class="col mb-3">
                                    <label class="form-label">Consignee Contact<span
                                            class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="number" id="consignee_mobile" name="consignee_mobile"
                                            class="form-control wizard-required"
                                            placeholder="Enter Consignee contact..." minlength="10" maxlength="10"
                                            required>
                                        <div class="wizard-form-error"></div>
                                    </div>
                                </div>
                                <div class="col mb-3">
                                    <label for="form-label">Address Line<span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" id="consignee_address1" name="consignee_address1"
                                            class="form-control wizard-required"
                                            placeholder="Enter Consignee address..." required>
                                        <div class="wizard-form-error"></div>
                                    </div>
                                </div>
                                <div class="col mb-3">
                                    <label for="form-label">Email<span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" id="consignee_emailid" name="consignee_emailid"
                                            class="form-control wizard-required"
                                            placeholder="Enter Consignee email...">
                                        <div class="wizard-form-error"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <div class="row">
                                    <div class="col-md-3 col-lg-3 col-xl-3 mb-3">
                                        <label class="form-label">Pincode<span class="text-danger">*</span></label>
                                        <div class="position-relative">
                                            <input type="number" class="form-control wizard-required"
                                                id="consignee_pincode" name="consignee_pincode"
                                                placeholder="Enter consingnee pincode" minlength="6" maxlength="6">
                                            <div class="wizard-form-error"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-lg-3 col-xl-3 mb-3">
                                        <label class="form-label">City<span class="text-danger">*</span></label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control wizard-required"
                                                id="consignee_city" name="consignee_city"
                                                placeholder="Enter consingnee city" required>
                                            <div class="wizard-form-error"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-lg-3 col-xl-3 mb-3">
                                        <label class="form-label">State<span class="text-danger">*</span></label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control wizard-required"
                                                id="consignee_state" name="consignee_state"
                                                placeholder="Enter consingnee state" required>
                                            <div class="wizard-form-error"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-lg-3 col-xl-3 mb-3">
                                        <label class="form-label">Country<span class="text-danger">*</span></label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control wizard-required"
                                                id="consignee_country" name="consignee_country"
                                                placeholder="Enter consingnee country" required>
                                            <div class="wizard-form-error"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="form-group d-flex align-items-center justify-content-end gap-8">
                                <button type="button"
                                    class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>
                                <button type="button"
                                    class="form-wizard-next-btn btn btn-primary-600 px-32">Next</button>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="wizard-fieldset">
                        <div class="row gy-3">
                            <div class="form-group mb-0">
                                <div class="row mb-3">
                                    <div class="col-md-3 col-lg-3 col-xl-3 mb-3">
                                        <label class="form-label">Order ID<span class="text-danger">*</span></label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control wizard-required" id="order_id"
                                                name="order_id" placeholder="Enter order ID" required>
                                            <div class="wizard-form-error"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-lg-3 col-xl-3 mb-3">
                                        <label class="form-label">Payment Mode<span class="text-danger">*</span></label>
                                        <select name="payment_mode" id="payment_mode"
                                            class="form-control wizard-required"
                                            data-placeholder="Select Payment mode..." required>
                                            <option value="">-- Select Mode --</option>
                                            <option value="Prepaid">Prepaid</option>
                                            <option value="Cod">COD</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <h6>Product Details:</h6>
                            <div id="div_add_products" class="">
                                <div class="row div_add_products">
                                    <div class="col-md-4 col-lg-4 col-xl-4 mb-3">
                                        <label for="form-label">Product Name<span class="text-danger">*</span></label>
                                        <input type="text" id="product_name" name="product_name[]" class="form-control wizard-required" placeholder="Enter product name..." required>
                                    </div>
                                    <div class="col-md-2 col-lg-2 col-xl-2 mb-3">
                                        <label for="form-label">Quantity<span class="text-danger">*</span></label>
                                        <input type="text" id="product_quantity" name="product_quantity[]" class="form-control wizard-required" placeholder="Enter product Quantity..." required>
                                    </div>
                                    <div class="col-md-2 col-lg-2 col-xl-2 mb-3">
                                        <label for="form-label">Product Value<span class="text-danger">*</span></label>
                                        <input type="text" id="product_value" name="product_value[]" class="form-control wizard-required" placeholder="Enter product value..." required>
                                    </div>
                                    <div class="col-md-3 col-lg-3 col-xl-3 mb-3">
                                        <label for="form-label">Category<span class="text-danger">*</span></label>
                                        <input type="text" id="product_category" name="product_category[]" class="form-control wizard-required" placeholder="Enter product category..." required>
                                    </div>
                                    <div class="col-md-3 col-lg-3 col-xl-3 mb-3">
                                        <label for="form-label">SKU<span class="text-danger">*</span></label>
                                        <input type="text" id="product_sku" name="product_sku[]" class="form-control wizard-required" placeholder="Enter SKU..." required>
                                    </div>
                                    <div class="col-md-1 col-lg-1 col-xl-1 mb-3">
                                        <label for="form-label" style="padding-top:30px;">&#160;</label>
                                        <button type="button" class="btn btn-success m-0" data-toggle="tooltip" id="btn_add_products" name="btn_add_products" data-original-title="Add Product">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="0.88em" height="1em" viewBox="0 0 448 512">
                                                <path fill="currentColor" d="M64 80c-8.8 0-16 7.2-16 16v320c0 8.8 7.2 16 16 16h320c8.8 0 16-7.2 16-16V96c0-8.8-7.2-16-16-16zM0 96c0-35.3 28.7-64 64-64h320c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64zm200 248v-64h-64c-13.3 0-24-10.7-24-24s10.7-24 24-24h64v-64c0-13.3 10.7-24 24-24s24 10.7 24 24v64h64c13.3 0 24 10.7 24 24s-10.7 24-24 24h-64v64c0 13.3-10.7 24-24 24s-24-10.7-24-24"></path>
                                            </svg></button>
                                    </div>
                                </div>
                            </div>
                            <h6>Order Details:</h6>
                            <div class="form-group mb-0">
                                <div class="row">
                                    <div class="col-md-3 col-lg-3 col-xl-3 mb-3">
                                        <label for="form-label">Order Amount<span class="text-danger">*</span></label>
                                        <input type="text" id="order_amount" name="order_amount" class="form-control wizard-required" placeholder="Enter amount..." required>
                                    </div>

                                    <div class="col-md-3 col-lg-3 col-xl-3 mb-3">
                                        <label for="form-label">GST Amount<span class="text-danger">*</span></label>
                                        <input type="text" id="tax_amount" name="tax_amount" class="form-control wizard-required" placeholder="Enter gst..." required>
                                    </div>

                                    <div class="col-md-3 col-lg-3 col-xl-3 mb-3">
                                        <label for="form-label">Total Amount<span class="text-danger">*</span></label>
                                        <input type="text" id="total_amount" name="total_amount" class="form-control wizard-required" placeholder="Enter total amount..." required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group d-flex align-items-center justify-content-end gap-8">
                                <button type="button"
                                    class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>
                                <button type="button"
                                    class="form-wizard-next-btn btn btn-primary-600 px-32">Next</button>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="wizard-fieldset">
                        <div class="form-group">

                            <div class="row form-group mb-0">
                                <div class="col-md-4 col-lg-4 col-xl-4 mb-3">
                                    <label for="shipment_weight">Actual Weight<span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" id="shipment_weight" value=""
                                            name="shipment_weight[]" class="form-control"
                                            placeholder="Enter weight..." aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <span class="input-group-text alert-info" style="padding: 0.6rem .8rem;">KG</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8 col-lg-8 col-xl-8 mb-3">
                                    <label for="shipment_length">Dimensions<span class="text-danger">*</span></label>
                                    <div class="input-group align-items-center">
                                        <div class="row">
                                            <div class="position-relative col-md-4 col-lg-4 col-xl-4 mb-3">
                                                <div class="input-group">
                                                    <input type="text" id="shipment_length" value=""
                                                        name="shipment_length[]" class="form-control"
                                                        placeholder="Enter weight..." aria-describedby="basic-addon2"
                                                        placeholder="Enter length..." onblur="calcvolwt();">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text alert-info" style="padding: 0.6rem .8rem;">cm</span>
                                                    </div>
                                                </div>


                                            </div>

                                            <div class="position-relative col-md-4 col-lg-4 col-xl-4 mb-3">
                                                <div class="input-group">
                                                    <input type="text" id="shipment_width" value=""
                                                        name="shipment_width[]" class="form-control"
                                                        placeholder="Enter width..." onblur="calcvolwt();">
                                                    <div class="input-group-append">

                                                        <span class="input-group-text alert-info" style="padding: 0.6rem .8rem;">cm</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="position-relative col-md-4 col-lg-4 col-xl-4 mb-3">
                                                <div class="input-group">
                                                    <input type="text" id="shipment_height" value=""
                                                        name="shipment_height[]"
                                                        class="form-control ui-wizard-content"
                                                        placeholder="Enter height..." onblur="calcvolwt();">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text alert-info" style="padding: 0.6rem .8rem;">cm</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group d-flex align-items-center justify-content-end gap-8">
                            <button type="button"
                                class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>
                            <!-- <button type="button" class="form-wizard-submit btn btn-primary-600 px-32">Publish</button> -->
                            <button type="submit"
                                class="form-wizard-submit btn btn-primary-600 px-32">Create Order</button>

                        </div>
                    </fieldset>
                </form>
            </div>
            <!-- Form Wizard End -->
        </div>
    </div>
</div>
<script src="{{ asset('assets/js/vendor/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/order/app.js') }}"></script>
@endsection

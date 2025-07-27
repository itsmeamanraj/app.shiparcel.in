@extends('layouts.admin')

@section('title', 'Create Warehouse')

@section('content')
<div class="dashboard-main-body">
    <div class="container">
        <div class="card">
            <div class="row">
                <div class="col-md-12">
                    <div class="card-header">
                        <h3 class="card-title">Create Warehouse</h3>
                        @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                        @endif

                        @if (session('error'))
                        <div class="alert alert-danger">
                            @if(is_array(session('error')))
                            {{ json_encode(session('error')) }}
                            @else
                            {{ session('error') }}
                            @endif
                        </div>
                        @endif

                    </div>
                    <form id="warehouseForm" method="POST" action="{{ route('create.warehouse') }}">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Address Title
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="address_title"
                                    placeholder="Enter Address Title" name="address_title" />
                                <small class="text-danger" id="address_title_error"></small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Sender Name
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sender_name"
                                    placeholder="Enter Sender Name" name="sender_name" />
                                <small class="text-danger" id="sender_name_error"></small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Full Address
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_address"
                                    placeholder="Enter Full Address" name="full_address" />
                                <small class="text-danger" id="full_address_error"></small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone"
                                    placeholder="Enter Phone" name="phone" />
                                <small class="text-danger" id="phone_error"></small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Pincode
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pincode"
                                    placeholder="Enter Pincode" name="pincode" />
                                <small class="text-danger" id="pincode_error"></small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">city
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="city"
                                    placeholder="Enter Pincode" name="city" />
                                <small class="text-danger" id="city"></small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">State
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="state"
                                    placeholder="Enter Pincode" name="state" />
                                <small class="text-danger" id="state"></small>
                            </div>
                            <div class="form-group mt-6">
                                <button type="button" id="submitButton" class="btn btn-primary mr-2">Submit</button>
                                <button type="reset" class="btn btn-secondary">Cancel</button>
                            </div>
                        </div>
                    </form>
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('submitButton').addEventListener('click', function() {
        // Clear previous errors
        const errorFields = ['address_title', 'sender_name', 'full_address', 'phone', 'pincode'];
        errorFields.forEach(field => {
            document.getElementById(`${field}_error`).textContent = '';
        });

        // Validate form fields
        const addressTitle = document.getElementById('address_title').value.trim();
        const senderName = document.getElementById('sender_name').value.trim();
        const fullAddress = document.getElementById('full_address').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const pincode = document.getElementById('pincode').value.trim();

        let isValid = true;

        if (!addressTitle) {
            document.getElementById('address_title_error').textContent = 'Address Title is required.';
            isValid = false;
        }

        if (!senderName) {
            document.getElementById('sender_name_error').textContent = 'Sender Name is required.';
            isValid = false;
        }

        if (!fullAddress) {
            document.getElementById('full_address_error').textContent = 'Full Address is required.';
            isValid = false;
        }

        if (!phone || !/^\d{10}$/.test(phone)) {
            document.getElementById('phone_error').textContent = 'Enter a valid 10-digit phone number.';
            isValid = false;
        }

        if (!pincode || !/^\d{6}$/.test(pincode)) {
            document.getElementById('pincode_error').textContent = 'Enter a valid 6-digit pincode.';
            isValid = false;
        }

        // Submit the form if valid
        if (isValid) {
            document.getElementById('warehouseForm').submit();
        }
    });
</script>
@endsection

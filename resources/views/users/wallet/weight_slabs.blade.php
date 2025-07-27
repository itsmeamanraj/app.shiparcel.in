<div class="container mt-3 weight-slabs-container">
    <span class="title text-primary">Select Weight Slab:</span><br>
    @foreach ($weightSlabs->groupBy('courier_company_id') as $companyId => $companySlabs)
    <div class="courier-group">
        <h5>{{ $companySlabs->first()->courierCompany->name }}</h5>
        @foreach ($companySlabs as $slab)
        <div class="form-check form-check-inline">
            <input class="form-check-input weight-filter" type="radio" name="weight_slab" value="{{ $slab->id }}">
            <label class="form-check-label">{{ $slab->weight }} KG</label>
        </div>
        @endforeach
    </div>
    @endforeach
</div>

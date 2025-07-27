@if ($mode == 'air')
<h4>Air Courier Rates</h4>
@else
<h4>Surface Courier Rates</h4>
@endif

<table class="table table-bordered mt-3">
    <thead class="table-light">
        <tr>
            <th rowspan="2">Couriers</th>
            <th colspan="6">FWD | Add.<br><small>0.50KG(s) | 0.50KG(s)</small></th>
            <th rowspan="2">COD Charges | COD %</th>
        </tr>
        <tr>
            <th>A</th>
            <th>B</th>
            <th>C</th>
            <th>D</th>
            <th>E</th>
            <th>F</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rates->groupBy('courier_company_id') as $companyId => $companyRates)
        @php
        $company = $companyRates->first()->courierCompany;
        @endphp
        <tr>
            <td>{{ $company->name }}</td>
            @foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $zone)
            @php
            $rate = $companyRates->where('zone', $zone)->first();
            @endphp
            <td>
                @if($rate)
                Rs.{{ $rate->forward_fwd }} | Rs.{{ $rate->additional_fwd }}
                @else
                N/A
                @endif
            </td>
            @endforeach
            <td>Rs.30 | 1.8%</td> <!-- Static COD charges, replace with actual -->
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">No rates available.</td>
        </tr>
        @endforelse
    </tbody>
</table>
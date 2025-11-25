@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Sales Return Management</h4>
                    <h6>Manage Sales Return Efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    @if(Auth::check() && Auth::user()->usertype === 'admin')
                                    <th>Party ID</th>
                                    @elseif(Auth::check() && Auth::user()->usertype === 'distributor')
                                    <th>Customer Name</th>
                                    @endif

                                    <th>Item Name</th>
                                    <th>Pcs/Carton</th>
                                    <th>Carton Qty</th>
                                    <th>Pcs Qty</th>
                                    <th>Rate</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                    <th>Total Return Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesReturns as $saleReturn)
                                @php
                                $itemNames = explode(',', $saleReturn->item_names);
                                $pcsPerCarton = explode(',', $saleReturn->pcs_per_carton);
                                $cartonQty = explode(',', $saleReturn->carton_qty);
                                $pcsQty = explode(',', $saleReturn->pcs_qty);
                                $rates = explode(',', $saleReturn->rate);
                                $discounts = explode(',', $saleReturn->discount);
                                $totals = explode(',', $saleReturn->total);
                                $itemCount = count($itemNames);
                                @endphp
                                @for($i = 0; $i < $itemCount; $i++)
                                    <tr>
                                    @if($i == 0)
                                    <td rowspan="{{ $itemCount }}">{{ $saleReturn->invoice_number }}</td>
                                    <td rowspan="{{ $itemCount }}">
                                        @if($saleReturn->sale_type == 'distributor')
                                        {{ $saleReturn->distributor->Customer ?? 'N/A' }}
                                        @elseif($saleReturn->sale_type == 'customer')
                                        {{ $saleReturn->customer->customer_name ?? 'N/A' }}
                                        @else
                                        N/A
                                        @endif

                                    </td>
                                    @endif
                                    <td>{{ $itemNames[$i] ?? '' }}</td>
                                    <td>{{ $pcsPerCarton[$i] ?? '' }}</td>
                                    <td>{{ $cartonQty[$i] ?? '' }}</td>
                                    <td>{{ $pcsQty[$i] ?? '' }}</td>
                                    <td>{{ $rates[$i] ?? '' }}</td>
                                    <td>{{ $discounts[$i] ?? '' }}</td>
                                    <td>{{ $totals[$i] ?? '' }}</td>
                                    @if($i == 0)
                                    <td rowspan="{{ $itemCount }}" class="text-danger">{{ $saleReturn->total_return_amount }}</td>
                                    @endif
                                    </tr>
                                    @endfor
                                    @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')
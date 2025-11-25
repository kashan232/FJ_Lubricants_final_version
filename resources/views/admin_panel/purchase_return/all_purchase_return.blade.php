@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Purchase Returns</h4>
                    <h6>Manage Purchases Returns Efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Category</th>
                                    <th>Subcategory</th>
                                    <th>Item</th>
                                    <th>Rate</th>
                                    <th>Carton Qty</th>
                                    <th>Return Qty</th>
                                    <th>PCS/Carton</th>
                                    <th>Measurement</th>
                                    <th>Return Amount</th>
                                    <th>Return Liters</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($Purchases as $purchase)
                                <tr>
                                    <td>{{ $purchase->purchase->invoice_number }}</td>
                                    <td>
                                        @foreach(json_decode($purchase->category) as $cat)
                                        {{ $cat }}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->subcategory) as $subcat)
                                        {{ $subcat }}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->item) as $item)
                                        {{ $item }}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->rate) as $rate)
                                        {{ $rate }}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->carton_qty) as $qty)
                                        {{ $qty }}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->return_qty) as $rqty)
                                        {{ $rqty }}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->pcs_carton) as $pcs)
                                        {{ $pcs }}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->measurement) as $unit)
                                        {{ $unit }}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->return_amount) as $amount)
                                        {{ $amount }}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->return_liters) as $liters)
                                        {{ $liters }}<br>
                                        @endforeach
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($purchase->created_at)->format('d M Y h:i A') }}</td>
                                </tr>
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
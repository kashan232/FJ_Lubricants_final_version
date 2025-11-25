@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Local Sales Details</h4>
                    <h6>Complete Local Sales Information</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    <h5 class="mb-3"><strong>Invoice #{{ $sale->invoice_number }}</strong></h5>

                    <table class="table">
                        <tr>
                            <th>Date:</th>
                            <td>{{ $sale->Date }}</td>
                        </tr>
                        <tr>
                            <th>Customer:</th>
                            <td>{{ $sale->customer->customer_name }}</td>
                        </tr>
                        <tr>
                            <th>City:</th>
                            <td>{{ $sale->customer_city }}</td>
                        </tr>
                        <tr>
                            <th>Area:</th>
                            <td>{{ $sale->customer_area }}</td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td>{{ $sale->customer_address }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $sale->customer_phone }}</td>
                        </tr>
                        <tr>
                            <th>Booker:</th>
                            <td>{{ $sale->Booker }}</td>
                        </tr>
                        <tr>
                            <th>Salesman:</th>
                            <td>{{ $sale->Saleman }}</td>
                        </tr>
                    </table>

                    <h5 class="mt-4"><strong>Sale Items</strong></h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Subcategory</th>
                                    <th>Code</th>
                                    <th>Item</th>
                                    <th>Size</th>
                                    <th>Packing</th>
                                    <th>Carton Qty</th>
                                    <th>Pcs</th>
                                    <th>Rate</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $categories = json_decode($sale->category, true);
                                    $subcategories = json_decode($sale->subcategory, true);
                                    $codes = json_decode($sale->code, true);
                                    $items = json_decode($sale->item, true);
                                    $sizes = json_decode($sale->size, true);
                                    $packings = json_decode($sale->pcs_carton, true);
                                    $carton_qtys = json_decode($sale->carton_qty, true);
                                    $pcs = json_decode($sale->pcs, true);
                                    $rates = json_decode($sale->rate, true);
                                    $discounts = json_decode($sale->discount, true);
                                    $amounts = json_decode($sale->amount, true);
                                @endphp

                                @foreach ($categories as $index => $category)
                                    <tr>
                                        <td>{{ $category }}</td>
                                        <td>{{ $subcategories[$index] ?? '-' }}</td>
                                        <td>{{ $codes[$index] ?? '-' }}</td>
                                        <td>{{ $items[$index] ?? '-' }}</td>
                                        <td>{{ $sizes[$index] ?? '-' }}</td>
                                        <td>{{ $packings[$index] ?? '-' }}</td>
                                        <td>{{ $carton_qtys[$index] ?? '-' }}</td>
                                        <td>{{ $pcs[$index] ?? '-' }}</td>
                                        <td>{{ number_format($rates[$index] ?? 0, 2) }}</td>
                                        <td>{{ number_format($discounts[$index] ?? 0, 2) }}</td>
                                        <td>{{ number_format($amounts[$index] ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-4"><strong>Financial Summary</strong></h5>
                    <table class="table">
                        <tr>
                            <th>Grand Total:</th>
                            <td>{{ number_format($sale->grand_total, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Discount:</th>
                            <td>{{ number_format($sale->discount_value, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Net Amount:</th>
                            <td>{{ number_format($sale->net_amount, 2) }}</td>
                        </tr>
                    </table>

                    <a href="{{ route('all-local-sale') }}" class="btn btn-primary mt-3">Back to Sales List</a>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')

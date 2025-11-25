@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <!-- Payment Modal -->
    <div class="modal fade" id="payModal" tabindex="-1" aria-labelledby="payModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="payModalLabel">Make Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('vendors-payment') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="purchase_id" id="purchase_id">
                        <input type="hidden" name="vendor_id" id="vendor_id">
                        <div class="mb-3">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" class="form-control" id="Invoice_Number" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Invoice Grand Total</label>
                            <input type="text" class="form-control" id="grand_total" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remaining Amount</label>
                            <input type="text" class="form-control" id="remaining_amount" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Amount Paid</label>
                            <input type="number" class="form-control" name="amount_paid" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" class="form-control" name="payment_date" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Make Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Purchase Management</h4>
                    <h6>Manage Purchases Efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif
                    @if (session()->has('error'))
                    <div class="alert alert-danger">
                        <strong>Error!</strong> {{ session('error') }}.
                    </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Purchase Date</th>
                                    <th>Party Name</th>
                                    <th>Category</th>
                                    <th>Subcategory</th>
                                    <th>Item</th>
                                    <th>Rate</th>
                                    <th>Carton Qty</th>
                                    <th>Pcs</th>
                                    <th>Amount</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($Purchases as $purchase)
                                <tr>
                                    <td>
                                        {{ $purchase->invoice_number }}
                                        @if($purchase->return_status == 1)
                                        <span class="badge bg-danger text-white ms-2">Returned</span>
                                        @endif
                                    </td>
                                    <td>{{ $purchase->purchase_date }} <br>{{ $purchase->vendorLedger->closing_balance ?? 'N/A' }}</td>
                                    <td>{{ $purchase->vendor?->Party_name ?? 'N/A' }}</td>

                                    <td>
                                        @foreach(json_decode($purchase->category) as $category)
                                        {{ $category }},
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->subcategory) as $subcategory)
                                        {{ $subcategory }},
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->item) as $item)
                                        {{ $item }},
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->rate) as $rate)
                                        {{ $rate }},
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->carton_qty) as $carton_qty)
                                        {{ $carton_qty }},
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->pcs) as $pcs)
                                        {{ $pcs }},
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach(json_decode($purchase->amount) as $amount)
                                        {{ $amount }},
                                        @endforeach
                                    </td>
                                    <td>{{ $purchase->grand_total }}</td>
                                    <td>
                                        <a href="{{ route('purchase.return.form', $purchase->id) }}" class="btn btn-dark btn-sm text-white">
                                            Purchase Return
                                        </a>
                                        <a href="{{ route('purchase.invoice', $purchase->id) }}" class="btn btn-primary btn-sm text-white">
                                            Invoice
                                        </a>
                                        <a href="{{ route('purchase.edit', $purchase->id) }}" class="btn btn-warning btn-sm text-white">
                                            Edit
                                        </a>

                                        <!-- <button class="btn btn-success btn-sm pay-btn"
                                            data-id="{{ $purchase->id }}"
                                            data-invoice-no="{{ $purchase->invoice_number }}"
                                            data-vendor-id="{{ $purchase->vendor->id }}"
                                            data-grand-total="{{ $purchase->grand_total }}"
                                            data-remaining-amount="{{ $purchase->remaining_amount }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#payModal">
                                            Pay
                                        </button> -->

                                    </td>
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

<script>
    $(document).ready(function() {
        $(document).on("click", ".pay-btn", function() {
            let purchaseId = $(this).data("id");
            let invoiceno = $(this).data("invoice-no");
            let vendorId = $(this).data("vendor-id");
            let grandTotal = $(this).data("grand-total");
            let remainingAmount = $(this).data("remaining-amount");

            $("#purchase_id").val(purchaseId);
            $("#Invoice_Number").val(invoiceno);
            $("#vendor_id").val(vendorId);
            $("#grand_total").val(grandTotal);
            $("#remaining_amount").val(remainingAmount);
        });
    });
</script>
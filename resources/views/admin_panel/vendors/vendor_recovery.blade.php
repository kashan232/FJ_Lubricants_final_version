@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
    <!-- Edit Payment Modal -->
    <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('update-vendor-payment') }}" method="POST">
                @csrf
                <input type="hidden" name="payment_id" id="modal_payment_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Vendor Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Vendor</label>
                            <input type="text" class="form-control" id="modal_vendor_name" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Original Amount</label>
                            <input type="text" class="form-control" id="modal_amount_paid" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Update Type</label>
                            <select class="form-control" name="adjust_type" required>
                                <option value="">-- Select --</option>
                                <option value="plus">+ plus</option>
                                <option value="minus">– minus</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Amount to Adjust</label>
                            <input type="number" step="0.01" class="form-control" name="adjust_amount" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control" name="description" id="modal_description">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" id="modal_date">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update Payment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Vendor Payments</h4>
                    <h6>Track all Payments from salesmen</h6>
                </div>
            </div>

            <div class="card">
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
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Party Code</th>
                                    <th>Party Name</th>
                                    <th>Paid Amount</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($VendorPayments as $key => $VendorPayment)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $VendorPayment->payment_date }}</td>
                                    <td>{{ $VendorPayment->vendor->Party_code ?? 'N/A' }}</td>
                                    <td>{{ $VendorPayment->vendor->Party_name ?? 'N/A' }}</td>
                                    <td>{{ $VendorPayment->amount_paid }}</td>
                                    <td>{{ $VendorPayment->description }}</td>
                                    <td>
                                        <button
                                            class="btn btn-sm btn-primary editPaymentBtn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editPaymentModal"
                                            data-id="{{ $VendorPayment->id }}"
                                            data-amount="{{ $VendorPayment->amount_paid }}"
                                            data-date="{{ $VendorPayment->payment_date }}"
                                            data-description="{{ $VendorPayment->description }}"
                                            data-vendor="{{ $VendorPayment->vendor->Party_name ?? 'N/A' }}">
                                            Edit
                                        </button>
                                    </td>

                                </tr>
                                @endforeach
                                @if($VendorPayments->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center">No Payments found.</td>
                                </tr>
                                @endif
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
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('editPaymentModal');

        document.querySelectorAll('.editPaymentBtn').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('modal_payment_id').value = this.dataset.id;
                document.getElementById('modal_amount_paid').value = this.dataset.amount;
                document.getElementById('modal_vendor_name').value = this.dataset.vendor;
                document.getElementById('modal_date').value = this.dataset.date;
                document.getElementById('modal_description').value = this.dataset.description;
            });
        });
    });
</script>
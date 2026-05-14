@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Customer Recoveries</h4>
                    <h6>Track all recoveries from salesmen</h6>
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
                                    <th>Sno</th>
                                    <th>Pay-id</th>
                                    <th>Date</th>
                                    <th>Shop</th>
                                    <th>Name</th>
                                    <th>Area</th>
                                    <th>Salesman</th>
                                    <th>Amount Paid</th>
                                    <th>Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($Recoveries as $key => $recovery)
                                    <tr id="recovery-row-{{ $recovery->id }}">
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $recovery->id }}</td>
                                        <td>{{ $recovery->date }}</td>
                                        <td>{{ $recovery->ledger->Customer->shop_name ?? 'N/A' }}</td>
                                        <td>{{ $recovery->ledger->Customer->customer_name ?? 'N/A' }}</td>
                                        <td>{{ $recovery->ledger->Customer->area ?? 'N/A' }}</td>
                                        <td>{{ $recovery->salesman }}</td>
                                        <td class="amount_paid">{{ number_format($recovery->amount_paid, 0) }}</td>
                                        <td class="remarks">{{ $recovery->remarks }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#editRecoveryModal{{ $recovery->id }}">
                                                Edit
                                            </button>
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

{{-- Modals moved outside the table for better compatibility with DataTables pagination --}}
@foreach($Recoveries as $recovery)
    <div class="modal fade" id="editRecoveryModal{{ $recovery->id }}" tabindex="-1" aria-labelledby="editRecoveryModalLabel{{ $recovery->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('customer_recovery.update', $recovery->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editRecoveryModalLabel{{ $recovery->id }}">Edit Customer Recovery</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <select name="customer_id" class="form-select" required>
                                <option value="" disabled>Select Customer</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" {{ ($recovery->ledger->customer_id ?? 0) == $cust->id ? 'selected' : '' }}>
                                        {{ $cust->customer_name }} ({{ $cust->shop_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Salesman</label>
                            <select name="salesman" class="form-select" required>
                                <option value="" disabled>Select Salesman</option>
                                @foreach($Salesmans as $saleman)
                                    <option value="{{ $saleman->name }}" {{ $recovery->salesman == $saleman->name ? 'selected' : '' }}>
                                        {{ $saleman->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Amount Paid</label>
                            <input type="number" name="amount_paid" class="form-control" value="{{ $recovery->amount_paid }}" min="0" step="any" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ $recovery->date }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control">{{ $recovery->remarks }}</textarea>
                        </div>

                        <div class="alert alert-danger d-none" id="editRecoveryError{{ $recovery->id }}"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Recovery</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@include('admin_panel.include.footer_include')

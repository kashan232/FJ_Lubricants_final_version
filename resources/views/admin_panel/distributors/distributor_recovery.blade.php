@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Distributor Recoveries</h4>
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
                                    <th>#</th>
                                    <th>Date</th>
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
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $recovery->date }}</td>
                                    <td>{{ $recovery->distributor->Customer ?? 'N/A' }}</td>
                                    <td>{{ $recovery->distributor->Area ?? 'N/A' }}</td>
                                    <td>{{ $recovery->salesman }}</td>
                                    <td>{{ number_format($recovery->amount_paid, 0) }}</td>
                                    <td>{{ $recovery->remarks }}</td>
                                    <td>
                                    <td>
                                    <td>
                                        @if(Auth::user()->usertype === 'admin')
                                        <a href="#" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#editRecoveryModal{{ $recovery->id }}">
                                            Edit
                                        </a>
                                        @else
                                        <button class="btn btn-sm btn-danger" disabled>No Action</button>
                                        @endif
                                        <div class="modal fade" id="editRecoveryModal{{ $recovery->id }}" tabindex="-1" aria-labelledby="editRecoveryModalLabel{{ $recovery->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST" action="{{ route('Distributor-recovery-update', $recovery->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Distributor Recovery</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">

                                                            <div class="mb-3">
                                                                <label class="form-label">Distributor</label>
                                                                <input type="text" class="form-control" value="{{ $recovery->distributor->Customer ?? 'N/A' }}" readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Select Salesman</label>
                                                                <select class="form-control" id="salesman" name="salesman" required>
                                                                    <option value="" disabled>Select Salesman</option>
                                                                    @foreach($Salesmans as $saleman)
                                                                    <option value="{{ $saleman->name }}" {{ $recovery->salesman == $saleman->name ? 'selected' : '' }}>
                                                                        {{ $saleman->name }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Original Amount</label>
                                                                <input type="text" class="form-control" id="modal_amount_paid_{{ $recovery->id }}" value="{{ $recovery->amount_paid }}" readonly>
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
                                                                <input type="text" class="form-control" name="description" value="{{ $recovery->remarks }}" id="modal_description_{{ $recovery->id }}">
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Date</label>
                                                                <input type="date" class="form-control" name="date" value="{{ $recovery->date }}" id="modal_date_{{ $recovery->id }}">
                                                            </div>

                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success">Update Payment</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>

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
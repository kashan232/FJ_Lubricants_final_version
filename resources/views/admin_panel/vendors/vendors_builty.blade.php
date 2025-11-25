@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Vendor Builty</h4>
                    <h6>Manage Vendors Builty</h6>
                </div>
                <div class="page-btn">
                    @if(Auth::user()->usertype === 'admin')
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                        Add Vendor Builty
                    </button>
                    @else
                    <button class="btn btn-sm btn-danger" disabled>No Action</button>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Vendor</th>
                                    <th>Date</th>
                                    <th>Month</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($builtyRecords as $index => $builty)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $builty->vendor->Party_name ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($builty->date)->format('d-m-Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($builty->month)->format('F Y') }}</td>
                                    <td>{{ number_format($builty->amount) }}</td>
                                    <td>{{ $builty->description }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editVendorModal{{ $builty->id }}">
                                            Edit
                                        </button>

                                        @foreach($builtyRecords as $builty)
                                        <!-- Edit Vendor Builty Modal -->
                                        <div class="modal fade" id="editVendorModal{{ $builty->id }}" tabindex="-1" aria-labelledby="editVendorModalLabel{{ $builty->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form action="{{ route('update-vendors-builty', $builty->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Vendor Builty</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">

                                                            <div class="form-group mb-3">
                                                                <label for="vendor_id">Select Vendor</label>
                                                                <select name="vendor_id" class="form-control" required>
                                                                    <option value="">-- Select Vendor --</option>
                                                                    @foreach($Vendors as $vendor)
                                                                    <option value="{{ $vendor->id }}" {{ $vendor->id == $builty->vendor_id ? 'selected' : '' }}>
                                                                        {{ $vendor->Party_name }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="form-group mb-3">
                                                                <label for="date">Date</label>
                                                                <input type="date" name="date" value="{{ $builty->date }}" class="form-control" required>
                                                            </div>

                                                            <div class="form-group mb-3">
                                                                <label for="month">Month</label>
                                                                <input type="month" name="month" value="{{ \Carbon\Carbon::parse($builty->month)->format('Y-m') }}" class="form-control" required>
                                                            </div>

                                                            <div class="form-group mb-3">
                                                                <label for="amount">Amount</label>
                                                                <input type="number" name="amount" value="{{ $builty->amount }}" class="form-control" required>
                                                            </div>

                                                            <div class="form-group mb-3">
                                                                <label for="description">Description</label>
                                                                <textarea name="description" class="form-control" rows="2">{{ $builty->description }}</textarea>
                                                            </div>

                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success">Update Builty</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        @endforeach

                                    </td>
                                </tr>
                                @endforeach
                                @if(count($builtyRecords) == 0)
                                <tr>
                                    <td colspan="6" class="text-center">No records found</td>
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

<!-- Add Vendor Builty Modal -->
<div class="modal fade" id="addVendorModal" tabindex="-1" aria-labelledby="addVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('store-vendors-builty') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Vendor Builty</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="form-group mb-3">
                        <label for="vendor_id">Select Vendor</label>
                        <select name="vendor_id" class="form-control" required>
                            <option value="">-- Select Vendor --</option>
                            @foreach($Vendors as $vendor)
                            <option value="{{ $vendor->id }}|{{ $vendor->Party_name }}">{{ $vendor->Party_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="date">Date</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="month">Month</label>
                        <input type="month" name="month" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="amount">Amount</label>
                        <input type="number" name="amount" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="description">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Builty</button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('admin_panel.include.footer_include')
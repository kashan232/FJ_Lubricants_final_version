@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Vendors Ledger Management</h4>
                    <h6>Manage Vendors Ledger Efficiently</h6>
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
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Party Code</th>
                                    <th>Party Name</th>
                                    <th>Opening Balance</th>
                                    <th>Previous Balance</th>
                                    <th>Closing Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($VendorLedgers->isEmpty())
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function() {
                                            document.getElementById("global-loader").style.display = "none";
                                        });
                                    </script>
                                @endif
                                @forelse($VendorLedgers as $ledger)
                                    <tr>
                                        <td>{{ $ledger->vendor_id }}</td>
                                        <td>{{ $ledger->updated_at->format('Y-m-d') }}</td>
                                        <td>{{ $ledger->vendor->Party_code }}</td>
                                        <td>{{ $ledger->vendor->Party_name }}</td>
                                        <td>{{ number_format($ledger->opening_balance, 0) }}</td>
                                        <td>{{ number_format($ledger->previous_balance, 0) }}</td>
                                        <td id="closing_balance_{{ $ledger->id }}">{{ number_format($ledger->closing_balance, 0) }}</td>
                                       
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

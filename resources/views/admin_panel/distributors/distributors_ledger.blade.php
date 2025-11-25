@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    @if(Auth::user()->usertype === 'admin')
                    <h4>Distributors Ledger Management</h4>
                    <h6>Manage Distributors Ledger Efficiently</h6>
                    @else
                    <h4>Ledger Management</h4>
                    <h6>Manage Ledger Efficiently</h6>
                    @endif
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
                        @php
                        $isAdmin = Auth::user()->usertype === 'admin';
                        $isDistributor = Auth::user()->usertype === 'distributor';
                        @endphp

                        <table class="table datanew" style="{{ $isDistributor ? 'font-size:16px;' : '' }}">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Owner</th>
                                    <th>Opening Balance</th>
                                    <th>Previous Balance</th>
                                    <th>Closing Balance</th>

                                    {{-- Action column only for Admin --}}
                                    @if($isAdmin)
                                    <th>Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>

                                @if($DistributorLedgers->isEmpty())
                                <script>
                                    document.addEventListener("DOMContentLoaded", function() {
                                        document.getElementById("global-loader").style.display = "none";
                                    });
                                </script>
                                @endif

                                @forelse($DistributorLedgers as $ledger)
                                <tr>
                                    <td>{{ $ledger->distributor_id }}</td>
                                    <td>{{ $ledger->updated_at->format('Y-m-d') }}</td>
                                    <td>{{ $ledger->distributor?->Customer ?? 'N/A' }}</td>
                                    <td>{{ $ledger->distributor?->Owner ?? 'N/A' }}</td>
                                    <td>{{ number_format($ledger->opening_balance, 0) }}</td>
                                    <td>{{ number_format($ledger->previous_balance, 0) }}</td>

                                    {{-- Highlight closing balance for Distributor --}}
                                    <td id="closing_balance_{{ $ledger->id }}"
                                        style="{{ $isDistributor ? 'color:red; font-weight:bold; font-size:18px;' : '' }}">
                                        {{ number_format($ledger->closing_balance, 0) }}
                                    </td>

                                    {{-- Only Admin can add recovery --}}
                                    @if($isAdmin)
                                    <td>
                                        <!-- <button class="btn btn-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#recoveryModal"
                                            data-id="{{ $ledger->id }}"
                                            data-closing-balance="{{ $ledger->closing_balance }}">
                                            Add Recovery
                                        </button> -->
                                         <span class="btn btn-danger btn-sm">
                                            No Action
                                        </span>
                                    </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ $isAdmin ? '8' : '7' }}" class="text-center">No records found.</td>
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

<!-- Recovery Modal -->
<div class="modal fade" id="recoveryModal" tabindex="-1" aria-labelledby="recoveryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recoveryModalLabel">Add Recovery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="recoveryForm">
                    @csrf
                    <input type="hidden" id="ledger_id" name="ledger_id">
                    <div class="mb-3">
                        <label for="closing_balance" class="form-label">Closing Balance</label>
                        <input type="text" class="form-control" id="closing_balance" name="closing_balance" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="amount_paid" class="form-label">Amount Paid</label>
                        <input type="number" class="form-control" id="amount_paid" name="amount_paid" required>
                    </div>
                    <div class="mb-3">
                        <label for="salesman" class="form-label">Salesman</label>
                        <select class="form-control" name="salesman" id="salesman" required>
                            <option disabled>Select Salesman</option>
                            @foreach($Salesmans as $saleman)
                            <option value="{{ $saleman->name }}">{{ $saleman->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Save Recovery</button>
                </form>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let globalLoader = document.getElementById("global-loader");

        // Agar globalLoader exist karta hai, to usko hide karo
        if (globalLoader) {
            globalLoader.style.display = "none";
        }
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var recoveryModal = document.getElementById('recoveryModal');

        recoveryModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var ledgerId = button.getAttribute('data-id');
            var closingBalance = button.getAttribute('data-closing-balance');

            document.getElementById('ledger_id').value = ledgerId;
            document.getElementById('closing_balance').value = closingBalance;
        });

        document.getElementById('recoveryForm').addEventListener('submit', function(event) {
            event.preventDefault();

            var formData = new FormData(this);
            fetch("{{ route('recovery-store') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var ledgerId = document.getElementById('ledger_id').value;
                        var newClosingBalance = data.new_closing_balance;
                        document.getElementById('closing_balance_' + ledgerId).innerText = newClosingBalance;

                        var recoveryModal = bootstrap.Modal.getInstance(document.getElementById('recoveryModal'));
                        recoveryModal.hide();

                        // ✅ Show SweetAlert Success Message
                        Swal.fire({
                            icon: 'success',
                            title: 'Recovery Added!',
                            text: 'Closing balance updated successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload(); // ✅ Page refresh after alert closes
                        });
                    } else {
                        // ✅ Show SweetAlert Error Message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Something went wrong!',
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // ✅ Show Error Alert
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while processing your request.',
                    });
                });
        });
    });
</script>
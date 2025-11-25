@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Distributor Balance Transfer & Transfer Records</h4>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLedgerModal">
                    + Add Distributor Ledger
                </button>
            </div>

            <!-- Listing Table -->
            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>From Distributor</th>
                                    <th>To Distributor</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Reason</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transfers as $key => $transfer)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $transfer->from_distributor }}</td>
                                    <td>{{ $transfer->toDistributor->Customer ?? 'N/A' }}</td>
                                    <td>{{ number_format($transfer->amount) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($transfer->transfer_date)->format('Y-m-d') }}</td>
                                    <td>{{ $transfer->reason }}</td>
                                    <td>
                                        <button class="btn btn-primary editBtn"
                                            data-id="{{ $transfer->id }}"
                                            data-from="{{ $transfer->from_distributor }}"
                                            data-to="{{ $transfer->to_distributor }}"
                                            data-amount="{{ $transfer->amount }}"
                                            data-date="{{ $transfer->transfer_date }}"
                                            data-reason="{{ $transfer->reason }}">Edit</button>

                                        <button type="button" class="btn btn-sm btn-danger deleteBtn" data-id="{{ $transfer->id }}">
                                            Delete
                                        </button>
                                        <form id="delete-form-{{ $transfer->id }}" action="{{ route('transfers.destroy', $transfer->id) }}" method="POST" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>


                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No transfers found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>


                    </div>
                </div>
            </div>

            <!-- Modal: Add Ledger -->
            <!-- Modal: Add Ledger -->
            <div class="modal fade" id="addLedgerModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Distributor Balance Transfer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="transferForm" action="{{ route('distributor.transfer.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                                @csrf
                                <!-- Distributor Info -->
                                <div class="col-md-6">
                                    <label class="form-label required">From (Old Distributor)</label>
                                    <input type="text" name="from_distributor" class="form-control" placeholder="Enter old distributor">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required">To (New Distributor)</label>
                                    <select id="toDistributor" name="to_distributor" class="form-select" required>
                                        <option value="">— Select Distributor —</option>
                                        @foreach($distributors as $dist)
                                        <option value="{{ $dist->id }}">{{ $dist->Customer }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Balance Info -->
                                <div class="col-12 mt-2">
                                    <div class="p-3 border rounded bg-light">
                                        <div><strong>Closing Balance:</strong> <span id="closing_balance">0</span></div>
                                        <div><strong>Transfer Amount:</strong> <span id="transferAmount">0</span></div>
                                        <div>
                                            <strong>New Closing Balance:</strong>
                                            <span id="closingBalance" class="fw-bold text-danger fs-5">0</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transfer Details -->
                                <div class="col-md-6">
                                    <label class="form-label required">Amount</label>
                                    <input name="amount" id="amount" type="number" min="1" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required">Transfer Date</label>
                                    <input name="transfer_date" id="transferDate" type="date" class="form-control" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label required">Reason / Description</label>
                                    <textarea name="reason" id="reason" class="form-control" rows="2" required></textarea>
                                </div>

                                <!-- Actions -->
                                <div class="col-12 mt-3 d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary px-4">Record Transfer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editLedgerModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Distributor Transfer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editTransferForm" method="POST" class="row g-3">
                                @csrf
                                @method('PUT')

                                <input type="hidden" name="transfer_id" id="edit_transfer_id">

                                <div class="col-md-6">
                                    <label class="form-label required">From Distributor</label>
                                    <input type="text" name="from_distributor" id="edit_from" class="form-control" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required">To Distributor</label>
                                    <select id="edit_to" name="to_distributor" class="form-select" required>
                                        <option value="">— Select Distributor —</option>
                                        @foreach($distributors as $dist)
                                        <option value="{{ $dist->id }}">{{ $dist->Customer }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="p-3 border rounded bg-light">
                                        <div><strong>Closing Balance:</strong> <span id="edit_closing_balance">0</span></div>
                                        <div><strong>Transfer Amount:</strong> <span id="edit_transferAmount">0</span></div>
                                        <div>
                                            <strong>New Closing Balance:</strong>
                                            <span id="edit_closingBalance" class="fw-bold text-danger fs-5">0</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required">Amount</label>
                                    <input name="amount" id="edit_amount" type="number" min="1" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required">Transfer Date</label>
                                    <input name="transfer_date" id="edit_date" type="date" class="form-control" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label required">Reason</label>
                                    <textarea name="reason" id="edit_reason" class="form-control" rows="2" required></textarea>
                                </div>

                                <div class="col-12 mt-3 d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success px-4">Update Transfer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <!-- End Modal -->

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // named routes from blade (make sure these route names exist)
        let ledgerRoute = @json(route('distributor.balance.ledger', ['id' => ':id']));
        let updateRoute = @json(route('transfers.update', ['id' => ':id']));

        // ---------- helpers ----------
        const fmt = n => {
            n = Number(n) || 0;
            return n.toLocaleString();
        };
        const safe = id => document.getElementById(id);

        function fetchLedger(id) {
            return fetch(ledgerRoute.replace(':id', id))
                .then(r => r.json())
                .catch(err => {
                    console.error('Ledger fetch error', err);
                    return {
                        closing_balance: 0
                    };
                });
        }

        // ---------- ADD modal logic ----------
        let add_closing = 0;
        const add_to = safe('toDistributor');
        const add_amount = safe('amount');
        const add_closing_el = safe('closing_balance');
        const add_transfer_el = safe('transferAmount');
        const add_new_closing_el = safe('closingBalance');

        function calculateAdd() {
            if (!add_amount) return;
            let amt = parseFloat(add_amount.value) || 0;
            if (add_transfer_el) add_transfer_el.textContent = fmt(amt);
            if (add_new_closing_el) add_new_closing_el.textContent = fmt(add_closing + amt);
        }

        if (add_to) {
            add_to.addEventListener('change', function() {
                let id = this.value;
                if (!id) {
                    add_closing = 0;
                    if (add_closing_el) add_closing_el.textContent = '0';
                    calculateAdd();
                    return;
                }
                fetchLedger(id).then(data => {
                    add_closing = parseFloat(data.closing_balance) || 0;
                    if (add_closing_el) add_closing_el.textContent = fmt(add_closing);
                    calculateAdd();
                });
            });
        }
        if (add_amount) add_amount.addEventListener('input', calculateAdd);

        // ---------- EDIT modal logic ----------
        let edit_closing = 0; // ledger closing fetched for selected distributor (includes old transfer if same distributor)
        let edit_old_amount = 0; // old transfer amount from DB
        let edit_old_to = null; // old to_distributor id (from DB)
        let edit_selected_changed = false; // user changed distributor selection in modal

        const edit_to = safe('edit_to');
        const edit_amount = safe('edit_amount');
        const edit_closing_el = safe('edit_closing_balance');
        const edit_transfer_el = safe('edit_transferAmount');
        const edit_new_closing_el = safe('edit_closingBalance');
        const editForm = safe('editTransferForm');

        function calculateEdit() {
            if (!edit_amount) return;
            let newAmt = parseFloat(edit_amount.value) || 0;
            if (edit_transfer_el) edit_transfer_el.textContent = fmt(newAmt);

            let newClosing;
            // If user changed distributor in modal -> old transfer belonged to some other distributor,
            // so for the currently selected distributor new closing = its ledger + new amount
            if (edit_selected_changed) {
                newClosing = edit_closing + newAmt;
            } else {
                // same distributor -> ledger_closing already includes old_amount, so subtract old and add new
                newClosing = (edit_closing - edit_old_amount) + newAmt;
            }

            if (edit_new_closing_el) edit_new_closing_el.textContent = fmt(newClosing);
        }

        // If user changes the 'to' select inside edit modal
        if (edit_to) {
            edit_to.addEventListener('change', function() {
                let newId = this.value;
                // selectedChanged = (user-changed-to != old_to)
                edit_selected_changed = (String(newId) !== String(edit_old_to));
                if (!newId) {
                    edit_closing = 0;
                    if (edit_closing_el) edit_closing_el.textContent = '0';
                    calculateEdit();
                    return;
                }
                fetchLedger(newId).then(data => {
                    edit_closing = parseFloat(data.closing_balance) || 0;
                    if (edit_closing_el) edit_closing_el.textContent = fmt(edit_closing);
                    calculateEdit();
                });
            });
        }
        if (edit_amount) edit_amount.addEventListener('input', calculateEdit);

        // ---------- when Edit button clicked (fill modal) ----------
        document.querySelectorAll('.editBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                // read data attrs from button (make sure blade sets these attributes)
                let id = this.getAttribute('data-id');
                let from = this.getAttribute('data-from') || '';
                let to = this.getAttribute('data-to') || '';
                let amount = parseFloat(this.getAttribute('data-amount')) || 0;
                let date = this.getAttribute('data-date') || '';
                let reason = this.getAttribute('data-reason') || '';

                // set hidden + inputs
                const setIf = (id, val) => {
                    let el = safe(id);
                    if (el) el.value = val;
                };
                setIf('edit_transfer_id', id);
                setIf('edit_from', from);
                setIf('edit_to', to);
                setIf('edit_amount', amount);
                setIf('edit_date', date);
                setIf('edit_reason', reason);

                // store old values for calculation
                edit_old_amount = amount;
                edit_old_to = to;
                edit_selected_changed = false; // not changed yet

                // fetch ledger for the (preselected) 'to' distributor
                if (to) {
                    fetchLedger(to).then(data => {
                        edit_closing = parseFloat(data.closing_balance) || 0;
                        if (edit_closing_el) edit_closing_el.textContent = fmt(edit_closing);

                        // initial calculation
                        calculateEdit();
                    });
                } else {
                    edit_closing = 0;
                    if (edit_closing_el) edit_closing_el.textContent = '0';
                    calculateEdit();
                }

                // set action via named route
                if (editForm) {
                    editForm.action = updateRoute.replace(':id', id);
                }
                // show modal
                new bootstrap.Modal(document.getElementById('editLedgerModal')).show();
            });
        });

        // Debugging helper (optional)
        // window.__ledger_debug = { fetchLedger, calculateAdd, calculateEdit };
    });
</script>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".deleteBtn").forEach(button => {
            button.addEventListener("click", function() {
                let id = this.getAttribute("data-id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "This transfer will be deleted and ledger balance will be updated!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById("delete-form-" + id).submit();
                    }
                });
            });
        });
    });
</script>
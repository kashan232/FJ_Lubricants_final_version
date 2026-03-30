@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg border-0">
                <div class="card-body">
                    <h2 class="text-center fw-bold text-dark mb-1">BALANCE SHEET</h2>
                    <p class="text-center text-muted mb-4" id="asOfText">As of Today</p>
                    <hr>

                    <form id="balanceSheetForm" class="mb-5 no-print">
                        <div class="row justify-content-center align-items-end g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Select Date</label>
                                <input type="date" name="as_of_date" id="as_of_date" class="form-control form-control-lg" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="fetchBtn" class="btn btn-primary btn-lg w-100 fw-bold">
                                    GENERATE
                                </button>
                            </div>
                        </div>
                    </form>

                    <div id="bsContainer" style="display: none;">
                        <div class="row g-4">
                            <!-- Left Column: Assets -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded shadow-sm h-100">
                                    <h4 class="fw-bold text-primary border-bottom pb-2 mb-3">ASSETS</h4>
                                    <ul class="list-group list-group-flush bg-transparent">
                                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3">
                                            <span>Current Inventory (Stock Value)</span>
                                            <span class="fw-bold" id="inventory_val">0</span>
                                        </li>
                                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3">
                                            <span>Accounts Receivable (Distributors)</span>
                                            <span class="fw-bold" id="receivable_dist">0</span>
                                        </li>
                                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3">
                                            <span>Accounts Receivable (Local Customers)</span>
                                            <span class="fw-bold" id="receivable_cust">0</span>
                                        </li>
                                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3">
                                            <span>Cash / Bank (Estimated)</span>
                                            <span class="fw-bold" id="cash_estimated">0</span>
                                        </li>
                                    </ul>
                                    <div class="mt-4 p-3 bg-primary text-white rounded d-flex justify-content-between fw-bold fs-5">
                                        <span>TOTAL ASSETS</span>
                                        <span id="total_assets">0</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Liabilities & Equity -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded shadow-sm h-100 d-flex flex-column">
                                    <div>
                                        <h4 class="fw-bold text-danger border-bottom pb-2 mb-3">LIABILITIES</h4>
                                        <ul class="list-group list-group-flush bg-transparent">
                                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3">
                                                <span>Accounts Payable (Vendors)</span>
                                                <span class="fw-bold" id="payable_vend">0</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="mt-5 pt-4">
                                        <h4 class="fw-bold text-success border-bottom pb-2 mb-3">EQUITY</h4>
                                        <ul class="list-group list-group-flush bg-transparent">
                                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3">
                                                <span>Retained Earnings / Capital</span>
                                                <span class="fw-bold" id="equity_val">0</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="mt-auto p-3 bg-dark text-white rounded d-flex justify-content-between fw-bold fs-5">
                                        <span>TOTAL LIAB. & EQUITY</span>
                                        <span id="total_liab_equity">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-5 no-print">
                            <button onclick="window.print()" class="btn btn-outline-secondary px-5 py-2">
                                <i class="fa fa-print"></i> Print Balance Sheet
                            </button>
                        </div>
                    </div>

                    <div id="loader" class="text-center mt-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#fetchBtn').click(function() {
            const date = $('#as_of_date').val();
            
            $('#bsContainer').hide();
            $('#loader').show();
            $(this).prop('disabled', true);

            $.ajax({
                url: "{{ route('fetch.balance.sheet') }}",
                type: "GET",
                data: { as_of_date: date },
                success: function(res) {
                    $('#loader').hide();
                    $('#fetchBtn').prop('disabled', false);
                    $('#bsContainer').fadeIn();
                    $('#asOfText').text("As of " + date);

                    // Format values
                    const format = (v) => parseFloat(v).toLocaleString(undefined, { minimumFractionDigits: 2 });

                    $('#inventory_val').text(format(res.inventory_value));
                    $('#receivable_dist').text(format(res.receivable_distributors));
                    $('#receivable_cust').text(format(res.receivable_customers));
                    $('#cash_estimated').text(format(res.estimated_cash));
                    $('#total_assets').text(format(res.total_assets));

                    $('#payable_vend').text(format(res.payable_vendors));
                    $('#equity_val').text(format(res.equity));
                    $('#total_liab_equity').text(format(res.total_liabilities + res.equity));
                },
                error: function() {
                    $('#loader').hide();
                    $('#fetchBtn').prop('disabled', false);
                    alert("Failed to fetch balance sheet data.");
                }
            });
        });

        // Trigger on load
        $('#fetchBtn').click();
    });
</script>

<style>
    @media print {
        .no-print, .main-wrapper > :not(.page-wrapper) {
            display: none !important;
        }
        .page-wrapper { margin: 0; padding: 0; }
        .card { box-shadow: none !important; }
        .bg-light { background-color: #fff !important; border: 1px solid #ddd; }
        .bg-primary { background-color: #007bff !important; color: #fff !important; }
        .bg-dark { background-color: #343a40 !important; color: #fff !important; }
    }
</style>

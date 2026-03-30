@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg border-0">
                <div class="card-body">
                    <h2 class="text-center fw-bold text-dark mb-1">TRIAL BALANCE</h2>
                    <p class="text-center text-muted mb-4" id="asOfText">As of Today</p>
                    <hr>

                    <form id="trialBalanceForm" class="mb-5 no-print">
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

                    <div id="tbContainer" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped custom-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="py-3">Account Name / Ledger</th>
                                        <th class="text-end py-3">Debit (Rs.)</th>
                                        <th class="text-end py-3">Credit (Rs.)</th>
                                    </tr>
                                </thead>
                                <tbody id="tbBody">
                                    <!-- Dynamic rows will be added here -->
                                </tbody>
                                <tfoot class="table-dark">
                                    <tr>
                                        <th class="py-3">GRAND TOTAL</th>
                                        <th class="text-end py-3" id="totalDebit">0.00</th>
                                        <th class="text-end py-3" id="totalCredit">0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-5 p-4 bg-light rounded border">
                            <h5 class="fw-bold text-primary mb-3">Accounting Note: What is a Trial Balance?</h5>
                            <ul class="mb-0">
                                <li><strong>Definition:</strong> A Trial Balance is a report that lists the balances of all general ledger accounts of a business at a specific point in time.</li>
                                <li><strong>Purpose:</strong> Its primary purpose is to ensure that the total of all <strong>Debits</strong> equals the total of all <strong>Credits</strong>, confirming that the books are mathematically balanced.</li>
                                <li><strong>Components:</strong> 
                                    <ul>
                                        <li><strong>Debits:</strong> Include Assets (Cash, Receivables, Stock), Expenses, and Purchases.</li>
                                        <li><strong>Credits:</strong> Include Liabilities (Payables), Income (Sales), and Capital/Equity.</li>
                                    </ul>
                                </li>
                                <li><strong>Accuracy:</strong> If the totals match, it indicates that the double-entry bookkeeping rules have been followed correctly.</li>
                            </ul>
                        </div>

                        <div class="text-center mt-5 no-print">
                            <button onclick="window.print()" class="btn btn-outline-secondary px-5 py-2">
                                <i class="fa fa-print"></i> Print Trial Balance
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
            
            $('#tbContainer').hide();
            $('#loader').show();
            $(this).prop('disabled', true);

            $.ajax({
                url: "{{ route('fetch.trial.balance') }}",
                type: "GET",
                data: { as_of_date: date },
                success: function(res) {
                    $('#loader').hide();
                    $('#fetchBtn').prop('disabled', false);
                    $('#tbContainer').fadeIn();
                    $('#asOfText').text("As of " + date);

                    const format = (v) => parseFloat(v).toLocaleString(undefined, { minimumFractionDigits: 2 });
                    
                    let html = '';
                    
                    // Add Debits
                    res.debits.forEach(item => {
                        html += `
                            <tr>
                                <td class="fw-medium">${item.account}</td>
                                <td class="text-end text-success fw-bold">${format(item.amount)}</td>
                                <td class="text-end text-muted">-</td>
                            </tr>
                        `;
                    });
                    
                    // Add Credits
                    res.credits.forEach(item => {
                        html += `
                            <tr>
                                <td class="fw-medium">${item.account}</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-end text-danger fw-bold">${format(item.amount)}</td>
                            </tr>
                        `;
                    });

                    $('#tbBody').html(html);
                    $('#totalDebit').text(format(res.total_debit));
                    $('#totalCredit').text(format(res.total_credit));
                },
                error: function() {
                    $('#loader').hide();
                    $('#fetchBtn').prop('disabled', false);
                    alert("Failed to fetch trial balance data.");
                }
            });
        });

        // Trigger on load
        $('#fetchBtn').click();
    });
</script>

<style>
    .custom-table th, .custom-table td { padding: 12px 15px; }
    .custom-table thead th { background-color: #212529 !important; color: white !important; border-bottom: none; }
    .custom-table tfoot th { background-color: #212529 !important; color: white !important; }
    
    @media print {
        .no-print, .main-wrapper > :not(.page-wrapper) {
            display: none !important;
        }
        .page-wrapper { margin: 0; padding: 0; }
        .card { box-shadow: none !important; border: none !important; }
        .bg-light { background-color: #fff !important; border: 1px solid #ddd !important; }
        .table-dark { background-color: #212529 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
        .text-success { color: #198754 !important; }
        .text-danger { color: #dc3545 !important; }
    }
</style>

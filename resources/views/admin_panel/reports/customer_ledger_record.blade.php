@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
<style>
    .ledger-table {
    table-layout: fixed;
    width: 100%;
}

.ledger-table td:nth-child(3),
.ledger-table th:nth-child(3),
.ledger-table td:nth-child(4),
.ledger-table th:nth-child(4) {
    text-align: left;
    white-space: normal;
    word-break: break-word;
}

/* numeric columns tight */
.ledger-table td:not(:nth-child(3)):not(:nth-child(4)),
.ledger-table th:not(:nth-child(3)):not(:nth-child(4)) {
    white-space: nowrap;
}

</style>
    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-center fw-bold mb-4 text-primary">Customer Ledger Report</h3>

                    <form id="ledgerSearchForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fw-bold" for="Customer">Select Customer</label>
                                <select id="Customer" class="form-control">
                                    <option value="">-- Select Customer --</option>
                                    @foreach($Customers as $Customer)
                                    <option value="{{ $Customer->id }}"
                                        data-contact="{{ $Customer->phone_number }}"
                                        data-city="{{ $Customer->city }}"
                                        data-area="{{ $Customer->area }}">
                                        {{ $Customer->shop_name }} ({{ $Customer->customer_name }}) ({{ $Customer->area }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold">Contact</label>
                                <input type="text" id="contact" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="fw-bold">City</label>
                                <input type="text" id="city" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="fw-bold">Area</label>
                                <input type="text" id="area" class="form-control bg-light" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="fw-bold">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control bg-light">
                            </div>

                            <div class="col-md-6">
                                <label class="fw-bold">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control bg-light">
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button type="button" id="searchLedger" class="btn btn-primary btn-lg px-5">
                                Search
                            </button>
                        </div>
                    </form>
                    <div class="text-end mt-2">
                        <button id="downloadPdf" class="btn btn-danger">
                            Download PDF
                        </button>
                    </div>
                    <div id="ledgerResult" style="display: none;">
                        <div class="ledger-container mt-4">
                            <div class="ledger-header">Customer Detailed Ledger Statement</div>
                            <div class="ledger-info">
                                <span><strong>Customer:</strong> <span id="CustomerName"></span></span>
                                <span><strong>Duration:</strong> From <span id="startDate"></span> To <span id="endDate"></span></span>
                            </div>

                            <div style="overflow-x:auto;">
                               <table class="ledger-table">
                                    <colgroup>
                                        <col style="width:90px;">    <!-- Date -->
                                        <col style="width:110px;">   <!-- INV -->
                                        <col style="width:320px;">   <!-- Item (WIDE) -->
                                        <col style="width:280px;">   <!-- Description (WIDE) -->
                                        <col style="width:70px;">    <!-- Carton -->
                                        <col style="width:70px;">    <!-- PCS -->
                                        <col style="width:70px;">    <!-- Liters -->
                                        <col style="width:90px;">    <!-- Rate -->
                                        <col style="width:110px;">   <!-- Debit -->
                                        <col style="width:110px;">   <!-- Credit -->
                                        <col style="width:130px;">   <!-- Balance -->
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th style="font-weight:800;">Date</th>
                                            <th style="font-weight:800;">INV-No</th>
                                            <th style="font-weight:800;">Item</th>
                                            <th style="font-weight:800;">Description</th>
                                            <th style="font-weight:800;">Carton</th>
                                            <th style="font-weight:800;">PCS</th>
                                            <th style="font-weight:800;">Liters</th>
                                            <th style="font-weight:800;">Rate</th>
                                            <th style="font-weight:800;">Debit</th>
                                            <th style="font-weight:800;">Credit</th>
                                            <th style="font-weight:800;">Balance</th>
                                        </tr>

                                        <tr>
                                            <td colspan="10" class="opening-balance text-end pe-3">Opening Balance:</td>
                                            <td id="openingBalance">Rs. 0</td>
                                        </tr>
                                    </thead>

                                    <tbody id="ledgerData"></tbody>

                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-end pe-3"><strong>Totals:</strong></td>
                                            <td id="totalCartons">0</td>
                                            <td id="totalPcs">0</td>
                                            <td id="totalLiters">0</td>
                                            <td id="totalRateGrand">Rs. 0</td>
                                            <td id="totalDebit">Rs. 0</td>
                                            <td id="totalCredit">Rs. 0</td>
                                            <td id="closingBalance">Rs. 0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    .ledger-container {
        border: 2px solid black;
        padding: 10px;
        width: 100%;
        /* FULL WIDTH */
        max-width: 100%;
        /* full width allowed */
        margin: 0 auto;
        /* center (optional) */
        background: #fff;
    }

    .page-wrapper .content {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
        /* optional: remove extra padding space */
        padding: 0 !important;
    }

    .ledger-header {
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        padding: 10px;
        border-bottom: 2px solid black;
    }

    .ledger-info {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        border-bottom: 2px solid black;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid black;
        padding: 8px;
        text-align: center;
    }

    thead th {
        background: #f2f2f2;
    }

    .opening-balance {
        text-align: right;
        font-weight: bold;
        padding: 8px;
        border: 1px solid black;
    }
</style>
<script>
    // helper: format date dd/mm/yyyy
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // helper: sum numbers in comma-separated strings or numbers
    function sumNumericString(val) {
        if (!val && val !== 0) return 0;
        if (typeof val === 'number') return val;
        return String(val).split(',').reduce((acc, part) => {
            const n = parseFloat(part.toString().replace(/[^\d\.\-]/g, '')) || 0;
            return acc + n;
        }, 0);
    }

    $('#searchLedger').off('click').on('click', function() {
        var CustomerId = $('#Customer').val();
        let startDate = $('#start_date').val();
        let endDate = $('#end_date').val();

        if (!CustomerId) {
            alert('Please select a Customer.');
            return;
        }

        $.ajax({
            url: "{{ route('fetch-Customer-ledger') }}",
            type: "GET",
            data: {
                Customer_id: CustomerId,
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                const formattedStartDate = formatDate(response.startDate);
                const formattedEndDate = formatDate(response.endDate);

                $('#ledgerResult').show();
                $('#CustomerName').text($('#Customer option:selected').text());
                $('#startDate').text(formattedStartDate || "N/A");
                $('#endDate').text(formattedEndDate || "N/A");

                // totals
                let openingBalance = parseFloat(response.opening_balance) || 0;
                let balance = openingBalance;
                let totalDebit = 0,
                    totalCredit = 0;
                let totalCartons = 0,
                    totalPcs = 0,
                    totalLiters = 0;
                let grandAmount = 0; // purchases - returns under Rate column

                let ledgerHTML = "";

                // Opening row (always 11 cells)
                ledgerHTML += `
<tr>
    <td>${formattedStartDate || '-'}</td>
    <td>-</td>
    <td>-</td>
    <td class="fw-bold">Opening Balance</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td class="fw-bold text-primary">Rs. ${balance.toFixed(2)}</td>
</tr>`;

                // collect sale entries
                let allEntries = [];

                // local_sales => purchase-like entries (debits)
                response.local_sales.forEach(s => {
                    allEntries.push({
                        date: s.Date,
                        type: 'sale',
                        invoice_number: s.invoice_number,
                        amount: parseFloat(s.net_amount) || 0,
                        items: s.items ?? s.item ?? '-',
                        cartons: s.cartons ?? s.carton_qty ?? '-',
                        pcs: s.pcs ?? '-',
                        liters: s.liters ?? s.liter ?? '-',
                        rates: s.rates ?? s.rate ?? '-',
                        desc: `To Sale A/c (${s.Saleman || '-'})`
                    });
                });

                // recoveries => credits
                response.recoveries.forEach(r => {
                    allEntries.push({
                        date: r.date,
                        type: 'recovery',
                        amount: parseFloat(r.amount_paid) || 0,
                        desc: r.remarks || r.salesman || 'Recovery'
                    });
                });

                // sale_returns => credits and may contain items/cartons/pcs/liters
                response.sale_returns.forEach(r => {
                    allEntries.push({
                        date: r.created_at,
                        type: 'sale_return',
                        invoice_number: r.invoice_number,
                        amount: parseFloat(r.total_return_amount) || 0,
                        items: r.items || '-',
                        cartons: r.cartons || '-',
                        pcs: r.pcs || '-',
                        liters: r.liters || '-',
                        rates: r.rates || '-',
                        desc: 'Sale Return'
                    });
                });

                // sort by date; if same date order: sale -> sale_return -> recovery
                allEntries.sort((a, b) => {
                    let da = new Date(a.date),
                        db = new Date(b.date);
                    if (+da === +db) {
                        const order = {
                            'sale': 1,
                            'sale_return': 2,
                            'recovery': 3
                        };
                        return (order[a.type] || 9) - (order[b.type] || 9);
                    }
                    return da - db;
                });

                // build rows & totals
                allEntries.forEach(entry => {
                    if (entry.type === 'sale') {

    let itemsArr  = String(entry.items).split(',').map(v => v.trim());
    let cartonArr = String(entry.cartons).split(',').map(v => v.trim());
    let pcsArr    = String(entry.pcs || '').split(',').map(v => v.trim());
    let literArr  = String(entry.liters || '').split(',').map(v => v.trim());
    let rateArr   = String(entry.rates).split(',').map(v => v.trim());

    itemsArr.forEach((itemName, i) => {

        let carton = parseFloat(cartonArr[i] || 0);
        let pcs    = parseFloat(pcsArr[i] || 0);
        let liter  = parseFloat(literArr[i] || 0);
        let rate   = parseFloat(rateArr[i] || 0);

        let debit = rate * (carton || liter || pcs || 1);

        totalDebit += debit;
        balance += debit;

        totalCartons += carton;
        totalPcs += pcs;
        totalLiters += liter;
        grandAmount += debit;

        ledgerHTML += `
<tr>
    <td>${formatDate(entry.date)}</td>
    <td>${entry.invoice_number || '-'}</td>
    <td style="text-align:left;">${itemName}</td>
    <td style="text-align:left;">${entry.desc}</td>
    <td>${carton || '-'}</td>
    <td>${pcs || '-'}</td>
    <td>${liter || '-'}</td>
    <td>${rate}</td>
    <td>Rs. ${debit.toFixed(2)}</td>
    <td>-</td>
    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">
        Rs. ${balance.toFixed(2)}
    </td>
</tr>`;
    });
}
 else if (entry.type === 'sale_return') {
                        let credit = parseFloat(entry.amount) || 0;
                        totalCredit += credit;
                        balance -= credit;

                        // subtract cartons/pcs/liters from totals
                        totalCartons -= sumNumericString(entry.cartons);
                        totalPcs -= sumNumericString(entry.pcs);
                        totalLiters -= sumNumericString(entry.liters);
                        grandAmount -= credit;

                        ledgerHTML += `
<tr>
    <td>${formatDate(entry.date)}</td>
    <td>${entry.invoice_number || '-'}</td>
    <td style="text-align:left;padding-left:8px;">${entry.items}</td>
    <td style="text-align:left;padding-left:8px;" class="text-danger fw-bold">${entry.desc}</td>
    <td>${entry.cartons}</td>
    <td>${entry.pcs}</td>
    <td>${entry.liters}</td>
    <td>${entry.rates}</td>
    <td>-</td>
    <td>Rs. ${credit.toFixed(2)}</td>
    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
</tr>`;
                    } else if (entry.type === 'recovery') {
                        let credit = parseFloat(entry.amount) || 0;
                        totalCredit += credit;
                        balance -= credit;

                        ledgerHTML += `
<tr>
    <td>${formatDate(entry.date)}</td>
    <td>-</td>
    <td>-</td>
    <td style="text-align:left;padding-left:8px;">${entry.desc}</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>Rs. ${credit.toFixed(2)}</td>
    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
</tr>`;
                    }
                });

                // render
                $('#ledgerData').html(ledgerHTML);
                $('#openingBalance').text(`Rs. ${openingBalance.toFixed(2)}`);
                $('#totalCartons').text(totalCartons);
                $('#totalPcs').text(totalPcs);
                $('#totalLiters').text(totalLiters);
                $('#totalRateGrand').text(`Rs. ${grandAmount.toFixed(2)}`);
                $('#totalDebit').text(`Rs. ${totalDebit.toFixed(2)}`);
                $('#totalCredit').text(`Rs. ${totalCredit.toFixed(2)}`);
                $('#closingBalance').text(`Rs. ${parseFloat(response.closing_balance).toFixed(2)}`);
            },
            error: function(xhr) {
                alert('Server error. See console.');
                console.error(xhr.responseText);
            }
        });
    });
</script>
<script>
    $('#downloadPdf').on('click', function () {

        let customerId = $('#Customer').val();
        let startDate  = $('#start_date').val();
        let endDate    = $('#end_date').val();

        if (!customerId) {
            alert('Select customer first');
            return;
        }

        let url = "{{ route('customer-ledger-pdf') }}"
            + "?customer_id=" + customerId
            + "&start_date=" + startDate
            + "&end_date=" + endDate;

        window.open(url, '_blank');
    });
</script>
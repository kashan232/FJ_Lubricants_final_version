@include('admin_panel.include.header_include')
<style>
    table th,
    table td {
        vertical-align: middle;
        border: 1px solid black;
        padding: 8px;
    }

    /* Item & Description left aligned and wrapped */
    table td:nth-child(3),
    table th:nth-child(3),
    table td:nth-child(4),
    table th:nth-child(4) {
        text-align: left;
        white-space: normal;
        padding-left: 8px;
    }
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
    /* Prevent wide table overflow */
    .ledger-container {
        max-width: 100%;
        overflow-x: auto;
    }

    /* Carton/PCS/Liters center */
    table td:nth-child(5),
    table th:nth-child(5),
    table td:nth-child(6),
    table th:nth-child(6),
    table td:nth-child(7),
    table th:nth-child(7) {
        text-align: center;
    }

    /* Debit / Credit / Balance right */
    table td:nth-child(9),
    table th:nth-child(9),
    table td:nth-child(10),
    table th:nth-child(10),
    table td:nth-child(11),
    table th:nth-child(11) {
        text-align: right;
    }

    /* Opening balance style */
    .opening-balance {
        font-weight: bold;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-center fw-bold mb-4 text-primary">Vendor Detailed Ledger Statement</h3>

                    <form id="ledgerSearchForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fw-bold" for="Vendor">Select Vendor</label>
                                <select id="Vendor" class="form-control">
                                    <option value="">-- Select Vendor --</option>
                                    @foreach($Vendors as $Vendor)
                                    <option value="{{ $Vendor->id }}"
                                        data-contact="{{ $Vendor->Party_phone }}"
                                        data-city="{{ $Vendor->City }}"
                                        data-area="{{ $Vendor->Area }}">
                                        {{ $Vendor->Party_name }}
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
                            <div class="ledger-header">Vendor Detailed Ledger Statement</div>
                            <div class="ledger-info">
                                <span><strong>Vendor:</strong> <span id="vendorName"></span></span>
                                <span><strong>Duration:</strong> From <span id="startDate"></span> To <span id="endDate"></span></span>
                            </div>
                            <table class="ledger-table">
                                 <colgroup>
                                    <col style="width: 90px;">   <!-- Date -->
                                    <col style="width: 110px;">  <!-- INV-No -->
                                    <col style="width: 280px;">  <!-- Item (WIDE) -->
                                    <col style="width: 220px;">  <!-- Description (WIDE) -->
                                    <col style="width: 70px;">   <!-- Carton -->
                                    <col style="width: 70px;">   <!-- PCS -->
                                    <col style="width: 70px;">   <!-- Liters -->
                                    <col style="width: 80px;">   <!-- Rate -->
                                    <col style="width: 100px;">  <!-- Debit -->
                                    <col style="width: 100px;">  <!-- Credit -->
                                    <col style="width: 120px;">  <!-- Balance -->
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

                                    <!-- Opening balance: label spans first 10 columns, value in 11th -->
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

@include('admin_panel.include.footer_include')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>
    function formatDate(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    $(document).ready(function() {
        // init Select2 on Vendor select
        $('#Vendor').select2({
            placeholder: '-- Select Vendor --',
            allowClear: true,
            width: '100%'
        });

        // When vendor selected, fill fields from data-* attributes
        $('#Vendor').on('select2:select', function(e) {
            var $selected = $(this).find(':selected');
            $('#contact').val($selected.data('contact') || '');
            $('#city').val($selected.data('city') || '');
            $('#area').val($selected.data('area') || '');
        });

        // Clear fields when cleared
        $('#Vendor').on('select2:clear', function() {
            $('#contact, #city, #area').val('');
        });
    });

    $(document).ready(function() {
        $('#Vendor').change(function() {
            var selected = $(this).find(':selected');
            $('#contact').val(selected.data('contact'));
            $('#city').val(selected.data('city'));
            $('#area').val(selected.data('area'));
        });

        $('#searchLedger').click(function() {
            var vendorID = $('#Vendor').val();
            var vendorName = $('#Vendor option:selected').text();
            let startDate = $('#start_date').val();
            let endDate = $('#end_date').val();
            if (!vendorID) {
                alert('Please select a Vendor.');
                return;
            }

            $.ajax({
                url: "{{ route('fetch-vendor-ledger') }}",
                type: "GET",
                data: {
                    Vendor_id: vendorID,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {

                    const startDateObj = new Date(response.startDate);
                    const endDateObj = new Date(response.endDate);
                    // Format dates to 'dd/mm/yyyy'
                    const formattedStartDate = formatDate(response.startDate);
                    const formattedEndDate = formatDate(response.endDate);

                    $('#ledgerResult').show();
                    $('#vendorName').text(vendorName);
                    $('#startDate').text(formattedStartDate || "N/A");
                    $('#endDate').text(formattedEndDate || "N/A");


                    let openingBalance = parseFloat(response.opening_balance) || 0;
                    let balance = openingBalance;

                    // monetary totals
                    let totalDebit = 0,
                        totalCredit = 0;

                    // new totals for carton/pcs and grand amount under Rate
                    let totalCartons = 0;
                    let totalPcs = 0;
                    let totalLiters = 0;
                    let grandAmount = 0; // purchases - returns (shown under Rate column)

                    let ledgerHTML = "";

                    let allEntries = [];

                    // ✅ Opening Balance Entry
                    ledgerHTML += `
<tr>
    <td>${formattedStartDate || '-'}</td>  <!-- Date -->
    <td>-</td>                             <!-- INV-No -->
    <td>-</td>                             <!-- Item -->
    <td class="fw-bold">Opening Balance</td> <!-- Description -->
    <td>-</td>                             <!-- Carton -->
    <td>-</td>                             <!-- PCS -->
    <td>-</td>                             <!-- Liters -->
    <td>-</td>                             <!-- Rate -->
    <td>-</td>                             <!-- Debit -->
    <td>-</td>                             <!-- Credit -->
    <td class="fw-bold text-primary">Rs. ${balance.toFixed(2)}</td> <!-- Balance -->
</tr>
`;


                    // ✅ Sales Entries
                    response.purchases.forEach(entry => {
                        allEntries.push({
                            date: entry.date,
                            type: 'purchase',
                            invoice_number: entry.invoice_number,
                            amount: parseFloat(entry.net_amount) || 0,
                            items: entry.items ?? entry.item ?? '-',
                            cartons: entry.cartons ?? entry.carton_qty ?? '-',
                            pcs: entry.pcs ?? '-',
                            liters: entry.liters ?? entry.liter ?? '-', // <-- new
                            rates: entry.rates ?? entry.rate ?? '-'
                        });
                    });



                    // ✅ recoveries Entries
                    response.recoveries.forEach(entry => {
                        allEntries.push({
                            date: entry.payment_date,
                            type: 'recovery',
                            salesman: entry.description,
                            amount: parseFloat(entry.amount_paid) || 0
                        });
                    });

                    // ✅ Recovery Entries
                    response.returns.forEach(entry => {
                        allEntries.push({
                            date: entry.date,
                            type: 'return',
                            invoice_number: entry.invoice_number,
                            amount: parseFloat(entry.net_amount) || 0,
                            items: entry.items ?? entry.item ?? '-',
                            cartons: entry.cartons ?? entry.carton_qty ?? '-',
                            pcs: entry.pcs ?? '-',
                            liters: entry.liters ?? entry.liter ?? '-',
                            rates: entry.rates ?? entry.rate ?? '-'
                        });
                    });


                    // ✅ Builty Entries
                    response.builties.forEach(entry => {
                        allEntries.push({
                            date: entry.date,
                            type: 'builty',
                            description: entry.description,
                            amount: parseFloat(entry.amount) || 0
                        });
                    });

                    // ✅ Sort Entries by Date (Sales pehle, Recovery baad me agar date same ho)
                    function sumNumericString(val) {
                        if (!val && val !== 0) return 0;
                        if (typeof val === 'number') return val;
                        // split by comma, trim, parseFloat
                        return String(val).split(',').reduce((acc, part) => {
                            const n = parseFloat(part.toString().replace(/[^\d\.\-]/g, '')) || 0;
                            return acc + n;
                        }, 0);
                    }

                    // sort entries by date (same as before)
                    allEntries.sort((a, b) => {
                        let dateA = new Date(a.date);
                        let dateB = new Date(b.date);
                        if (dateA - dateB === 0) {
                            return a.type === 'sale' ? -1 : 1;
                        }
                        return dateA - dateB;
                    });

                    // ✅ Maintain Correct Ledger Balance
                    allEntries.forEach(entry => {
                        if (entry.type === 'purchase') {

                            // 🔹 SPLIT STRINGS INTO ARRAYS
                            let itemsArr = String(entry.items).split(',').map(v => v.trim());
                            let cartonArr = String(entry.cartons).split(',').map(v => v.trim());
                            let pcsArr = String(entry.pcs || '').split(',').map(v => v.trim());
                            let literArr = String(entry.liters || '').split(',').map(v => v.trim());
                            let rateArr = String(entry.rates).split(',').map(v => v.trim());

                            // 🔹 LOOP ITEM-WISE
                            itemsArr.forEach((itemName, i) => {

                                let carton = parseFloat(cartonArr[i] || 0);
                                let pcs = parseFloat(pcsArr[i] || 0);
                                let liter = parseFloat(literArr[i] || 0);
                                let rate = parseFloat(rateArr[i] || 0);

                                // simple amount calc (fallback)
                                let debit = rate * (carton || 1);

                                totalDebit += debit;
                                balance += debit;

                                totalCartons += carton;
                                totalPcs += pcs;
                                totalLiters += liter;
                                grandAmount += debit;

                                ledgerHTML += `
<tr>
    <td>${formatDate(entry.date)}</td>
    <td>${entry.invoice_number}</td>
    <td>${itemName}</td>
    <td>To Purchase A/c</td>
    <td>${carton || '-'}</td>
    <td>${pcs || '-'}</td>
    <td>${liter || '-'}</td>
    <td>${rate}</td>
    <td>Rs. ${debit.toFixed(2)}</td>
    <td>-</td>
    <td class="fw-bold text-success">Rs. ${balance.toFixed(2)}</td>
</tr>`;
                            });
                        } else if (entry.type === 'recovery') {
                            let credit = parseFloat(entry.amount) || 0;
                            totalCredit += credit;
                            balance -= credit;

                            // recovery has no items — but still print placeholders so row has 11 cells
                            ledgerHTML += `
<tr>
    <td>${formatDate(entry.date)}</td>
    <td>-</td>
    <td>-</td>
    <td>${entry.salesman || entry.description || 'Recovery'}</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>Rs. ${credit.toFixed(2)}</td>
    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
</tr>`;
                        } else if (entry.type === 'builty') {
                            let debit = parseFloat(entry.amount) || 0;
                            totalDebit += debit;
                            balance += debit;

                            ledgerHTML += `
<tr>
    <td>${formatDate(entry.date)}</td>
    <td>-</td>
    <td>-</td>
    <td>${entry.description || 'Builty'}</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>Rs. ${debit.toFixed(2)}</td>
    <td>-</td>
    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
</tr>`;
                        } else if (entry.type === 'return') {
                            let credit = parseFloat(entry.amount) || 0;
                            totalCredit += credit;
                            balance -= credit;

                            let cartonsCount = sumNumericString(entry.cartons);
                            let pcsCount = sumNumericString(entry.pcs);
                            let litersCount = sumNumericString(entry.liters || entry.liter);

                            totalCartons -= cartonsCount;
                            totalPcs -= pcsCount;
                            totalLiters -= litersCount;
                            grandAmount -= credit;

                            const items = entry.items || '-';
                            const cartons = entry.cartons || '-';
                            const pcs = entry.pcs || '-';
                            const liters = entry.liters || entry.liter || '-';
                            const rates = entry.rates || entry.rate || '-';
                            const desc = 'Purchase Return';

                            ledgerHTML += `
<tr>
    <td>${formatDate(entry.date)}</td>
    <td>${entry.invoice_number || '-'}</td>
    <td>${items}</td>
    <td>${desc}</td>
    <td>${cartons}</td>
    <td>${pcs}</td>
    <td>${liters}</td>
    <td>${rates}</td>
    <td>-</td>
    <td>Rs. ${credit.toFixed(2)}</td>
    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
</tr>`;
                        }
                    });



                    // ✅ Update Totals
                    // ✅ Update Totals
                    $('#ledgerData').html(ledgerHTML);

                    // update totals in footer
                    $('#openingBalance').text(`Rs. ${openingBalance.toFixed(2)}`);
                    $('#totalCartons').text(totalCartons);
                    $('#totalPcs').text(totalPcs);
                    $('#totalLiters').text(totalLiters);
                    $('#totalRateGrand').text(`Rs. ${grandAmount.toFixed(2)}`);
                    $('#totalDebit').text(`Rs. ${totalDebit.toFixed(2)}`);
                    $('#totalCredit').text(`Rs. ${totalCredit.toFixed(2)}`);
                    $('#closingBalance').text(`Rs. ${parseFloat(response.closing_balance).toFixed(2)}`);
                }
            });
        });
    });
</script>
<script>
    $('#downloadPdf').on('click', function () {

        let vendorId  = $('#Vendor').val();
        let startDate = $('#start_date').val();
        let endDate   = $('#end_date').val();

        if (!vendorId) {
            alert('Select vendor first');
            return;
        }

        let url = "{{ route('vendor-ledger-pdf') }}"
            + "?vendor_id=" + vendorId
            + "&start_date=" + startDate
            + "&end_date=" + endDate;

        window.open(url, '_blank');
    });
</script>
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
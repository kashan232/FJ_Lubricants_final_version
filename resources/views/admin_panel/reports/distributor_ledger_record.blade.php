@include('admin_panel.include.header_include')

<style>
/* base table */
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

/* Prevent wide table overflow and make full width */
.ledger-container {
    border: 2px solid black;
    padding: 10px;
    width: 100%;
    max-width: 100%;
    margin: 0 auto 20px;
    background: #fff;
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
.ledger-header {
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        padding: 10px;
        border-bottom: 2px solid black;
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
.opening-balance { font-weight: bold; }

/* make table responsive wrapper */
.table-responsive { overflow-x: auto; width: 100%; }

/* Remove extra padding from page wrapper so ledger can use full width */
.page-wrapper .content { padding-left: 8px; padding-right: 8px; }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-center fw-bold mb-4 text-primary">Distributor Detailed Ledger Statement</h3>

                    <form id="ledgerSearchForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fw-bold" for="distributor">Select Distributor</label>
                                <select id="distributor" class="form-control">
                                    <option value="">-- Select Distributor --</option>
                                    @foreach($Distributors as $distributor)
                                    <option value="{{ $distributor->id }}"
                                        data-contact="{{ $distributor->Contact }}"
                                        data-city="{{ $distributor->City }}"
                                        data-area="{{ $distributor->Area }}">
                                        {{ $distributor->Customer }}
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
                            <button type="button" id="searchLedger" class="btn btn-primary btn-lg px-5">Search</button>
                        </div>
                    </form>

                    <div class="text-end mt-2">
                        <button id="downloadPdf" class="btn btn-danger">Download PDF</button>
                    </div>

                </div>
            </div>

            <div id="ledgerResult" style="display: none;">
                <div class="ledger-container mt-4">
                   <div class="ledger-header">Distributor Detailed Ledger Statement</div>

                    <div class="ledger-info" style="display:flex;justify-content:space-between;padding:10px;border-bottom:2px solid black;">
                        <span><strong>Distributor:</strong> <span id="distributorName"></span></span>
                        <span><strong>Duration:</strong> From <span id="startDate"></span> To <span id="endDate"></span></span>
                    </div>

                    <div class="table-responsive">
                    <table>
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

                            <!-- Opening balance -->
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

@include('admin_panel.include.footer_include')

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
function formatDate(dateString) {
    if(!dateString) return '-';
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
}

$(document).ready(function() {
    $('#distributor').select2({ placeholder: '-- Select Distributor --', allowClear: true, width: '100%' });

    $('#distributor').on('select2:select', function(e) {
        var $selected = $(this).find(':selected');
        $('#contact').val($selected.data('contact') || '');
        $('#city').val($selected.data('city') || '');
        $('#area').val($selected.data('area') || '');
    });

    $('#distributor').on('select2:clear', function() { $('#contact, #city, #area').val(''); });

    $('#searchLedger').click(function() {
        var distributorId = $('#distributor').val();
        var distributorName = $('#distributor option:selected').text();
        let startDate = $('#start_date').val();
        let endDate = $('#end_date').val();
        if (!distributorId) { alert('Please select a distributor.'); return; }

        $.ajax({
            url: "{{ route('fetch-distributor-ledger') }}",
            type: "GET",
            data: { distributor_id: distributorId, start_date: startDate, end_date: endDate },
            success: function(response) {
                $('#ledgerResult').show();
                $('#distributorName').text(distributorName);
                $('#startDate').text(formatDate(response.startDate) || "N/A");
                $('#endDate').text(formatDate(response.endDate) || "N/A");

                let openingBalance = parseFloat(response.opening_balance) || 0;
                let balance = openingBalance;

                let totalDebit = 0, totalCredit = 0;
                let totalCartons = 0, totalPcs = 0, totalLiters = 0, grandAmount = 0;
                let ledgerHTML = "";
                let allEntries = [];

                // opening row (11 cells)
                ledgerHTML += `
<tr>
    <td>${formatDate(response.startDate)}</td>
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

                // push sales
                response.sales.forEach(entry => {
                    allEntries.push({
                        date: entry.Date,
                        type: 'sale',
                        invoice_number: entry.invoice_number,
                        booker: entry.Booker,
                        amount: parseFloat(entry.net_amount) || 0,
                        items: entry.items || entry.item || '-',
                        cartons: entry.cartons || entry.carton_qty || '-',
                        pcs: entry.pcs || '-',
                        liters: entry.liters || entry.liter || '-',
                        rates: entry.rates || entry.rate || '-'
                    });
                });

                // recoveries
                response.recoveries.forEach(entry => {
                    allEntries.push({
                        date: entry.date,
                        type: 'recovery',
                        salesman: entry.salesman,
                        remarks: entry.remarks,
                        amount: parseFloat(entry.amount_paid) || 0
                    });
                });

                // sale_returns
                response.sale_returns.forEach(entry => {
                    allEntries.push({
                        date: entry.created_at,
                        type: 'sale_return',
                        invoice_number: entry.invoice_number,
                        amount: parseFloat(entry.total_return_amount) || 0,
                        items: entry.items || entry.item || '-',
                        cartons: entry.cartons || entry.carton_qty || '-',
                        pcs: entry.pcs || '-',
                        liters: entry.liters || entry.liter || '-',
                        rates: entry.rates || entry.rate || '-'
                    });
                });

                // transfers
                response.transfers.forEach(entry => {
                    allEntries.push({
                        date: entry.transfer_date,
                        type: 'transfer',
                        from: entry.from_distributor,
                        reason: entry.reason,
                        amount: parseFloat(entry.amount) || 0
                    });
                });

                function sumNumericString(val) {
                    if (!val && val !== 0) return 0;
                    if (typeof val === 'number') return val;
                    return String(val).split(',').reduce((acc, part) => {
                        const n = parseFloat(part.toString().replace(/[^\d\.\-]/g, '')) || 0;
                        return acc + n;
                    }, 0);
                }

                allEntries.sort((a, b) => {
                    let dateA = new Date(a.date);
                    let dateB = new Date(b.date);
                    if (dateA - dateB === 0) { return a.type === 'sale' ? -1 : 1; }
                    return dateA - dateB;
                });

                // build rows (all with 11 cells)
                allEntries.forEach(entry => {
                    if (entry.type === 'sale') {
                        let debit = parseFloat(entry.amount) || 0;
                        totalDebit += debit; balance += debit;

                        let cartonsCount = sumNumericString(entry.cartons);
                        let pcsCount = sumNumericString(entry.pcs);
                        let litersCount = sumNumericString(entry.liters);

                        totalCartons += cartonsCount;
                        totalPcs += pcsCount;
                        totalLiters += litersCount;
                        grandAmount += debit;

                        ledgerHTML += `
<tr>
    <td>${formatDate(entry.date)}</td>
    <td>${entry.invoice_number || '-'}</td>
    <td>${entry.items || '-'}</td>
    <td>To Sale A/c (${entry.booker || ''})</td>
    <td>${entry.cartons || '-'}</td>
    <td>${entry.pcs || '-'}</td>
    <td>${entry.liters || '-'}</td>
    <td>${entry.rates || '-'}</td>
    <td>Rs. ${debit.toFixed(2)}</td>
    <td>-</td>
    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
</tr>`;
                    }
                    else if (entry.type === 'recovery') {
                        let credit = parseFloat(entry.amount) || 0;
                        totalCredit += credit; balance -= credit;

                        ledgerHTML += `
<tr>
    <td>${formatDate(entry.date)}</td>
    <td>-</td>
    <td>-</td>
    <td>${entry.remarks || entry.salesman || 'Recovery'}</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>Rs. ${credit.toFixed(2)}</td>
    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
</tr>`;
                    }
                    else if (entry.type === 'sale_return') {
                        let credit = parseFloat(entry.amount) || 0;
                        totalCredit += credit; balance -= credit;

                        let cartonsCount = sumNumericString(entry.cartons);
                        let pcsCount = sumNumericString(entry.pcs);
                        let litersCount = sumNumericString(entry.liters);

                        totalCartons -= cartonsCount;
                        totalPcs -= pcsCount;
                        totalLiters -= litersCount;
                        grandAmount -= credit;

                        ledgerHTML += `
<tr>
    <td>${formatDate(entry.date)}</td>
    <td>${entry.invoice_number || '-'}</td>
    <td>${entry.items || '-'}</td>
    <td class="text-danger fw-bold">Sale Return</td>
    <td>${entry.cartons || '-'}</td>
    <td>${entry.pcs || '-'}</td>
    <td>${entry.liters || '-'}</td>
    <td>${entry.rates || '-'}</td>
    <td>-</td>
    <td class="text-danger fw-bold">Rs. ${credit.toFixed(2)}</td>
    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
</tr>`;
                    }
                    else if (entry.type === 'transfer') {
                        let debit = parseFloat(entry.amount) || 0;
                        totalDebit += debit; balance += debit;
                        ledgerHTML += `
<tr>
    <td>${formatDate(entry.date)}</td>
    <td>-</td>
    <td>-</td>
    <td>Balance Transfer from ${entry.from || '-'} ${entry.reason ? '('+entry.reason+')' : ''}</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>Rs. ${debit.toFixed(2)}</td>
    <td>-</td>
    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
</tr>`;
                    }
                });

                $('#ledgerData').html(ledgerHTML);

                // footer totals
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
document.getElementById("downloadPdf").addEventListener("click", function() {
    const element = document.querySelector(".ledger-container");
    html2pdf().set({
        margin: 0.2,
        filename: 'Distributor-Ledger.pdf',
        image: { type: 'jpeg', quality: 1 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    }).from(element).save();
});
</script>

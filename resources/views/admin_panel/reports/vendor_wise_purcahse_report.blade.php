@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <style>
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }

        table.report-table th,
        table.report-table td {
            border: 1px solid #000 !important;
            padding: 6px;
            text-align: center;
        }

        table.report-table th {
            background-color: #f2f2f2;
        }

        table.report-table tfoot td {
            font-weight: bold;
            background-color: #e9e9e9;
        }
    </style>

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-center fw-bold mb-4 text-primary">PURCHASE COMPANY REPORT</h3>

                    <form id="ledgerSearchForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fw-bold" for="Vendor">Select Vendor</label>
                                <select id="Vendor" class="form-control">
                                    <option value="">-- Select Vendor --</option>
                                    @foreach($Vendors as $Vendor)
                                    <option value="{{ $Vendor->id }}">{{ $Vendor->Party_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control">
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button type="button" id="searchLedger" class="btn btn-primary btn-lg px-5">Search</button>
                        </div>
                    </form>

                    <div class="text-end mt-2">
                        <button id="downloadPdf" class="btn btn-danger">Download PDF</button>
                    </div>

                    <div class="table-responsive mt-4 Purcahse_report" id="report-preview">
                        <h4 class="text-center fw-bold">PURCHASE COMPANY REPORT HYDERABAD</h4>
                        <div class="date-range-text text-center mb-2"></div>
                        <div class="report-party-name  fw-bold text-secondary mb-2"></div>

                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Inv#</th>
                                    <th>Date</th>
                                    <th>Item Name</th>
                                    <th>Carton Packing</th>
                                    <th>Pur in Carton</th>
                                    <th>Pur in Pcs</th>
                                    <th>Pur in Liter</th>
                                    <th>Net Amount</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function formatDate(dateStr) {
        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const d = new Date(dateStr);
        return `${String(d.getDate()).padStart(2, '0')}-${months[d.getMonth()]}-${String(d.getFullYear()).slice(-2)}`;
    }

    $('#searchLedger').click(function() {
        const start = $('#start_date').val();
        const end = $('#end_date').val();
        const vendorId = $('#Vendor').val();

        if (!start || !end || !vendorId) {
            alert('Please select Vendor, Start Date, and End Date.');
            return;
        }

        $.ajax({
            url: "{{ route('fetch.vendor.purchase.report') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                start_date: start,
                end_date: end,
                vendor_id: vendorId
            },
            success: function(response) {
                console.log(response); // For debugging

                let rows = '';
                response.report.forEach(row => {
                    rows += `
                        <tr>
                            <td>${row.inv_no}</td>
                            <td>${row.date}</td>
                            <td>${row.item}</td>
                            <td>${row.carton_packing}</td>
                            <td>${row.carton_qty}</td>
                            <td>${row.pcs}</td>
                            <td>${row.liter}</td>
                            <td>${Number(row.net_amount).toLocaleString()}</td>
                        </tr>`;
                });

                // Set Vendor Name
                $('.report-party-name').html(`<p>${$('#Vendor option:selected').text()}</p>`);

                // Set Date Range
                const dateRangeHTML = `
                    <p>From <strong>${formatDate(start)}</strong> To <strong>${formatDate(end)}</strong></p>
                `;
                $('.date-range-text').html(dateRangeHTML);

                // Set Table
                $('#report-preview tbody').html(rows);
                $('#report-preview tfoot').html(`
                    <tr>
                        <td colspan="4">Total</td>
                        <td>${response.totals.carton}</td>
                        <td>${response.totals.pcs}</td>
                        <td>${response.totals.liter}</td>
                        <td>${Number(response.totals.net_amount).toLocaleString()}</td>
                    </tr>
                `);
            }
        });
    });

    document.getElementById("downloadPdf").addEventListener("click", function() {
        const element = document.querySelector(".Purcahse_report");
        const opt = {
            margin: 0.3,
            filename: 'Vendor-Wise-Purchase-Report.pdf',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 2,
                useCORS: true
            },
            jsPDF: {
                unit: 'in',
                format: 'a4',
                orientation: 'portrait'
            }
        };
        html2pdf().set(opt).from(element).save();
    });
</script>
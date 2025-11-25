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
                    <h3 class="card-title text-center fw-bold mb-4 text-primary"> PURCHASE REPORT</h3>

                    <form id="ledgerSearchForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control">
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

                    {{-- REPORT TABLE PREVIEW --}}
                    <div class="table-responsive mt-4 Purcahse_report" id="report-preview">
                        <h4 class="text-center fw-bold">PURCHASE REPORT HYDERABAD</h4>
                        <div class="date-range-text"></div>

                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Date</th>
                                    <th>Item Name</th>
                                    <th>Carton Packing</th>
                                    <th>Pur in Carton</th>
                                    <th>Pur in Pcs</th>
                                    <th>Pur in Lit</th>
                                    <th>Net Amount</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>

                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    // Format date to e.g. 01-JUN-25
    function formatDate(dateStr) {
        const months = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
        const d = new Date(dateStr);
        const day = ("0" + d.getDate()).slice(-2);
        const month = months[d.getMonth()];
        const year = d.getFullYear().toString().slice(-2);
        return `${day}-${month}-${year}`;
    }
    $('#searchLedger').click(function() {
        const start = $('#start_date').val();
        const end = $('#end_date').val();

        if (!start || !end) {
            alert('Please select both start and end dates.');
            return;
        }

        $.ajax({
            url: "{{ route('fetch-purchase-report') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                start_date: start,
                end_date: end
            },
            success: function(response) {
                let rows = '';
                response.report.forEach(row => {
                    rows += `
                    <tr>
                        <td>${row.code}</td>
                        <td>${row.date}</td>
                        <td>${row.item}</td>
                        <td>${row.carton_packing}</td>
                        <td>${row.carton_qty}</td>
                        <td>${row.pcs}</td>
                        <td>${row.liter}</td>
                        <td>${Number(row.net_amount).toLocaleString()}</td>
                    </tr>
                `;
                });

                // ✅ Format & show date range under heading
                const dateRangeHTML = `
                <p class="mb-3 text-center">
                    From <strong>${formatDate(start)}</strong> To <strong>${formatDate(end)}</strong>
                </p>
            `;
                $('.date-range-text').html(dateRangeHTML);

                // ✅ Inject table data
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
</script>

<script>
    document.getElementById("downloadPdf").addEventListener("click", function() {
        const element = document.querySelector(".Purcahse_report");

        const opt = {
            margin: 0,
            filename: 'Date-wise-Purchase-Report.pdf',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 10,
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
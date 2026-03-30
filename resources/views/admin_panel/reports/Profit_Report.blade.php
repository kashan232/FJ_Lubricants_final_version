@include('admin_panel.include.header_include')
<!-- select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-3 shadow-lg">
                <div class="card-body">
                    <h3 class="text-center fw-bold text-primary">PROFIT REPORT</h3>
                    <hr>

                    <form id="reportSearchForm">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Select Products</label>
                                <select class="form-control select2" name="Product[]" multiple="multiple" id="ProductSelect">
                                    <option value="All">All Products</option>
                                    @foreach($Products as $product)
                                    <option value="{{ $product->item }}">{{ $product->item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="searchBtn" class="btn btn-primary w-100 py-2 fw-bold">
                                    <i class="fa fa-search"></i> SEARCH
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-5">
                        <div id="reportContainer" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="fw-bold text-secondary">Summary Results</h4>
                                <button type="button" id="exportBtn" class="btn btn-success btn-sm shadow-sm px-3">
                                    <i class="fa fa-file-excel"></i> Export CSV
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover text-center align-middle" id="profitTable">
                                    <thead class="table-dark">
                                        <tr class="text-white">
                                            <th class="text-white">S.No</th>
                                            <th class="text-white">Product Name</th>
                                            <th class="text-white">Total Purchase (PKR)</th>
                                            <th class="text-white">Distributor Sale (PKR)</th>
                                            <th class="text-white">Customer Sale (PKR)</th>
                                            <th class="text-white">Total Sale (PKR)</th>
                                            <th class="text-white">Profit / Loss (PKR)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reportBody">
                                        <!-- AJAX data here -->
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td colspan="2" class="text-end">GRAND TOTAL:</td>
                                            <td id="totalPurchase">0</td>
                                            <td id="totalDistSale">0</td>
                                            <td id="totalCustSale">0</td>
                                            <td id="totalSale">0</td>
                                            <td id="totalProfit">0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div id="noData" class="alert alert-info text-center mt-4" style="display: none;">
                            No data found for the selected criteria.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: "Choose products...",
            allowClear: true,
            width: '100%'
        });

        $('#searchBtn').click(function() {
            const formData = $('#reportSearchForm').serialize();
            const startStr = $('#start_date').val();
            const endStr = $('#end_date').val();

            if (!startStr || !endStr) {
                alert("Please select both dates.");
                return;
            }

            // Show loading state
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');

            $.ajax({
                url: "{{ route('fetch.profit.report') }}",
                type: "GET",
                data: formData,
                success: function(response) {
                    $('#searchBtn').prop('disabled', false).html('<i class="fa fa-search"></i> SEARCH');
                    
                    if (response.length > 0) {
                        $('#noData').hide();
                        $('#reportContainer').fadeIn();
                        let rows = '';
                        let grandPurchase = 0;
                        let grandDistSale = 0;
                        let grandCustSale = 0;
                        let grandTotalSale = 0;
                        let grandProfit = 0;

                        response.forEach((item, index) => {
                            grandPurchase += parseFloat(item.purchase_total);
                            grandDistSale += parseFloat(item.distributor_sale);
                            grandCustSale += parseFloat(item.customer_sale);
                            grandTotalSale += parseFloat(item.sale_total);
                            grandProfit += parseFloat(item.profit);

                            const profitClass = item.profit >= 0 ? 'text-success' : 'text-danger';

                            rows += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td class="text-start fw-bold">${item.item}</td>
                                    <td>${parseFloat(item.purchase_total).toLocaleString()}</td>
                                    <td>${parseFloat(item.distributor_sale).toLocaleString()}</td>
                                    <td>${parseFloat(item.customer_sale).toLocaleString()}</td>
                                    <td>${parseFloat(item.sale_total).toLocaleString()}</td>
                                    <td class="${profitClass} fw-bold">${parseFloat(item.profit).toLocaleString()}</td>
                                </tr>
                            `;
                        });

                        $('#reportBody').html(rows);
                        $('#totalPurchase').text(grandPurchase.toLocaleString());
                        $('#totalDistSale').text(grandDistSale.toLocaleString());
                        $('#totalCustSale').text(grandCustSale.toLocaleString());
                        $('#totalSale').text(grandTotalSale.toLocaleString());
                        
                        const grandProfitClass = grandProfit >= 0 ? 'text-success' : 'text-danger';
                        $('#totalProfit').text(grandProfit.toLocaleString()).removeClass('text-success text-danger').addClass(grandProfitClass);

                    } else {
                        $('#reportContainer').hide();
                        $('#noData').fadeIn();
                    }
                },
                error: function() {
                    $('#searchBtn').prop('disabled', false).html('<i class="fa fa-search"></i> SEARCH');
                    alert("An error occurred while fetching report data.");
                }
            });
        });

        $('#exportBtn').click(function() {
            let csv = [];
            let rows = document.querySelectorAll("#profitTable tr");

            for (let i = 0; i < rows.length; i++) {
                let row = [],
                    cols = rows[i].querySelectorAll("td, th");

                for (let j = 0; j < cols.length; j++) {
                    // Clean text and handle commas
                    let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/(\s\s+)/gm, " ");
                    data = data.replace(/"/g, '""');
                    row.push('"' + data + '"');
                }

                csv.push(row.join(","));
            }

            let csvFile = new Blob([csv.join("\n")], {
                type: "text/csv"
            });
            let downloadLink = document.createElement("a");
            let filename = "Profit_Report_" + $('#start_date').val() + "_to_" + $('#end_date').val() + ".csv";

            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
        });
    });
</script>

<style>
    @media print {
        .main-wrapper > :not(.page-wrapper),
        .card-body > form,
        .card-body > hr,
        #searchBtn,
        .btn-outline-dark {
            display: none !important;
        }
        .page-wrapper { margin: 0; padding: 0; }
        .card { border: none !important; box-shadow: none !important; }
        .table-dark { background-color: #000 !important; color: #fff !important; }
        th, td { border: 1px solid #ddd !important; }
    }
</style>

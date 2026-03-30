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
                    <h3 class="text-center fw-bold text-primary">ITEM WISE SALE REPORT</h3>
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
                                <h4 class="fw-bold text-secondary">Sale Summary by Item</h4>
                                <button type="button" id="exportBtn" class="btn btn-success btn-sm shadow-sm px-3">
                                    <i class="fa fa-file-excel"></i> Export CSV
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover text-center align-middle" id="reportTable" style="font-size: 13px;">
                                    <thead class="table-dark">
                                        <tr class="text-white">
                                            <th rowspan="2" class="align-middle text-white">S.No</th>
                                            <th rowspan="2" class="align-middle text-white">Item Name</th>
                                            <th colspan="4" class="text-white bg-secondary">DISTRIBUTOR SALE</th>
                                            <th colspan="4" class="text-white bg-primary">CUSTOMER SALE</th>
                                            <th rowspan="2" class="align-middle text-white">NET AMOUNT</th>
                                        </tr>
                                        <tr class="text-white">
                                            <th class="text-white">CTN</th>
                                            <th class="text-white">PCS</th>
                                            <th class="text-white">LTR</th>
                                            <th class="text-white">AMT</th>
                                            <th class="text-white">CTN</th>
                                            <th class="text-white">PCS</th>
                                            <th class="text-white">LTR</th>
                                            <th class="text-white">AMT</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reportBody">
                                        <!-- AJAX data here -->
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td colspan="2" class="text-end">GRAND TOTAL:</td>
                                            <td id="totalDistCtn">0</td>
                                            <td id="totalDistPcs">0</td>
                                            <td id="totalDistLtr">0</td>
                                            <td id="totalDistAmt">0</td>
                                            <td id="totalCustCtn">0</td>
                                            <td id="totalCustPcs">0</td>
                                            <td id="totalCustLtr">0</td>
                                            <td id="totalCustAmt">0</td>
                                            <td id="totalNetAmt">0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div id="noData" class="alert alert-info text-center mt-4" style="display: none;">
                            No sales data found.
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
        $('.select2').select2({
            placeholder: "Select items...",
            allowClear: true,
            width: '100%'
        });

        $('#searchBtn').click(function() {
            const formData = $('#reportSearchForm').serialize();
            const startStr = $('#start_date').val();
            const endStr = $('#end_date').val();

            if (!startStr || !endStr) {
                alert("Please select dates.");
                return;
            }

            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ...');

            $.ajax({
                url: "{{ route('fetch.item.wise.sale.report') }}",
                type: "GET",
                data: formData,
                success: function(response) {
                    $('#searchBtn').prop('disabled', false).html('<i class="fa fa-search"></i> SEARCH');
                    
                    if (response.length > 0) {
                        $('#noData').hide();
                        $('#reportContainer').fadeIn();
                        
                        let rows = '';
                        let gDCs = 0, gDPs = 0, gDLs = 0, gDAs = 0;
                        let gCCs = 0, gCPs = 0, gCLs = 0, gCAs = 0;
                        let gNet = 0;

                        response.forEach((item, index) => {
                            gDCs += item.dist_ctn;
                            gDPs += item.dist_pcs;
                            gDLs += item.dist_ltr;
                            gDAs += item.dist_amt;

                            gCCs += item.cust_ctn;
                            gCPs += item.cust_pcs;
                            gCLs += item.cust_ltr;
                            gCAs += item.cust_amt;

                            gNet += item.total_amt;

                            rows += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td class="text-start fw-bold">${item.item}</td>
                                    <td>${item.dist_ctn.toLocaleString()}</td>
                                    <td>${item.dist_pcs.toLocaleString()}</td>
                                    <td>${item.dist_ltr.toLocaleString()}</td>
                                    <td class="bg-light">${item.dist_amt.toLocaleString()}</td>
                                    <td>${item.cust_ctn.toLocaleString()}</td>
                                    <td>${item.cust_pcs.toLocaleString()}</td>
                                    <td>${item.cust_ltr.toLocaleString()}</td>
                                    <td class="bg-light">${item.cust_amt.toLocaleString()}</td>
                                    <td class="fw-bold bg-white">${item.total_amt.toLocaleString()}</td>
                                </tr>
                            `;
                        });

                        $('#reportBody').html(rows);
                        $('#totalDistCtn').text(gDCs.toLocaleString());
                        $('#totalDistPcs').text(gDPs.toLocaleString());
                        $('#totalDistLtr').text(gDLs.toLocaleString());
                        $('#totalDistAmt').text(gDAs.toLocaleString());
                        $('#totalCustCtn').text(gCCs.toLocaleString());
                        $('#totalCustPcs').text(gCPs.toLocaleString());
                        $('#totalCustLtr').text(gCLs.toLocaleString());
                        $('#totalCustAmt').text(gCAs.toLocaleString());
                        $('#totalNetAmt').text(gNet.toLocaleString());

                    } else {
                        $('#reportContainer').hide();
                        $('#noData').fadeIn();
                    }
                },
                error: function() {
                    $('#searchBtn').prop('disabled', false).html('<i class="fa fa-search"></i> SEARCH');
                    alert("Error loading data.");
                }
            });
        });

        $('#exportBtn').click(function() {
            let csv = [];
            let rows = document.querySelectorAll("#reportTable tr");

            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll("td, th");
                for (let j = 0; j < cols.length; j++) {
                    let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/(\s\s+)/gm, " ");
                    data = data.replace(/"/g, '""');
                    row.push('"' + data + '"');
                }
                csv.push(row.join(","));
            }

            let csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
            let downloadLink = document.createElement("a");
            let filename = "Item_Wise_Sale_Report_" + $('#start_date').val() + "_to_" + $('#end_date').val() + ".csv";
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
        });
    });
</script>

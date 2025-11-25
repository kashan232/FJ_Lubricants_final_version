@include('admin_panel.include.header_include')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    /* Select2 x Bootstrap height fix */
    .select2-container .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #ced4da;
        padding-bottom: 2px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        margin-top: 4px;
    }
</style>
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-center fw-bold mb-4 text-primary">PRODUCT SALES REPORT</h3>

                    <form id="ledgerSearchForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="Product" class="form-label">Select Product</label>
                                <select class="form-control select2" id="Product" name="Product[]" multiple required>
                                    <option value="All">All</option>
                                    @foreach($Products as $Product)
                                    <option value="{{ $Product->item_name }}">{{ $Product->item_name }}</option>
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
                            <button type="button" id="searchLedger" class="btn btn-primary btn-lg px-5">
                                Search
                            </button>
                        </div>
                    </form>

                    <div class="title mt-4">PRODUCT SALES REPORT</div>
                    <div id="salesmanHeading" class="text-left fw-bold mb-2" style="font-size: 16px;"></div>
                    <table id="productSaleTable">
                        <thead>
                            <tr>
                                <th>SN#</th>
                                <th>Item</th>
                                <th>Carton Qty</th>
                                <th>PCS Qty</th>
                                <th>Liters</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">No Data Available</td>
                            </tr>
                        </tbody>
                    </table>




                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    body {
        font-family: Arial, sans-serif;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid black;
        padding: 5px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
        font-weight: bold;
        text-align: center;
    }

    .title {
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 10px;
    }
</style>
<script>
    $(function() {
        $('#Product').select2({
            placeholder: 'Select Product',
            allowClear: true,
            width: '100%',
            closeOnSelect: false
        });
    });

    document.getElementById("searchLedger").addEventListener("click", function() {
        let products = $('#Product').val() || [];
        let startDate = document.getElementById("start_date").value;
        let endDate = document.getElementById("end_date").value;
        let csrfToken = document.querySelector('input[name="_token"]').value;

        fetch("{{ route('get-Product-sales-report') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    Product: products,
                    start_date: startDate,
                    end_date: endDate
                })
            })
            .then(response => response.json())
            .then(data => {
                let tableBody = document.querySelector("#productSaleTable tbody");
                tableBody.innerHTML = "";

                if (data.length > 0) {
                    let totalCartons = 0,
                        totalPcs = 0,
                        totalLiters = 0,
                        totalAmount = 0;

                    data.forEach((item, index) => {
                        totalCartons += parseFloat(item.carton_qty);
                        totalPcs += parseFloat(item.pcs);
                        totalLiters += parseFloat(item.liters);
                        totalAmount += parseFloat(item.amount);

                        let row = `<tr>
                <td>${index + 1}</td>
                <td>${item.item}</td>
                <td>${item.carton_qty}</td>
                <td>${item.pcs}</td>
                <td>${item.liters}</td>
                <td>${item.amount}</td>
            </tr>`;
                        tableBody.innerHTML += row;
                    });

                    // Grand Total
                    tableBody.innerHTML += `
        <tr class="fw-bold">
            <td colspan="2" class="text-end">Grand Total</td>
            <td>${totalCartons}</td>
            <td>${totalPcs}</td>
            <td>${totalLiters}</td>
            <td>${totalAmount.toFixed(2)}</td>
        </tr>`;
                } else {
                    tableBody.innerHTML = `<tr><td colspan="6" class="text-center">No Data Available</td></tr>`;
                }
            })

            .catch(error => console.error("Error:", error));
    });
</script>
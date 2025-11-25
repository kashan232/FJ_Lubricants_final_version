@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-center fw-bold mb-4 text-primary">DATE WISE SALES REPORT</h3>

                    <form id="ledgerSearchForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="salesman" class="form-label">Select Salesman</label>
                                <select class="form-control" id="salesman" name="salesman" required>
                                    <option value="All">All</option>
                                    @foreach($Salesmans as $saleman)
                                    <option value="{{ $saleman->name }}">{{ $saleman->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(Auth::check() && Auth::user()->usertype === 'admin')
                            <div class="col-md-3">
                                <label for="type" class="form-label">Select Type</label>
                                <select id="type" name="type" class="form-control">
                                    <option value="all">All</option>
                                    <option value="distributor">Distributor</option>
                                    <option value="customer">Customer</option>
                                </select>
                            </div>
                            @elseif(Auth::check() && Auth::user()->usertype === 'distributor')
                            <div class="col-md-3">
                                <label for="type" class="form-label">Select Type</label>
                                <select id="type" name="type" class="form-control">
                                    <option value="customer">Customer</option>
                                </select>
                            </div>
                            @endif

                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-3">
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

                    <div class="title mt-4">DATE WISE SALE REPORT</div>
                    <div id="salesmanHeading" class="text-left fw-bold mb-2" style="font-size: 16px;"></div>
                    <table id="recoveryTable">
                        <thead>
                            <tr>
                                <th>SN#</th>
                                <th>Invoice#</th>
                                <th>Date</th>
                                <th>Party Name</th>
                                <th>Area</th>
                                <th>Remarks</th>
                                <th>Sales Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center">No Data Available</td>
                            </tr>
                        </tbody>
                    </table>


                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

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
    document.getElementById("searchLedger").addEventListener("click", function() {
        let salesman = document.getElementById("salesman").value;
        let type = document.getElementById("type").value;
        let startDate = document.getElementById("start_date").value;
        let endDate = document.getElementById("end_date").value;
        let csrfToken = document.querySelector('input[name="_token"]').value;

        fetch("{{ route('get-sales-report') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    salesman: salesman,
                    type: type,
                    start_date: startDate,
                    end_date: endDate
                })
            })
            .then(response => response.json())
            .then(data => {
                const selectedSalesman = document.getElementById("salesman").value;
                const salesmanHeading = document.getElementById("salesmanHeading");
                salesmanHeading.innerHTML = selectedSalesman === "All" ? "Salesman: All" : "Salesman: " + selectedSalesman;

                let tableHead = document.querySelector("#recoveryTable thead");
                let tableBody = document.querySelector("#recoveryTable tbody");
                tableBody.innerHTML = "";

                if (selectedSalesman !== 'All') {
                    // Show the main thead
                    tableHead.style.display = "table-header-group";

                    let totalAmount = 0;

                    if (data.length > 0) {
                        data.forEach((item, index) => {
                            let formattedDate = new Date(item.date).toLocaleDateString("en-GB");
                            let amount = parseFloat(item.amount_paid.replace(/,/g, ''));
                            totalAmount += amount;

                            let row = `<tr>
                            <td>${index + 1}</td>
                            <td>${item.invoice_number}</td> <!-- Invoice Number yahan aayega -->
                            <td>${formattedDate}</td>
                            <td>${item.party_name}</td>
                            <td>${item.area}</td>
                            <td>${item.remarks}</td>
                            <td>${item.amount_paid}</td>
                        </tr>`;
                            tableBody.innerHTML += row;
                        });

                        tableBody.innerHTML += `
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Total Sales:</td>
                            <td class="fw-bold">${totalAmount.toLocaleString()}</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Total Customers:</td>
                            <td class="fw-bold">${data.length}</td>
                        </tr>`;
                    } else {
                        tableBody.innerHTML = `<tr><td colspan="7" class="text-center">No Data Available</td></tr>`;
                    }

                } else {
                    // Hide the main static thead for 'All'
                    tableHead.style.display = "none";

                    if (data.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="7" class="text-center">No Data Available</td></tr>`;
                        return;
                    }

                    const grouped = {};
                    data.forEach(item => {
                        const sm = item.salesman || 'Unknown';
                        if (!grouped[sm]) grouped[sm] = [];
                        grouped[sm].push(item);
                    });

                    let totalRecoveryAll = 0;
                    let totalCustomersAll = 0;

                    for (const [smName, rows] of Object.entries(grouped)) {
                        let total = 0;

                        // Group heading
                        tableBody.innerHTML += `
                        <tr><td colspan="7" class="fw-bold text-primary">Salesman: ${smName}</td></tr>
                        <tr>
                            <th>SN#</th>
                            <th>Invoice#</th>
                            <th>Date</th>
                            <th>Party Name</th>
                            <th>Area</th>
                            <th>Remarks</th>
                            <th>Amount</th>
                        </tr>`;

                        rows.forEach((item, index) => {
                            const formattedDate = new Date(item.date).toLocaleDateString("en-GB");
                            const amount = parseFloat(item.amount_paid.replace(/,/g, ''));
                            total += amount;

                            tableBody.innerHTML += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.invoice_number}</td>
                                <td>${formattedDate}</td>
                                <td>${item.party_name}</td>
                                <td>${item.area}</td>
                                <td>${item.remarks}</td>
                                <td>${item.amount_paid}</td>
                            </tr>`;
                        });

                        totalRecoveryAll += total;
                        totalCustomersAll += rows.length;

                        tableBody.innerHTML += `
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Total Sales:</td>
                            <td class="fw-bold">${total.toLocaleString()}</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Total Customers:</td>
                            <td class="fw-bold">${rows.length}</td>
                        </tr>
                        <tr><td colspan="7" class="text-center text-muted">------------------------------------------</td></tr>`;
                    }

                    // 👉 FINAL SUMMARY FOOTER
                    tableBody.innerHTML += `
                    <tr>
                        <td colspan="6" class="text-end fw-bold text-danger">Grand Total Sales:</td>
                        <td class="fw-bold text-danger">${totalRecoveryAll.toLocaleString()}</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="text-end fw-bold text-danger">Grand Total Customers:</td>
                        <td class="fw-bold text-danger">${totalCustomersAll}</td>
                    </tr>`;
                }
            })
            .catch(error => console.error("Error:", error));
    });
</script>
@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <style>
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 20px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #000 !important;
            padding: 6px;
            text-align: center;
        }

        .section-title {
            font-weight: bold;
            background: #f0f0f0;
            padding: 6px 10px;
            margin-top: 20px;
        }

        .summary-row {
            font-weight: bold;
            background-color: #e9ecef;
        }
    </style>

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="text-center fw-bold text-primary">SALEMAN MARKET CREDIT REPORT</h3>

                    <form id="ledgerSearchForm">
                        @csrf
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label for="salesman" class="form-label">Select Salesman</label>
                                <select class="form-control" id="salesman" name="salesman" required>
                                    <option value="All">All</option>
                                    @foreach($Salesmans as $saleman)
                                    <option value="{{ $saleman->name }}">{{ $saleman->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Select City</label>
                                <select class="form-control" name="city" id="citySelect">
                                    <option value="All">All</option>
                                    @foreach($cities as $city)
                                    <option value="{{ $city->city_name }}">{{ $city->city_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12" id="areaCheckboxes">
                                <label class="form-label d-block">Select Areas</label>
                                <div class="row" id="areasContainer">
                                    <!-- Dynamic Area Checkboxes -->
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control">
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="button" id="searchLedger" class="btn btn-primary btn-lg px-5">Search</button>
                        </div>
                    </form>

                    <div class="text-end mt-3">
                        <button id="downloadPdf" class="btn btn-danger">Download PDF</button>
                    </div>

                    <hr>
                    <div id="reportResults"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
<script>
    $(document).ready(function() {
        // Load areas when city is selected
        $('#citySelect').change(function() {
            let city = $(this).val();
            $('#areasContainer').html('<p class="text-muted">Loading areas...</p>');

            if (city) {
                $.ajax({
                    url: "{{ route('fetch-areas') }}",
                    method: "GET",
                    data: {
                        city_id: city
                    },
                    success: function(data) {
                        $('#areasContainer').html('');
                        if (data.length > 0) {
                            $.each(data, function(key, area) {
                                $('#areasContainer').append(`
                                    <div class="col-md-2">
                                        <div class="form-check">
                                            <input class="form-check-input area-checkbox" type="checkbox" name="area[]" value="${area.area_name}" id="area_${key}">
                                            <label class="form-check-label" for="area_${key}">${area.area_name}</label>
                                        </div>
                                    </div>
                                `);
                            });
                        } else {
                            $('#areasContainer').html('<p class="text-danger">No areas found.</p>');
                        }
                    },
                    error: function() {
                        $('#areasContainer').html('<p class="text-danger">Error fetching areas.</p>');
                    }
                });
            } else {
                $('#areasContainer').html('<p class="text-danger">Please select a city.</p>');
            }
        });

        $('#searchLedger').click(function() {
            let salesman = $('#salesman').val();
            let city = $('#citySelect').val();
            let area = [];
            $('.area-checkbox:checked').each(function() {
                area.push($(this).val());
            });
            let startDate = $('#start_date').val();
            let endDate = $('#end_date').val();

            if (!salesman || !city || !startDate || !endDate || (city !== 'All' && area.length === 0)) {
                alert('Please fill all fields!');
                return;
            }

            $.ajax({
                url: "{{ route('receivable.salesman.marketreport') }}",
                method: "GET",
                data: {
                    salesman: salesman,
                    city: city,
                    area: area,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    $('#reportResults').html('');
                    let grandTotal = 0;

                    Object.keys(response).forEach(salesman => {
                        const cityData = response[salesman];

                        let salesmanHTML = `<div class="section-title text-primary fs-5">${salesman.toUpperCase()}</div>`;

                        let salesmanTotal = 0;

                        Object.keys(cityData).forEach(city => {
                            const areaData = cityData[city];

                            Object.keys(areaData).forEach(area => {
                                const customers = areaData[area];

                                if (customers.length === 0) return;

                                let cityTotal = 0;

                                salesmanHTML += `
                            <div class="section-title text-info">${city} - ${area}</div>
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Shop Name</th>
                                        <th>Customer</th>
                                        <th>Phone</th>
                                        <th>Opening</th>
                                        <th>Sales</th>
                                        <th>Returns</th>
                                        <th>Recoveries</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                                customers.forEach(c => {
                                    let bal = parseFloat(c.balance || 0);
                                    cityTotal += bal;

                                    salesmanHTML += `
                                <tr>
                                    <td>${c.shop_name}</td>
                                    <td>${c.customer_name}</td>
                                    <td>${c.phone}</td>
                                    <td>${c.opening_balance.toLocaleString()}</td>
                                    <td>${c.total_sales.toLocaleString()}</td>
                                    <td>${c.total_returns.toLocaleString()}</td>
                                    <td>${c.total_recoveries.toLocaleString()}</td>
                                    <td>${bal.toLocaleString()}</td>
                                </tr>
                            `;
                                });

                                salesmanHTML += `
                            <tr class="summary-row">
                                <td colspan="7" class="text-end">Total (${city} - ${area})</td>
                                <td>${cityTotal.toLocaleString()}</td>
                            </tr>
                            </tbody>
                            </table>
                            <hr>
                        `;

                                salesmanTotal += cityTotal;
                            });
                        });

                        salesmanHTML += `
                    <div class="text-end fw-bold text-dark mt-2">
                        Total Credit for ${salesman}: ${salesmanTotal.toLocaleString()}
                    </div>
                    <hr>
                `;

                        grandTotal += salesmanTotal;
                        $('#reportResults').append(salesmanHTML);
                    });

                    $('#reportResults').append(`
                <div class="section-title text-end text-dark fs-5">
                    <strong>Grand Total Credit: </strong> ${grandTotal.toLocaleString()}
                </div>
            `);
                },
                error: function() {
                    alert('Failed to load salesman report');
                }
            });
        });

    });
</script>
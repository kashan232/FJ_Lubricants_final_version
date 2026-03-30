@include('admin_panel.include.header_include')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

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

        .area-title {
            font-weight: bold;
            background: #e9ecef;
            padding: 4px 8px;
            margin-top: 10px;
        }

        .summary-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }

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

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="text-center fw-bold text-primary">AREA WISE SALE REPORT</h3>

                    <form id="saleSearchForm">
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
                                <select class="form-control select2" name="city[]" id="citySelect" multiple>
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
                                <input type="date" id="start_date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="button" id="searchSale" class="btn btn-primary btn-lg px-5">Search</button>
                        </div>
                    </form>

                    <hr>
                    <div id="reportResults"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(function() {
        $('#citySelect').select2({
            placeholder: 'Select Cities',
            allowClear: true,
            width: '100%',
            closeOnSelect: false
        });
    });

    $(document).ready(function() {
        $('#citySelect').on('change', function() {
            let cities = $(this).val() || [];

            if (cities.includes("All")) {
                let allCities = $("#citySelect option").map(function() {
                    return $(this).val();
                }).get();
                cities = allCities.filter(c => c !== "All");
                $('#citySelect').val(cities).trigger('change.select2');
            }

            $('#areasContainer').html('<p class="text-muted">Loading areas...</p>');
            if (cities.length === 0) {
                $('#areasContainer').html('<p class="text-danger">Please select city.</p>');
                return;
            }

            $.ajax({
                url: "{{ route('fetch-areas-report') }}",
                method: "GET",
                data: {
                    cities: cities
                },
                dataType: "json",
                success: function(data) {
                    if (!data || data.length === 0) {
                        $('#areasContainer').html('<p class="text-danger">No areas found.</p>');
                        return;
                    }

                    const byCity = {};
                    data.forEach(row => {
                        if (!byCity[row.city]) byCity[row.city] = new Set();
                        byCity[row.city].add(row.area);
                    });

                    let html = '';
                    Object.entries(byCity).forEach(([c, setAreas]) => {
                        html += `<div class="col-12 fw-bold mt-2">${c}</div>`;
                        Array.from(setAreas).forEach((a, idx) => {
                            html += `
                        <div class="col-md-2">
                            <div class="form-check">
                                <input class="form-check-input area-checkbox" type="checkbox" name="area[]" value="${a}" id="area_${c}_${idx}" checked>
                                <label class="form-check-label" for="area_${c}_${idx}">${a}</label>
                            </div>
                        </div>
                    `;
                        });
                    });
                    $('#areasContainer').html(html);
                },
                error: function() {
                    $('#areasContainer').html('<p class="text-danger">Error fetching areas.</p>');
                }
            });
        });

        $('#searchSale').click(function() {
            let salesman = $('#salesman').val();
            let city = $('#citySelect').val();
            let area = [];
            $('.area-checkbox:checked').each(function() {
                area.push($(this).val());
            });
            let startDate = $('#start_date').val();
            let endDate = $('#end_date').val();

            if (!city || (!startDate || !endDate) || (city !== "All" && area.length === 0)) {
                alert('Please fill all fields!');
                return;
            }

            $.ajax({
                url: "{{ route('fetch.area.wise.sale.report') }}",
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
                    let grandDistributorSale = 0;
                    let grandCustomerSale = 0;

                    const dataByCity = response.data;
                    const salesmanName = response.salesman_name;

                    if ($.isEmptyObject(dataByCity)) {
                        $('#reportResults').append('<div class="alert alert-info text-center">No sales data found for the selected criteria.</div>');
                        return;
                    }

                    let headerHTML = `
                        <div class="report-header text-center mb-4">
                            <h3 class="fw-bold">Area Wise Sale Report for Salesman: <span class="text-primary">${salesmanName}</span></h3>
                            <p>Date Range: ${startDate} to ${endDate}</p>
                        </div>
                        <hr>
                    `;
                    $('#reportResults').append(headerHTML);

                    Object.keys(dataByCity).forEach(city => {
                        let cityHTML = `<div class="section-title text-primary fs-5">${city.toUpperCase()}</div>`;
                        let cityDistTotal = 0;
                        let cityCustTotal = 0;

                        const areasData = dataByCity[city];
                        Object.keys(areasData).forEach(areaName => {
                            let areaData = areasData[areaName];
                            cityDistTotal += areaData.total_distributor;
                            cityCustTotal += areaData.total_customer;
                            
                            cityHTML += `<div class="area-title text-dark">Area: ${areaName}</div>`;

                            // Distributor Sales Table
                            if (areaData.distributor_sales.length > 0) {
                                cityHTML += `
                                    <div class="mt-2 fw-bold text-info ml-3">Distributor Sales</div>
                                    <table class="report-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Invoice</th>
                                                <th>Booker</th>
                                                 <th>Salesman</th>
                                                <th>Distributor Name</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                `;
                                areaData.distributor_sales.forEach(sale => {
                                    cityHTML += `
                                        <tr>
                                            <td>${sale.Date}</td>
                                            <td>${sale.invoice_number}</td>
                                            <td>${sale.Booker}</td>
                                             <td>${sale.Saleman}</td>
                                             <td>${sale.distributor ? sale.distributor.Customer : (sale.distributor_id || 'N/A')}</td>
                                             <td>${parseFloat(sale.net_amount).toLocaleString()}</td>
                                        </tr>
                                    `;
                                });
                                cityHTML += `
                                        <tr class="summary-row">
                                            <td colspan="5" class="text-end">Total Area Distributor Sale</td>
                                            <td>${parseFloat(areaData.total_distributor).toLocaleString()}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                `;
                            }

                            // Customer Sales Table
                            if (areaData.customer_sales.length > 0) {
                                cityHTML += `
                                    <div class="mt-2 fw-bold text-success ml-3">Customer Sales</div>
                                    <table class="report-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Invoice</th>
                                                <th>Booker</th>
                                                <th>Salesman</th>
                                                <th>Shop Name</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                `;
                                areaData.customer_sales.forEach(sale => {
                                    cityHTML += `
                                        <tr>
                                            <td>${sale.Date}</td>
                                            <td>${sale.invoice_number}</td>
                                            <td>${sale.Booker}</td>
                                            <td>${sale.Saleman}</td>
                                            <td>${sale.customer_shopname}</td>
                                            <td>${parseFloat(sale.net_amount).toLocaleString()}</td>
                                        </tr>
                                    `;
                                });
                                cityHTML += `
                                        <tr class="summary-row">
                                            <td colspan="5" class="text-end">Total Area Customer Sale</td>
                                            <td>${parseFloat(areaData.total_customer).toLocaleString()}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                `;
                            }

                            cityHTML += `<div class="text-end mt-1 mb-3"><strong>Area Grand Total: ${(areaData.total_distributor + areaData.total_customer).toLocaleString()}</strong></div>`;
                        });

                        cityHTML += `
                            <div class="alert alert-secondary text-end mt-3">
                                <strong>City Total Distributor Sale:</strong> ${cityDistTotal.toLocaleString()} <br>
                                <strong>City Total Customer Sale:</strong> ${cityCustTotal.toLocaleString()} <br>
                                <strong>City Grand Total Sale:</strong> ${(cityDistTotal + cityCustTotal).toLocaleString()}
                            </div>
                            <hr>
                        `;
                        grandDistributorSale += cityDistTotal;
                        grandCustomerSale += cityCustTotal;
                        $('#reportResults').append(cityHTML);
                    });

                    // Grand Totals at the end
                    $('#reportResults').append(`
                        <div class="card bg-light border-primary mt-4">
                            <div class="card-body">
                                <h4 class="fw-bold text-center mb-3">REPORT GRAND TOTALS</h4>
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <h5>Total Distributor Sale:</h5>
                                        <h3 class="text-info">${grandDistributorSale.toLocaleString()}</h3>
                                    </div>
                                    <div class="col-md-4">
                                        <h5>Total Customer Sale:</h5>
                                        <h3 class="text-success">${grandCustomerSale.toLocaleString()}</h3>
                                    </div>
                                    <div class="col-md-4">
                                        <h5>Grand Total Sale:</h5>
                                        <h3 class="text-primary">${(grandDistributorSale + grandCustomerSale).toLocaleString()}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                },
                error: function() {
                    alert('Failed to load data');
                }
            });
        });
    });
</script>

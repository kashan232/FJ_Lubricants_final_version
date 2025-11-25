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

        .summary-row {
            font-weight: bold;
            background-color: #e9ecef;
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
                    <h3 class="text-center fw-bold text-primary">MARKET CREDIT REPORT</h3>

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

            // Agar "All" select hua to sari cities auto select kar do
            if (cities.includes("All")) {
                let allCities = $("#citySelect option").map(function() {
                    return $(this).val();
                }).get();

                // "All" ke bina sari cities select karo
                cities = allCities.filter(c => c !== "All");

                // Select2 me reflect karo
                $('#citySelect').val(cities).trigger('change.select2');
            }

            // --- Baaki AJAX same rahega ---
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

                    // ✅ saare areas ko default checked kar diya
                    $('#areasContainer').html(html);
                },
                error: function() {
                    $('#areasContainer').html('<p class="text-danger">Error fetching areas.</p>');
                }
            });
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

            if (!city || (!startDate || !endDate) || (city !== 'All' && area.length === 0)) {
                alert('Please fill all fields!');
                return;
            }

            $.ajax({
                url: "{{ route('fetch.receivable.report') }}",
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

                    let totalDistributors = 0;
                    let totalCustomers = 0;

                    const dataByCity = response.data;
                    const salesmanName = response.salesman_name; // ✅ Get salesman name from response

                    // Display Salesman Name at the top of the report
                    if (salesmanName) {
                        $('#reportResults').append(`
                        <div class="report-header text-center mb-4">
                            <h3 class="fw-bold">Market Credit Report for Salesman: <span class="text-primary">${salesmanName}</span></h3>
                            <p>Date Range: ${startDate} to ${endDate}</p>
                        </div>
                        <hr>
                    `);
                    }


                    Object.keys(dataByCity).forEach(city => {
                        let cityDistributors = dataByCity[city].distributors;
                        let cityCustomers = dataByCity[city].customers;

                        let distributorTotal = 0;
                        let customerTotal = 0;

                        totalDistributors += cityDistributors.length;
                        totalCustomers += cityCustomers.length;

                        let cityHTML = `<div class="section-title text-primary fs-5">${city.toUpperCase()}</div>`;

                        // ========== DISTRIBUTOR MARKET CREDIT ==========
                        if (cityDistributors.length > 0) {
                            cityHTML += `
            <div class="section-title text-info">Distributor Market Credit</div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>PCode</th>
                        <th>Distributor Name</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
        `;

                            cityDistributors.forEach(dis => {
                                let bal = parseFloat(dis.balance);
                                distributorTotal += bal;

                                cityHTML += `
                <tr>
                    <td>${dis.pcode}</td>
                    <td>${dis.name}</td>
                    <td>${dis.address}</td>
                    <td>${dis.contact}</td>
                    <td>${bal.toLocaleString()}</td>
                </tr>
            `;
                            });

                            cityHTML += `
                <tr class="summary-row">
                    <td colspan="4" class="text-end">Total Distributor Credit</td>
                    <td>${distributorTotal.toLocaleString()}</td>
                </tr>
                <tr class="summary-row">
                    <td colspan="4" class="text-end">Total Distributors in ${city}:</td>
                    <td>${cityDistributors.length}</td>
                </tr>
                </tbody>
            </table>
        `;
                        }

                        // ========== CUSTOMER MARKET CREDIT ==========
                        if (cityCustomers.length > 0) {
                            cityHTML += `
            <div class="section-title text-success">Customer Market Credit</div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>PCode</th>
                        <th>ShopName</th>
                        <th>Customer Name</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
        `;

                            cityCustomers.forEach(cus => {
                                let bal = parseFloat(cus.balance);
                                customerTotal += bal;

                                cityHTML += `
                <tr>
                    <td>${cus.pcode}</td>
                    <td>${cus.shopname}</td>
                    <td>${cus.name}</td>
                    <td>${cus.address}</td>
                    <td>${cus.contact}</td>
                    <td>${bal.toLocaleString()}</td>
                </tr>
            `;
                            });

                            cityHTML += `
                <tr class="summary-row">
                    <td colspan="5" class="text-end">Total Customer Credit</td>
                    <td>${customerTotal.toLocaleString()}</td>
                </tr>
                <tr class="summary-row">
                    <td colspan="5" class="text-end">Total Customers in ${city}:</td>
                    <td>${cityCustomers.length}</td>
                </tr>
                </tbody>
            </table>
        `;
                        }

                        const cityTotal = distributorTotal + customerTotal;
                        grandTotal += cityTotal;

                        cityHTML += `
        <div class="text-end mt-2 fw-bold text-dark">
            Total Credit (Distributor + Customer) in ${city}: ${cityTotal.toLocaleString()}
        </div>
        <hr>
    `;

                        $('#reportResults').append(cityHTML);
                    });

                    // ==== GRAND TOTAL ====
                    $('#reportResults').append(`
        <div class="section-title text-dark text-end fs-5">
            <strong>Total Distributors: </strong> ${totalDistributors.toLocaleString()}
        </div>
        <div class="section-title text-dark text-end fs-5">
            <strong>Total Customers: </strong> ${totalCustomers.toLocaleString()}
        </div>
        <div class="section-title text-dark text-end fs-5">
            <strong>Grand Credit Amount: </strong> ${grandTotal.toLocaleString()}
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
<script>
    document.getElementById("downloadPdf").addEventListener("click", function() {
        const element = document.querySelector(".ledger-container");

        html2canvas(element).then(canvas => {
            const imgData = canvas.toDataURL("image/png");
            const pdf = new jspdf.jsPDF("p", "mm", "a4");

            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

            pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
            pdf.save("Vendor_ledger .pdf");
        });
    });

    // Show PDF button only when result appears
    $('#searchLedger').click(function() {
        setTimeout(() => {
            $('#downloadPdf').removeClass('d-none');
        }, 500);
    });
</script>
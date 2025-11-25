@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Sale Return Management</h4>
                    <h6>Manage Sale Return Efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    <form action="{{ route('sale-return.store') }}" method="POST" id="sale-return-form">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="sale_type" class="form-label">Select Sale Type</label>
                                @if(Auth::check() && Auth::user()->usertype === 'admin')
                                <select class="form-select" id="sale_type" name="sale_type" required>
                                    <option value="">-- Select Sale Type --</option>
                                    <option value="distributor">Distributor Sale</option>
                                    <option value="customer">Local Customer Sale</option>
                                </select>
                                @elseif(Auth::check() && Auth::user()->usertype === 'distributor')
                                <select class="form-select" id="sale_type" name="sale_type" required>
                                    <option value="">-- Select Sale Type --</option>
                                    <option value="customer">Local Customer Sale</option>
                                </select>
                                @endif

                            </div>

                            <div class="col-md-6">
                                <label for="invoice_number" class="form-label">Invoice Number</label>
                                <select class="form-select" id="invoice_number" name="invoice_number" required>
                                    <option value="">-- Select Invoice Number --</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="party_id" class="form-label">Party ID</label>
                                <input type="text" class="form-control" id="party_id" name="party_id" readonly>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-success" id="searchSale">Search</button>
                        </div>

                        <!-- Static Table for Displaying Sale Data -->
                        <div id="sale-details-section" class="mt-4">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th id="partyTypeTh">Distributor/Customer</th>
                                            <th>Invoice</th>
                                            <th>Item</th>
                                            <th>Pcs/Carton</th>
                                            <th>Carton Qty</th>
                                            <th>Pcs Qty</th>
                                            <th>Liter</th>
                                            <th>Rate</th>
                                            <th>Discount</th>
                                            <th>Total</th>
                                            <th>Return Carton Qty</th>
                                            <th>Return Pcs Qty</th>
                                            <th>Return Amount</th>
                                        </tr>
                                    </thead>

                                    <tbody id="return-table-body" class="text-center align-middle">
                                        {{-- Rows will be generated dynamically via JS --}}
                                    </tbody>
                                    <tfoot class="text-end fw-bold">
                                        <tr>
                                            <td colspan="11">Gross Amount:</td>
                                            <td id="grossAmount">0</td>
                                        </tr>
                                        <tr>
                                            <td colspan="11">Discount Amount:</td>
                                            <td id="discountAmount">0</td>
                                        </tr>
                                        <tr>
                                            <td colspan="11">Scheme Amount:</td>
                                            <td id="schemeAmount">0</td>
                                        </tr>
                                        <tr>
                                            <td colspan="11">Net Amount:</td>
                                            <td id="netAmount">0</td>
                                        </tr>
                                        <tr>
                                            <td colspan="11">Total Return Amount:</td>
                                            <td id="totalReturnAmount">0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <button type="submit" class="btn btn-primary">Submit Return</button>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .table th,
    .table td {
        vertical-align: middle !important;
    }

    .form-control-plaintext {
        background-color: #f1f1f1;
        text-align: center;
        font-weight: bold;
    }

    tfoot td {
        background-color: #f9f9f9;
    }

    .table tfoot tr:last-child td {
        background-color: #ffe5e5;
        color: #b30000;
        font-size: 1rem;
    }
</style>

<!-- jQuery CDN if not already included -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#sale_type').on('change', function() {
        let saleType = $(this).val();
        $('#invoice_number').html('<option value="">Loading...</option>');

        if (saleType) {
            $.ajax({
                url: '{{ route("get-sale-invoices") }}',
                type: 'GET',
                data: {
                    sale_type: saleType
                },
                success: function(data) {
                    let options = '<option value="">-- Select Invoice Number --</option>';
                    $.each(data, function(key, value) {
                        options += `<option value="${value}">${value}</option>`;
                    });
                    $('#invoice_number').html(options);
                }
            });
        } else {
            $('#invoice_number').html('<option value="">-- Select Invoice Number --</option>');
        }
    });

    $('#searchSale').on('click', function() {
        let saleType = $('#sale_type').val();
        let invoiceNumber = $('#invoice_number').val();

        if (!saleType || !invoiceNumber) {
            alert('Please select both Sale Type and Invoice Number.');
            return;
        }

        $.ajax({
            url: '{{ route("fetch-sale-details") }}',
            type: 'GET',
            data: {
                sale_type: saleType,
                invoice_number: invoiceNumber
            },
            success: function(response) {
                if (response.success) {
                    let tableHTML = '';
                    let totalReturnAmount = 0;
                    let grossAmount = 0;

                    $.each(response.sales, function(index, sale) {
                        let rate = sale.rate;
                        let itemAmount = sale.item_total;
                        grossAmount += itemAmount;

                        // Ensure sale.item_id is valid and not "N/A"
                        let itemID = sale.item_id ? sale.item_id : 'Unknown';
                        tableHTML += `<tr data-item-id="${itemID}"> 
        <td>${sale.distributor}</td>
        <td>${sale.invoice_number}</td>
        <td>${sale.item}</td>
        <td>${sale.packing}</td>
        <td>${sale.carton_quantity}</td>
        <td>${sale.pcs_quantity}</td>
        <td>${sale.liter}</td>
        <td>${rate}</td>
        <td>${sale.discount_amount}</td>
        <td>${itemAmount.toFixed(2)}</td>
        <td>
            <input type="number" min="0" class="form-control return-carton-qty" 
                   data-index="${index}" data-rate="${rate}" value="0" />
        </td>
        <td>
            <input type="number" min="0" class="form-control return-pcs-qty" 
                   data-index="${index}" data-rate="${rate}" value="0" />
        </td>
        <td>
            <input type="text" class="form-control-plaintext return-amount" 
                   readonly value="0" />
        </td>
    </tr>`;
                    });





                    $('#return-table-body').html(tableHTML);

                    // Ensure the values are treated as numbers for formatting
                    $('#grossAmount').text(grossAmount.toFixed(2));
                    $('#discountAmount').text(parseFloat(response.summary.discount_value || 0).toFixed(2));
                    $('#schemeAmount').text(parseFloat(response.summary.scheme_value || 0).toFixed(2));
                    $('#netAmount').text(parseFloat(response.summary.net_amount || 0).toFixed(2));
                    $('#totalReturnAmount').text(parseFloat(response.summary.total_return_amount || 0).toFixed(2));

                    $('#party_id').val(response.party_id);
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Sale details not found.');
            }
        });
    });


    $(document).on('input', '.return-carton-qty, .return-pcs-qty', function() {
        let $row = $(this).closest('tr');
        let rate = parseFloat($row.find('.return-carton-qty').data('rate')) || 0;
        let pcsPerCarton = parseFloat($row.find('td:nth-child(4)').text()) || 1;
        let returnCartonQty = parseFloat($row.find('.return-carton-qty').val()) || 0;
        let returnPcsQty = parseFloat($row.find('.return-pcs-qty').val()) || 0;

        // Total return amount ki calculation
        let returnAmount = (rate * returnCartonQty) + ((rate / pcsPerCarton) * returnPcsQty);
        $row.find('.return-amount').val(returnAmount.toFixed(2));

        // Total return amount ko update karna
        let totalReturnAmount = 0;
        $('.return-amount').each(function() {
            totalReturnAmount += parseFloat($(this).val()) || 0;
        });

        $('#totalReturnAmount').text(totalReturnAmount.toFixed(2));
    });

    $('#sale-return-form').on('submit', function(e) {
        e.preventDefault();

        let saleType = $('#sale_type').val();
        let invoiceNumber = $('#invoice_number').val();
        let returnItems = [];

        $('#return-table-body tr').each(function() {
            let item = {
                item_id: $(this).data('item-id'),
                item_name: $(this).find('td:eq(2)').text(), // 3rd column me item name hai
                pcs_per_carton: parseInt($(this).find('td:eq(3)').text()),
                carton_qty: parseInt($(this).find('.return-carton-qty').val()) || 0,
                pcs_qty: parseInt($(this).find('.return-pcs-qty').val()) || 0,
                rate: parseFloat($(this).find('td:eq(7)').text()),
                discount: parseFloat($(this).find('td:eq(8)').text()) || 0,
                total: parseFloat($(this).find('.return-amount').val()) || 0,
            };

            console.log("Return Item:", item); // Debugging line

            if (item.carton_qty > 0 || item.pcs_qty > 0) {
                returnItems.push(item);
            }
        });

        if (returnItems.length === 0) {
            alert('Please enter at least one return quantity.');
            return;
        }

        let partyId = $('#party_id').val();
        $.ajax({
            url: '{{ route("sale-return.store") }}',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                sale_type: saleType,
                party_id: partyId,
                invoice_number: invoiceNumber,
                return_items: returnItems,
                _token: '{{ csrf_token() }}'
            }),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Reset or reload code here (e.g., reset the form or page reload)
                            $('#sale-return-form')[0].reset(); // Reset the form
                            // Optionally, reload page or perform other actions
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while processing the sale return.',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed!',
                    text: 'Failed to submit sale return. Please try again.',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
</script>
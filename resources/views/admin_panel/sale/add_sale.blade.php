@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Distributor Sales Management</h4>
                    <h6>Manage Distributor Sales Efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif

                    <form action="{{ route('store-sale') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Invoice Date</label>
                                <input type="date" class="form-control" name="Date" id="Date">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="distributor" class="form-label">Select Distributor</label>
                                <select class="form-control" name="distributor_id" id="distributor">
                                    <option value="">Select Distributor</option>
                                    @foreach($Distributors as $distributor)
                                    <option value="{{ $distributor->id }}"
                                        data-city="{{ $distributor->City }}"
                                        data-area="{{ $distributor->Area }}"
                                        data-address="{{ $distributor->Address }}"
                                        data-phone="{{ $distributor->Contact }}">
                                        {{ $distributor->Customer }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="distributor_city" id="city" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Area</label>
                                <input type="text" class="form-control" name="distributor_area" id="area" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="distributor_address" id="address" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="distributor_phone" id="phone" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Order Booker</label>
                                <select class="form-control" name="Booker" id="Booker" required>
                                    <option disabled>Select Booker</option>
                                    @foreach($Staffs as $Staff)
                                    <option value="{{ $Staff->name }}">{{ $Staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Saleman</label>
                                <select class="form-control" name="Saleman" id="Saleman" required>
                                    <option disabled>Select Salesman</option>
                                    @foreach($Staffs as $Staff)
                                    <option value="{{ $Staff->name }}">{{ $Staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center" id="purchaseTable">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Sub Category</th>
                                        <th>Code</th>
                                        <th>Item</th>
                                        <th>Measurement</th>
                                        <th>Packing</th>
                                        <th>Carton Qty</th>
                                        <th>Pcs Qty</th>
                                        <th>Liter</th>
                                        <th>Rate</th>
                                        <th>Disc Rs</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select class="form-control form-control-lg category-select" name="category[]" style="width: 150px;">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                <option value="{{ $category->category_name }}">{{ $category->category_name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control form-control-lg subcategory-select" name="subcategory[]" style="width: 150px;">
                                                <option>Select Subcategory</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control form-control-lg code" name="code[]" style="width: 130px;" readonly></td>
                                        <td>
                                            <select class="form-control form-control-lg item-select" name="item[]" style="width: 400px;">
                                                <option>Select Item</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control form-control-lg size" name="size[]" style="width: 180px;" readonly></td>
                                        <td><input type="number" class="form-control form-control-lg pcs-carton" name="pcs_carton[]" style="width: 180px;" readonly></td>
                                        <td><input type="number" class="form-control form-control-lg carton-qty" name="carton_qty[]" style="width: 180px;"></td>
                                        <td><input type="number" class="form-control form-control-lg pcx" name="pcs[]" style="width: 180px;"></td>
                                        <td><input type="number" class="form-control form-control-lg liter" name="liter[]" step="any" style="width: 180px;"></td>
                                        <td><input type="number" class="form-control form-control-lg rate" name="rate[]" style="width: 180px;"></td>
                                        <td><input type="number" class="form-control form-control-lg discount" name="discount[]" style="width: 180px;"></td>
                                        <td><input type="number" class="form-control form-control-lg amount" name="amount[]" style="width: 180px;" readonly></td>
                                        <td><button type="button" class="btn btn-danger remove-row">Delete</button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-end fw-bold">Grand Total:</td>
                                        <td colspan="2">
                                            <input type="number" class="form-control form-control-lg fw-bold text-center" id="grandTotal" name="grand_total" readonly>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="text-end fw-bold">Discount:</td>
                                        <td colspan="2">
                                            <div class="input-group">
                                                <input type="number" class="form-control form-control-lg fw-bold text-center" id="discountValue" name="discount_value" value="0">
                                                <select id="discountType" class="form-control form-control-lg">
                                                    <option value="pkr">PKR</option>
                                                    <option value="percent">%</option>
                                                </select>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="text-end fw-bold">Scheme:</td>
                                        <td colspan="2">
                                            <input type="number" class="form-control form-control-lg fw-bold text-center" id="schemeValue" name="scheme_value" value="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="text-end fw-bold">Net Amount:</td>
                                        <td colspan="2">
                                            <input type="number" class="form-control form-control-lg fw-bold text-center" id="netAmount" name="net_amount" readonly>
                                        </td>
                                    </tr>

                                </tfoot>

                            </table>
                        </div>

                        <button type="button" class="btn btn-success mt-3" id="addRow">Add More</button>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')

<!-- JavaScript to Auto-Fill Distributor Details -->
<script>
    document.getElementById('distributor').addEventListener('change', function() {
        let selectedOption = this.options[this.selectedIndex];

        document.getElementById('city').value = selectedOption.getAttribute('data-city') || '';
        document.getElementById('area').value = selectedOption.getAttribute('data-area') || '';
        document.getElementById('address').value = selectedOption.getAttribute('data-address') || '';
        document.getElementById('phone').value = selectedOption.getAttribute('data-phone') || '';
    });


    $(document).ready(function() {
        // Add New Row
        $(document).on('click', '#addRow', function() {
            let newRow = `
    <tr>
        <td>
            <select class="form-control form-control-lg category-select" name="category[]" style="width: 150px;">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                <option value="{{ $category->category_name }}">{{ $category->category_name }}</option>
                                                @endforeach
                                            </select>
        </td>
        <td>
            <select class="form-control form-control-lg subcategory-select" name="subcategory[]" style="width: 150px;">
                                                <option>Select Subcategory</option>
                                            </select>
        </td>
        <td>
                                            <input type="text" class="form-control form-control-lg code" name="code[]" style="width: 130px;" readonly>
                                        </td>
        <td>
                                            <select class="form-control form-control-lg item-select" name="item[]" style="width: 400px;">
                                                <option>Select Item</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control form-control-lg size" name="size[]" style="width: 180px;" readonly></td>
                                        <td><input type="number" class="form-control form-control-lg pcs-carton" name="pcs_carton[]" style="width: 180px;" readonly></td>
                                        <td><input type="number" class="form-control form-control-lg carton-qty" name="carton_qty[]" style="width: 180px;"></td>
                                        <td><input type="number" class="form-control form-control-lg pcx" name="pcs[]" style="width: 180px;"></td>
                                        <td><input type="number" class="form-control form-control-lg liter" name="liter[]" step="any" style="width: 180px;"></td>
                                        <td><input type="number" class="form-control form-control-lg rate" name="rate[]" style="width: 180px;"></td>
                                        <td><input type="number" class="form-control form-control-lg discount" name="discount[]" style="width: 180px;"></td>
                                        <td><input type="number" class="form-control form-control-lg amount" name="amount[]" style="width: 180px;" readonly></td>
        <td><button type="button" class="btn btn-danger remove-row">Delete</button></td>
    </tr>`;

            $("#purchaseTable tbody").append(newRow);
        });


        // Remove row functionality
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            calculateGrandTotal(); // Recalculate grand total after row removal
        });

        // Fetch Subcategories on Category Change
        $(document).on('change', '.category-select', function() {
            let categoryName = $(this).val();
            let subCategoryDropdown = $(this).closest('tr').find('.subcategory-select');

            if (categoryName) {
                $.ajax({
                    url: "{{ route('get.subcategories', ':categoryname') }}".replace(':categoryname', categoryName),
                    type: 'GET',
                    success: function(response) {
                        subCategoryDropdown.html('<option value="">Select Sub Category</option>');
                        $.each(response, function(index, name) {
                            subCategoryDropdown.append(`<option value="${name}">${name}</option>`);
                        });
                    },
                    error: function() {
                        alert('Error fetching subcategories.');
                    }
                });
            } else {
                subCategoryDropdown.html('<option value="">Select Sub Category</option>');
            }
        });

        // Fetch Items on Subcategory Change
        $(document).on('change', '.subcategory-select', function() {
            let subCategoryName = $(this).val();
            let categoryName = $(this).closest('tr').find('.category-select').val();
            let itemDropdown = $(this).closest('tr').find('.item-select');

            if (subCategoryName && categoryName) {
                $.ajax({
                    url: "{{ route('get.items') }}",
                    type: 'GET',
                    data: {
                        category_name: categoryName,
                        sub_category_name: subCategoryName
                    },
                    success: function(response) {
                        itemDropdown.html('<option value="">Select Item</option>');
                        $.each(response, function(index, item) {
                            // itemDropdown.append(`<option value="${item.item_name}" data-pcs="${item.pcs_in_carton}" data-code="${item.item_code}" data-size	="${item.size}"  data-rp="${item.retail_price}">${item.item_name}</option>`);
                            itemDropdown.append(`<option value="${item.item_name}" data-pcs="${item.pcs_in_carton}" data-code="${item.item_code}" data-size="${item.size}" data-rp="${item.retail_price}">${item.item_name}</option>`);

                        });
                    },
                    error: function() {
                        alert('Error fetching items.');
                    }
                });
            } else {
                itemDropdown.html('<option value="">Select Item</option>');
            }
        });

        // Fetch PCS when Item is Selected
        $(document).on('change', '.item-select', function() {
            let pcsValue = $(this).find(":selected").data('pcs') || 0;
            $(this).closest('tr').find('.pcs-carton').val(pcsValue);
        });

        $(document).on('change', '.item-select', function() {
            let rpValue = $(this).find(":selected").data('rp') || 0;
            $(this).closest('tr').find('.rate').val(rpValue);
        });

        $(document).on('change', '.item-select', function() {
            let codeValue = $(this).find(":selected").data('code') || 0;
            $(this).closest('tr').find('.code').val(codeValue);
        });

        $(document).on('change', '.item-select', function() {
            let selectedOption = $(this).find(":selected");
            let sizeValue = selectedOption.data('size') || 0;

            console.log("Selected Item:", selectedOption.text());
            console.log("Size Value:", sizeValue);

            $(this).closest('tr').find('.size').prop('readonly', false).val(sizeValue).prop('readonly', true);
        });



        $(document).on('input', '.carton-qty, .pcs-carton, .size, .pcx, .rate, .discount', function() {
            let row = $(this).closest('tr');

            let cartonQty = parseFloat(row.find('.carton-qty').val()) || 0;
            let packing = parseFloat(row.find('.pcs-carton').val()) || 0;
            let pcsQty = parseFloat(row.find('.pcx').val()) || 0;
            let rate = parseFloat(row.find('.rate').val()) || 0;
            let discount = parseFloat(row.find('.discount').val()) || 0;
            let sizeText = row.find('.size').val().toLowerCase().trim();
            let measurement = 0;

            if (sizeText.includes('ml')) {
                measurement = parseFloat(sizeText.replace(/[^0-9.]/g, '')) / 1000;
            } else if (sizeText.includes('l')) {
                measurement = parseFloat(sizeText.replace(/[^0-9.]/g, ''));
            } else {
                measurement = parseFloat(sizeText) || 0;
            }

            // ✅ **Updated Liter Calculation**
            let litersFromCartons = cartonQty * packing * measurement;
            let litersFromPcs = pcsQty * measurement;
            let totalLiters = litersFromCartons + litersFromPcs;

            // 🟢 Remove trailing zeros (17.50 → 17.5, 16.80 → 16.8)
            row.find('.liter').val(parseFloat(totalLiters.toFixed(2)).toString());

            // 🧮 **Carton Amount Calculation**
            let cartonAmount = rate * cartonQty;

            // 🔢 **Per Piece Rate Calculation**
            let perPieceRate = (packing > 0) ? (rate / packing) : 0;

            // 💰 **Pcs Amount Calculation**
            let pcsAmount = perPieceRate * pcsQty;

            // 📊 **Total Before Discount**
            let totalBeforeDiscount = cartonAmount + pcsAmount;

            // 💸 **Final Amount After Applying Discount**
            let finalAmount = totalBeforeDiscount - discount;

            // 🟢 Remove trailing zeros from amount field
            row.find('.amount').val(parseFloat(finalAmount.toFixed(2)).toString());

            // Recalculate Grand Total
            calculateGrandTotal();
        });

        function calculateGrandTotal() {
            let grandTotal = 0;
            $(".amount").each(function() {
                grandTotal += parseFloat($(this).val()) || 0;
            });

            // 🟢 Remove trailing zeros from Grand Total
            $("#grandTotal").val(parseFloat(grandTotal.toFixed(2)).toString());

            // 🟢 Set Net Amount same as Grand Total initially
            $('#netAmount').val(parseFloat(grandTotal.toFixed(2)).toString());
        }


        // Function to Calculate Net Amount (including Discount & Scheme)
        $(document).on('input', '#discountValue, #discountType, #schemeValue', function() {
            let grandTotal = parseFloat($('#grandTotal').val()) || 0;
            let discountValue = parseFloat($('#discountValue').val()) || 0;
            let discountType = $('#discountType').val();
            let schemeValue = parseFloat($('#schemeValue').val()) || 0;
            let discountAmount = 0;

            if (discountType === "percent") {
                discountAmount = (grandTotal * discountValue) / 100;
            } else {
                discountAmount = discountValue;
            }

            let netAmount = grandTotal - discountAmount - schemeValue;

            // 🟢 Remove trailing zeros from Net Amount
            $('#netAmount').val(parseFloat(netAmount.toFixed(2)).toString());
        });



    });
</script>
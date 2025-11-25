@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4> Edit Distributor Sales Management</h4>
                    <h6>Manage Edit Distributor Sales Efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif
                    <form action="{{ route('sale.update', $original->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Invoice Date</label>
                                <input type="date" class="form-control" name="Date" id="Date" value="{{ $original['Date'] }}">
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
                                        data-phone="{{ $distributor->Contact }}"
                                        {{ $original['distributor_id'] == $distributor->id ? 'selected' : '' }}>
                                        {{ $distributor->Customer }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="distributor_city" class="form-control" id="city" value="{{ $original['distributor_city'] }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Area</label>
                                <input type="text" name="distributor_area" class="form-control" id="area" value="{{ $original['distributor_area'] }}">

                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="distributor_address" class="form-control" id="address" value="{{ $original['distributor_address'] }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="distributor_phone" class="form-control" id="phone" value="{{ $original['distributor_phone'] }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Order Booker</label>
                                <select class="form-control" name="Booker" id="Booker" required>
                                    <option disabled>Select Booker</option>
                                    @foreach($Staffs as $Staff)
                                    <option value="{{ $Staff->name }}" {{ $original['Booker'] == $Staff->name ? 'selected' : '' }}>{{ $Staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Saleman</label>
                                <select class="form-control" name="Saleman" id="Saleman" required>
                                    <option disabled>Select Salesman</option>
                                    @foreach($Staffs as $Staff)
                                    <option value="{{ $Staff->name }}" {{ $original['Booker'] == $Staff->name ? 'selected' : '' }}>{{ $Staff->name }}</option>
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
                                    @php
                                    $categoriesSelected = json_decode($original['category']);
                                    $subcategories = json_decode($original['subcategory']);
                                    $codes = json_decode($original['code']);
                                    $items = json_decode($original['item']);
                                    $sizes = json_decode($original['size']);
                                    $pcs_cartons = json_decode($original['pcs_carton']);
                                    $carton_qtys = json_decode($original['carton_qty']);
                                    $pcs = json_decode($original['pcs']);
                                    $liters = json_decode($original['liter']);
                                    $rates = json_decode($original['rate']);
                                    $discounts = json_decode($original['discount']);
                                    $amounts = json_decode($original['amount']);
                                    $rowCount = count($categoriesSelected);
                                    @endphp

                                    @for ($i = 0; $i < $rowCount; $i++)


                                        <tr>
                                        <td>
                                            <select class="form-control category-select" name="category[]" data-index="{{ $i }}">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                <option value="{{ $category->category_name }}"
                                                    {{ (isset($categoriesSelected[$i]) && $categoriesSelected[$i] == $category->category_name) ? 'selected' : '' }}>
                                                    {{ $category->category_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="subcategory-cell"> {{-- Added a class for easier targeting --}}
                                            <select class="form-control subcategory-select" name="subcategory[]">
                                                <option>Select Sub Category</option>
                                                {{-- Subcategories will be populated by JavaScript --}}
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control" name="code[]" style="width: 130px;" value="{{ $codes[$i] }}" readonly></td>
                                        <td><input type="text" class="form-control" name="item[]" style="width: 400px;" value="{{ $items[$i] }}"></td>
                                        <td><input type="text" class="form-control" name="size[]" value="{{ $sizes[$i] }}" readonly></td>
                                        <td><input type="number" class="form-control" name="pcs_carton[]" style="width: 180px;" value="{{ $pcs_cartons[$i] }}" readonly></td>
                                        <td><input type="number" class="form-control" name="carton_qty[]" style="width: 180px;" value="{{ $carton_qtys[$i] }}"></td>
                                        <td><input type="number" class="form-control" name="pcs[]" style="width: 180px;" value="{{ $pcs[$i] }}"></td>
                                        <td><input type="number" class="form-control" name="liter[]" style="width: 180px;" value="{{ $liters[$i] }}"></td>
                                        <td><input type="number" class="form-control" name="rate[]" style="width: 180px;" value="{{ $rates[$i] }}"></td>
                                        <td><input type="number" class="form-control" name="discount[]" style="width: 180px;" value="{{ $discounts[$i] }}"></td>
                                        <td><input type="number" class="form-control" name="amount[]" style="width: 180px;" value="{{ $amounts[$i] }}" readonly></td>
                                        <td><button type="button" class="btn btn-danger remove-row">Delete</button></td>
                                        </tr>
                                        @endfor
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-end fw-bold">Grand Total:</td>
                                        <td colspan="2">
                                            <input type="number" name="grand_total" class="form-control" id="grandTotal" value="{{ $original['grand_total'] }}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="text-end fw-bold">Discount:</td>
                                        <td colspan="2">
                                            <div class="input-group">
                                                <input type="number" name="discount_value" class="form-control" id="discountValue" value="{{ $original['discount_value'] }}">
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
                                            <input type="number" name="scheme_value" class="form-control" id="schemeValue" value="{{ $original['scheme_value'] }}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="text-end fw-bold">Net Amount:</td>
                                        <td colspan="2">
                                            <input type="number" name="net_amount" class="form-control" id="netAmount" value="{{ $original['net_amount'] }}">
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
<script>
    document.getElementById('distributor').addEventListener('change', function() {
        let selectedOption = this.options[this.selectedIndex];
        document.getElementById('city').value = selectedOption.getAttribute('data-city') || '';
        document.getElementById('area').value = selectedOption.getAttribute('data-area') || '';
        document.getElementById('address').value = selectedOption.getAttribute('data-address') || '';
        document.getElementById('phone').value = selectedOption.getAttribute('data-phone') || '';
    });

    document.querySelectorAll('.subcategory-select').forEach((select, index) => {
        let subcategories = @json($subcategories);
        if (subcategories[index]) {
            let option = new Option(subcategories[index], subcategories[index], true, true);
            select.add(option);
        }
    });

    function populateSubcategories(categorySelectElement, selectedSubcategoryName = null, selectedItemName = null) {
        let categoryName = $(categorySelectElement).val();
        let subCategoryDropdown = $(categorySelectElement).closest('tr').find('.subcategory-select');
        let row = $(categorySelectElement).closest('tr');

        if (categoryName) {
            $.ajax({
                url: "{{ route('get.subcategories', ':categoryname') }}".replace(':categoryname', categoryName),
                type: 'GET',
                success: function(response) {
                    subCategoryDropdown.html('<option value="">Select Sub Category</option>');
                    $.each(response, function(index, name) {
                        let selected = (selectedSubcategoryName && selectedSubcategoryName == name) ? 'selected' : '';
                        subCategoryDropdown.append(`<option value="${name}" ${selected}>${name}</option>`);
                    });
                    row.find('.subcategory-select').trigger('change', [selectedItemName]);
                },
                error: function() {
                    alert('Error fetching subcategories.');
                }
            });
        } else {
            subCategoryDropdown.html('<option value="">Select Sub Category</option>');
            row.find('.item-select').html('<option value="">Select Item</option>');
            row.find('.code, .size, .pcs-carton, .liter, .rate, .discount, .amount, .carton-qty, .pcx').val('');
            calculateGrandTotalFromRows();
        }
    }

    function calculateGrandTotalFromRows() {
        // Pehle se jo total stored hai (agar koi hai)
        let existingTotal = parseFloat($('#grandTotal').data('initial') || 0);

        // Naye amounts ka total
        let newTotal = 0;
        $(".amount").each(function() {
            let val = parseFloat($(this).val());
            if (!isNaN(val) && val > 0) {
                newTotal += val;
            }
        });

        let grandTotal = existingTotal + newTotal;

        $("#grandTotal").val(grandTotal.toFixed(2));


        calculateNetAmount();
    }

    function calculateNetAmount() {
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
        $('#netAmount').val(netAmount.toFixed(2));

    }

    $('#discountValue, #discountType, #schemeValue').on('input change', function() {
        calculateNetAmount();
    });
    // On page load, trigger calculation of amounts for existing rows
    $('#purchaseTable tbody tr').each(function(index) {
        let row = $(this);
        let categorySelect = row.find('.category-select');
        let selectedCategory = categorySelect.val();

        let originalSubcategory = @json($subcategories)[index] || null;
        let originalItem = @json($items)[index] || null;

        if (selectedCategory) {
            populateSubcategories(categorySelect, originalSubcategory, originalItem);
        }

        setTimeout(function() {
            recalcRowAmount(row);
        }, 50 * (index + 1));
    });

    // Add new row event - don't recalc immediately
    $(document).on('click', '#addRow', function() {
        let newRowHtml = `
            <tr>
                <td>
                    <select class="form-control category-select" name="category[]">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->category_name }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="subcategory-cell">
                    <select class="form-control form-control-lg subcategory-select" name="subcategory[]" style="width: 150px;">
                        <option value="">Select Subcategory</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-lg code" name="code[]" style="width: 130px;" readonly>
                </td>
                <td>
                    <select class="form-control form-control-lg item-select" name="item[]" style="width: 400px;">
                        <option value="">Select Item</option>
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

        $("#purchaseTable tbody").append(newRowHtml);
        // Don't calculate grand total here. Wait for input.
    });

    $(document).on('change', '.category-select', function() {
        let categoryName = $(this).val();
        let subCategoryDropdown = $(this).closest('tr').find('.subcategory-select');
        let row = $(this).closest('tr');

        if (categoryName) {
            $.ajax({
                url: "{{ route('get.subcategories', ':categoryname') }}".replace(':categoryname', categoryName),
                type: 'GET',
                success: function(response) {
                    subCategoryDropdown.html('<option value="">Select Sub Category</option>');
                    $.each(response, function(index, name) {
                        subCategoryDropdown.append(`<option value="${name}">${name}</option>`);
                    });
                    row.find('.subcategory-select').trigger('change');
                },
                error: function() {
                    alert('Error fetching subcategories.');
                }
            });
        } else {
            subCategoryDropdown.html('<option value="">Select Sub Category</option>');
            row.find('.item-select').html('<option value="">Select Item</option>');
            row.find('.code, .size, .pcs-carton, .liter, .amount').val('');
            row.find('.carton-qty, .pcx, .rate, .discount').val('0');
            calculateGrandTotalFromRows();
        }
    });

    $(document).on('change', '.subcategory-select', function(event, originalItemName = null) {
        let subCategoryName = $(this).val();
        let categoryName = $(this).closest('tr').find('.category-select').val();
        let itemDropdown = $(this).closest('tr').find('.item-select');
        let row = $(this).closest('tr');

        if (!subCategoryName || !categoryName) {
            itemDropdown.html('<option value="">Select Item</option>');
            row.find('.code, .size, .pcs-carton, .liter, .amount').val('');
            row.find('.carton-qty, .pcx, .rate, .discount').val('0');
            calculateGrandTotalFromRows();
            return;
        }

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
                    itemDropdown.append(`<option value="${item.item_name}" data-pcs="${item.pcs_in_carton}" data-code="${item.item_code}" data-size="${item.size}" data-rp="${item.retail_price}">${item.item_name}</option>`);
                });

                let itemToSelect = originalItemName || row.find('[name="item[]"]').val();
                if (itemToSelect && itemDropdown.find(`option[value="${itemToSelect}"]`).length) {
                    itemDropdown.val(itemToSelect); // Set the value
                    // Trigger change on item-select to fill other fields and calculate
                    row.find('.item-select').trigger('change');
                } else {
                    // For new rows or unselected items, reset fields to 0
                    row.find('.pcs-carton, .rate, .code, .size, .liter, .amount').val('');
                    row.find('.carton-qty, .pcx, .discount').val('0');
                    row.find('.carton-qty').trigger('input'); // This will trigger calculateGrandTotalFromRows
                }
            },
            error: function() {
                alert('Error fetching items.');
            }
        });
    });

    $(document).on('change', '.item-select', function() {
        let selectedOption = $(this).find(":selected");
        let row = $(this).closest('tr');

        row.find('.pcs-carton').val(selectedOption.data('pcs') || 0);
        row.find('.rate').val(selectedOption.data('rp') || 0);
        row.find('.code').val(selectedOption.data('code') || '');
        row.find('.size').val(selectedOption.data('size') || '');

        // Set quantities/discount/amount to 0 when a new item is selected
        row.find('.carton-qty').val('0');
        row.find('.pcx').val('0');
        row.find('.discount').val('0');
        row.find('.amount').val('0'); // Ensure amount is 0 before new calculation

        // Trigger calculation for the current row after details are filled
        row.find('.carton-qty').trigger('input');
    });


    $(document).on('input', '.carton-qty, .pcx, .rate, .discount, .pcs-carton, .size', function() {
        let row = $(this).closest('tr');
        let cartonQty = parseFloat(row.find('.carton-qty').val()) || 0;
        let packing = parseFloat(row.find('.pcs-carton').val()) || 0; // pcs per carton
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
        let litersFromCartons = cartonQty * packing * measurement;
        let litersFromPcs = pcsQty * measurement;
        let totalLiters = litersFromCartons + litersFromPcs;
        row.find('.liter').val(parseFloat(totalLiters.toFixed(2)).toString());
        let cartonAmount = rate * cartonQty;
        let perPieceRate = (packing > 0) ? (rate / packing) : 0;
        let pcsAmount = perPieceRate * pcsQty;
        let totalBeforeDiscount = cartonAmount + pcsAmount;
        let finalAmount = totalBeforeDiscount - discount;
        row.find('.amount').val(parseFloat(finalAmount.toFixed(2)).toString());
        calculateGrandTotalFromRows();
    });
    $(document).ready(function() {
        let initialGrandTotal = parseFloat($('#grandTotal').val()) || 0;
        $('#grandTotal').data('initial', initialGrandTotal);
        calculateGrandTotalFromRows();
    });
    $(document).on('input', '.carton-qty, .rate, .discount', function() {
        let row = $(this).closest('tr');
        recalcRowAmount(row);
    });


    function recalcRowAmount(row) {
        let qty = parseFloat(row.find('.carton-qty').val()) || 0;
        let rate = parseFloat(row.find('.rate').val()) || 0;
        let discount = parseFloat(row.find('.discount').val()) || 0;
        let amount = (qty * rate) - discount;
        if (amount < 0) amount = 0;
        row.find('.amount').val(amount.toFixed(2));
        calculateGrandTotalFromRows();
    }
</script>
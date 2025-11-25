@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Edit Local Sales Management</h4>
                    <h6>Manage Local Sales Efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif

                    <form action="{{ route('local.sale.update', $original->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Invoice Date</label>
                                <input type="date" class="form-control" name="Date" id="Date" value="{{ $original['Date'] }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="customer" class="form-label">Select Customer</label>
                                <select class="form-control" name="customer_id" id="customer">
                                    <option value="">Select Customer</option>
                                    @foreach($Customers as $Customer)
                                    <option value="{{ $Customer->id }}"
                                        data-city="{{ $Customer->city }}"
                                        data-area="{{ $Customer->area }}"
                                        data-address="{{ $Customer->address }}"
                                        data-phone="{{ $Customer->phone_number }}"
                                        data-shopname="{{ $Customer->shop_name }}"
                                        {{ isset($original['customer_id']) && $original['customer_id'] == $Customer->id ? 'selected' : '' }}>
                                        {{ $Customer->shop_name }} ({{ $Customer->customer_name }}) ({{ $Customer->area }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Shop Name</label>
                                <input type="text" class="form-control" name="customer_shopname" id="shopname" value="{{ $original['customer_shopname'] }}" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="customer_city" id="city" value="{{ $original['customer_city'] }}" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Area</label>
                                <input type="text" class="form-control" name="customer_area" id="area" value="{{ $original['customer_area'] }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="customer_address" id="address" value="{{ $original['customer_address'] }}" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="customer_phone" id="phone" value="{{ $original['customer_phone'] }}" readonly>
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
                                <label class="form-label">Salesman</label>
                                <select class="form-control" name="Saleman" id="Saleman" required>
                                    <option disabled>Select Salesman</option>
                                    @foreach($Staffs as $Staff)
                                    <option value="{{ $Staff->name }}" {{ $original['Saleman'] == $Staff->name ? 'selected' : '' }}>{{ $Staff->name }}</option>
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
                                        <td class="subcategory-cell">
                                            <select class="form-control subcategory-select" name="subcategory[]">
                                                <option value="">Select Sub Category</option>
                                                {{-- Subcategories will be populated by JavaScript --}}
                                                @if(isset($subcategories[$i]))
                                                <option value="{{ $subcategories[$i] }}" selected>{{ $subcategories[$i] }}</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control code" name="code[]" style="width: 130px;" value="{{ $codes[$i] ?? '' }}" readonly></td>
                                        <td>
                                            <select class="form-control item-select" name="item[]" style="width: 400px;">
                                                <option value="">Select Item</option>
                                                @if(isset($items[$i]))
                                                <option value="{{ $items[$i] }}" selected>{{ $items[$i] }}</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control size" name="size[]" value="{{ $sizes[$i] ?? '' }}" readonly></td>
                                        <td><input type="number" class="form-control pcs-carton" name="pcs_carton[]" style="width: 180px;" value="{{ $pcs_cartons[$i] ?? 0 }}" readonly></td>
                                        <td><input type="number" class="form-control carton-qty" name="carton_qty[]" style="width: 180px;" value="{{ $carton_qtys[$i] ?? 0 }}"></td>
                                        <td><input type="number" class="form-control pcx" name="pcs[]" style="width: 180px;" value="{{ $pcs[$i] ?? 0 }}"></td>
                                        <td><input type="number" class="form-control liter" name="liter[]" step="any" style="width: 180px;" value="{{ $liters[$i] ?? 0 }}"></td>
                                        <td><input type="number" class="form-control rate" name="rate[]" style="width: 180px;" value="{{ $rates[$i] ?? 0 }}"></td>
                                        <td><input type="number" class="form-control discount" name="discount[]" style="width: 180px;" value="{{ $discounts[$i] ?? 0 }}"></td>
                                        <td><input type="number" class="form-control amount" name="amount[]" style="width: 180px;" value="{{ $amounts[$i] ?? 0 }}" readonly></td>
                                        <td><button type="button" class="btn btn-danger remove-row">Delete</button></td>
                                        </tr>
                                        @endfor
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-end fw-bold">Grand Total:</td>
                                        <td colspan="2">
                                            <input type="number" name="grand_total" class="form-control" id="grandTotal" value="{{ $original['grand_total'] }}" readonly>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="text-end fw-bold">Discount:</td>
                                        <td colspan="2">
                                            <div class="input-group">
                                                <input type="number" name="discount_value" class="form-control" id="discountValue" value="{{ $original['discount_value'] }}">
                                                <select id="discountType" class="form-control form-control-lg" name="discount_type">
                                                    <option value="pkr" {{ $original['discount_type'] == 'pkr' ? 'selected' : '' }}>PKR</option>
                                                    <option value="percent" {{ $original['discount_type'] == 'percent' ? 'selected' : '' }}>%</option>
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
                                            <input type="number" name="net_amount" class="form-control" id="netAmount" value="{{ $original['net_amount'] }}" readonly>
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
    // Initial setup for customer details on page load
    document.addEventListener('DOMContentLoaded', function() {
        const customerSelect = document.getElementById('customer');
        if (customerSelect) {
            function updateCustomerDetails() {
                let selectedOption = customerSelect.options[customerSelect.selectedIndex];
                document.getElementById('shopname').value = selectedOption.getAttribute('data-shopname') || '';
                document.getElementById('city').value = selectedOption.getAttribute('data-city') || '';
                document.getElementById('area').value = selectedOption.getAttribute('data-area') || '';
                document.getElementById('address').value = selectedOption.getAttribute('data-address') || '';
                document.getElementById('phone').value = selectedOption.getAttribute('data-phone') || '';
            }

            customerSelect.addEventListener('change', updateCustomerDetails);
            // Trigger on page load to set initial values if a customer is already selected
            updateCustomerDetails();
        }
    });

    // Function to populate subcategories based on category selection
    // Function to populate subcategories based on category selection
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
                    // Trigger change on subcategory dropdown to populate items for this row
                    // Pass selectedItemName to the subcategory change handler
                    row.find('.subcategory-select').trigger('change', [selectedItemName]);
                },
                error: function() {
                    alert('Error fetching subcategories.');
                }
            });
        } else {
            // Clear all dependent fields if no category is selected
            subCategoryDropdown.html('<option value="">Select Sub Category</option>');
            row.find('.item-select').html('<option value="">Select Item</option>');
            row.find('.code, .size, .pcs-carton, .liter, .amount').val('');
            row.find('.carton-qty, .pcx, .rate, .discount').val('0');
            calculateGrandTotalFromRows(); // Recalculate grand total after clearing a row
        }
    }

    // Function to calculate the total amount from all visible rows
    function calculateGrandTotalFromRows() {
        let grandTotal = 0;
        $(".amount").each(function() {
            let val = parseFloat($(this).val());
            if (!isNaN(val)) { // Ensure it's a valid number
                grandTotal += val;
            }
        });
        $("#grandTotal").val(grandTotal.toFixed(2));
        calculateNetAmount(); // Update net amount whenever grand total changes
    }

    // Function to calculate net amount based on grand total, discount, and scheme
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

    // Event listeners for changes in discount and scheme fields
    $('#discountValue, #discountType, #schemeValue').on('input change', function() {
        calculateNetAmount();
    });

    // --- Core Fix for Existing Rows Initialization ---
    $(document).ready(function() {
        // Store original item and subcategory values as they are rendered by PHP
        // This is crucial for pre-selecting options in dynamically loaded dropdowns
        const originalSubcategoriesData = @json($subcategories);
        const originalItemsData = @json($items);

        // Iterate over each pre-existing row in the table
        $('#purchaseTable tbody tr').each(function(index) {
            let row = $(this);
            let categorySelect = row.find('.category-select');
            let selectedCategory = categorySelect.val();

            // Use the stored original data for this specific row
            let originalSubcategory = originalSubcategoriesData[index] || null;
            let originalItem = originalItemsData[index] || null;

            // Populate subcategories and then items, passing the original item name
            // This ensures the item dropdown gets its options and the correct one is selected.
            if (selectedCategory) {
                // Pass categorySelect[0] as it expects a DOM element
                populateSubcategories(categorySelect[0], originalSubcategory, originalItem);
            }

            // Re-calculate the row's amount after all data (including AJAX fetched data like rate, pcs_carton)
            // is expected to be loaded. The delay is crucial.
            setTimeout(function() {
                recalcRowAmount(row);
            }, 200 * (index + 1)); // Stagger the recalculations slightly for multiple rows
        });

        // Initial calculation after all asynchronous operations are likely done
        $(window).on('load', function() {
            calculateGrandTotalFromRows();
            calculateNetAmount();
        });
    });


    // Add new row event
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
                    <select class="form-control subcategory-select" name="subcategory[]">
                        <option value="">Select Subcategory</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control code" name="code[]" style="width: 130px;" readonly>
                </td>
                <td>
                    <select class="form-control item-select" name="item[]" style="width: 400px;">
                        <option value="">Select Item</option>
                    </select>
                </td>
                <td><input type="text" class="form-control size" name="size[]" readonly></td>
                <td><input type="number" class="form-control pcs-carton" name="pcs_carton[]" style="width: 180px;" value="0" readonly></td>
                <td><input type="number" class="form-control carton-qty" name="carton_qty[]" style="width: 180px;" value="0"></td>
                <td><input type="number" class="form-control pcx" name="pcs[]" style="width: 180px;" value="0"></td>
                <td><input type="number" class="form-control liter" name="liter[]" step="any" style="width: 180px;" value="0"></td>
                <td><input type="number" class="form-control rate" name="rate[]" style="width: 180px;" value="0"></td>
                <td><input type="number" class="form-control discount" name="discount[]" style="width: 180px;" value="0"></td>
                <td><input type="number" class="form-control amount" name="amount[]" readonly value="0"></td>
                <td><button type="button" class="btn btn-danger remove-row">Delete</button></td>
            </tr>`;

        $("#purchaseTable tbody").append(newRowHtml);
    });

    // Event listener for category changes (for both existing and new rows)
    $(document).on('change', '.category-select', function() {
        populateSubcategories(this);
    });

    // Event listener for subcategory changes (for both existing and new rows)
    $(document).on('change', '.subcategory-select', function(event, selectedItemFromPHP = null) {
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
            url: "{{ route('get.items') }}", // Ensure this route is correctly defined in web.php to handle the query parameters
            type: 'GET',
            data: {
                category_name: categoryName,
                sub_category_name: subCategoryName
            },
            success: function(response) {
                itemDropdown.html('<option value="">Select Item</option>');
                $.each(response, function(index, item) {
                    // Populate options with data attributes
                    itemDropdown.append(`<option value="${item.item_name}" data-pcs="${item.pcs_in_carton}" data-code="${item.item_code}" data-size="${item.size}" data-rp="${item.retail_price}">${item.item_name}</option>`);
                });

                // If selectedItemFromPHP is provided (from initial load), select it
                if (selectedItemFromPHP && itemDropdown.find(`option[value="${selectedItemFromPHP}"]`).length) {
                    itemDropdown.val(selectedItemFromPHP);
                }

                // Trigger item-select change to populate other fields (code, size, pcs-carton, rate)
                // This is crucial for both initial load and manual selection
                itemDropdown.trigger('change');
            },
            error: function(xhr, status, error) {
                console.error("Error fetching items:", error);
                alert('Error fetching items. Check console for details.');
            }
        });
    });

    // Event listener for item selection changes
    $(document).on('change', '.item-select', function() {
        let selectedOption = $(this).find(":selected");
        let row = $(this).closest('tr');

        // Update fields based on selected item's data attributes
        row.find('.pcs-carton').val(selectedOption.data('pcs') || 0);
        row.find('.rate').val(selectedOption.data('rp') || 0);
        row.find('.code').val(selectedOption.data('code') || '');
        row.find('.size').val(selectedOption.data('size') || '');

        // Now, trigger the recalculation for this row
        recalcRowAmount(row);
    });

    // Unified input event for all calculation-affecting fields within a row
    $(document).on('input', '.carton-qty, .pcx, .rate, .discount, .pcs-carton', function() {
        let row = $(this).closest('tr');
        recalcRowAmount(row); // Recalculate only the current row's amount
    });

    // Function to recalculate amount for a single row
    function recalcRowAmount(row) {
        let cartonQty = parseFloat(row.find('.carton-qty').val()) || 0;
        let packing = parseFloat(row.find('.pcs-carton').val()) || 0; // pcs per carton
        let pcsQty = parseFloat(row.find('.pcx').val()) || 0;
        let rate = parseFloat(row.find('.rate').val()) || 0; // Rate is per carton
        let discount = parseFloat(row.find('.discount').val()) || 0;
        let sizeText = row.find('.size').val().toLowerCase().trim();
        let measurement = 0;

        // Determine measurement (liters) from size text
        if (sizeText.includes('ml')) {
            measurement = parseFloat(sizeText.replace(/[^0-9.]/g, '')) / 1000;
        } else if (sizeText.includes('l')) {
            measurement = parseFloat(sizeText.replace(/[^0-9.]/g, ''));
        } else {
            measurement = parseFloat(sizeText) || 0; // Fallback for other formats, assuming it's already in liters or 0 if not parsable
        }

        // Calculate total liters
        let litersFromCartons = cartonQty * packing * measurement;
        let litersFromPcs = pcsQty * measurement;
        let totalLiters = litersFromCartons + litersFromPcs;
        row.find('.liter').val(parseFloat(totalLiters.toFixed(2)).toString());

        // Calculate total amount for the row
        let cartonAmount = rate * cartonQty;
        let perPieceRate = (packing > 0) ? (rate / packing) : 0; // Avoid division by zero
        let pcsAmount = perPieceRate * pcsQty;

        let totalBeforeDiscount = cartonAmount + pcsAmount;
        let finalAmount = totalBeforeDiscount - discount;

        if (finalAmount < 0) finalAmount = 0; // Ensure amount doesn't go negative

        row.find('.amount').val(parseFloat(finalAmount.toFixed(2)).toString());

        calculateGrandTotalFromRows(); // Always recalculate grand total after a row's amount changes
    }

    // Remove row functionality
    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
        calculateGrandTotalFromRows(); // Recalculate grand total after removing a row
    });

    // Ensure grand total and net amount are calculated once everything is loaded and processed.
    // This is a fallback and good practice, though the individual row calculations should keep it updated.
    $(window).on('load', function() {
        calculateGrandTotalFromRows();
        calculateNetAmount();
    });
</script>
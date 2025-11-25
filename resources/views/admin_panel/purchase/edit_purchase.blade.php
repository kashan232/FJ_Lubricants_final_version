@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Edit Purchase Management</h4>
                    <h6>Manage Edit Purchase Efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif
                    <form action="{{ route('update-Purchase', $purchase->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Purchase Date</label>
                                <input type="date" class="form-control" name="purchase_date" value="{{ $purchase->purchase_date }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Party Code</label>
                                <input type="text" class="form-control party_code" name="party_code" value="{{ $purchase->party_code }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Party Name</label>
                                <select name="party_name" id="party_name" class="form-control vendor-select">
                                    <option value="" disabled>Choose One</option>
                                    @foreach($Vendors as $Vendor)
                                    <option value="{{ $Vendor->id }}" data-code="{{ $Vendor->Party_code }}" {{ $purchase->party_name == $Vendor->id ? 'selected' : '' }}>{{ $Vendor->Party_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center" id="purchaseTable">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Sub Category</th>
                                        <th>Item</th>
                                        <th>Measurement</th>
                                        <th>Pcs/Carton</th>
                                        <th>Rate (Per Carton)</th>
                                        <th>Carton Qty</th>
                                        <th>Pcs</th>
                                        <th>Liter</th>
                                        <th>Gross Total</th>
                                        <th>Discount</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(is_array(json_decode($purchase->category)) && count(json_decode($purchase->category)) > 0)
                                    @foreach(json_decode($purchase->category) as $key => $category)
                                    <tr>
                                        <td>
                                            <select class="form-control form-control-lg category-select" name="category[]" style="width: 150px;">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $cat)
                                                <option value="{{ $cat->category_name }}" {{ json_decode($purchase->category)[$key] == $cat->category_name ? 'selected' : '' }}>{{ $cat->category_name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control form-control-lg subcategory-select" name="subcategory[]" style="width: 150px;">
                                                <option value="">Select Subcategory</option>
                                                @if(isset(json_decode($purchase->subcategory)[$key]))
                                                <option value="{{ json_decode($purchase->subcategory)[$key] }}" selected>{{ json_decode($purchase->subcategory)[$key] }}</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control form-control-lg item-select" name="item[]" style="width: 180px;">
                                                <option value="">Select Item</option>
                                                @if(isset(json_decode($purchase->item)[$key]))
                                                <option value="{{ json_decode($purchase->item)[$key] }}" data-size="{{ json_decode($purchase->size)[$key] ?? '' }}" data-pcs="{{ json_decode($purchase->pcs_carton)[$key] ?? '' }}" selected>{{ json_decode($purchase->item)[$key] }}</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control form-control-lg size" name="size[]" style="width: 100px;" value="{{ json_decode($purchase->size)[$key] ?? '' }}" readonly></td>
                                        <td><input type="number" class="form-control form-control-lg pcs-carton" name="pcs_carton[]" style="width: 100px;" value="{{ json_decode($purchase->pcs_carton)[$key] ?? '' }}" readonly></td>
                                        <td><input type="number" class="form-control form-control-lg rate" name="rate[]" style="width: 100px;" value="{{ json_decode($purchase->rate)[$key] ?? '' }}"></td>
                                        <td><input type="number" class="form-control form-control-lg carton-qty" name="carton_qty[]" style="width: 100px;" value="{{ json_decode($purchase->carton_qty)[$key] ?? '' }}"></td>
                                        <td><input type="number" class="form-control form-control-lg pcx" name="pcs[]" style="width: 100px;" value="{{ json_decode($purchase->pcs)[$key] ?? '' }}"></td>
                                        <td><input type="number" class="form-control form-control-lg liter" name="liter[]" step="any" style="width: 100px;" value="{{ json_decode($purchase->liter)[$key] ?? '' }}"></td>
                                        <td><input type="number" class="form-control form-control-lg gross-total" name="gross_total[]" style="width: 100px;" value="{{ json_decode($purchase->gross_total)[$key] ?? '' }}" readonly></td>
                                        <td><input type="number" class="form-control form-control-lg discount" name="discount[]" style="width: 100px;" value="{{ json_decode($purchase->discount)[$key] ?? '' }}"></td>
                                        <td><input type="number" class="form-control form-control-lg amount" name="amount[]" style="width: 100px;" value="{{ json_decode($purchase->amount)[$key] ?? '' }}" readonly></td>
                                        <td>
                                            @if($key > 0)
                                            <button type="button" class="btn btn-danger remove-row">Delete</button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    @else
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
                                                <option value="">Select Subcategory</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control form-control-lg item-select" name="item[]" style="width: 180px;">
                                                <option value="">Select Item</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control form-control-lg size" name="size[]" style="width: 100px;" readonly></td>
                                        <td><input type="number" class="form-control form-control-lg pcs-carton" name="pcs_carton[]" style="width: 100px;" readonly></td>
                                        <td><input type="number" class="form-control form-control-lg rate" name="rate[]" style="width: 100px;"></td>
                                        <td><input type="number" class="form-control form-control-lg carton-qty" name="carton_qty[]" style="width: 100px;"></td>
                                        <td><input type="number" class="form-control form-control-lg pcx" name="pcs[]" style="width: 100px;"></td>
                                        <td><input type="number" class="form-control form-control-lg liter" name="liter[]" step="any" style="width: 100px;"></td>
                                        <td><input type="number" class="form-control form-control-lg gross-total" name="gross_total[]" style="width: 100px;" readonly></td>
                                        <td><input type="number" class="form-control form-control-lg discount" name="discount[]" style="width: 100px;"></td>
                                        <td><input type="number" class="form-control form-control-lg amount" name="amount[]" style="width: 100px;" readonly></td>
                                        <td></td>
                                    </tr>
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-end fw-bold">Grand Total:</td>
                                        <td colspan="2"><input type="number" class="form-control form-control-lg fw-bold text-center" id="grandTotal" name="grand_total" value="{{ $purchase->grand_total }}" readonly></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <button type="button" class="btn btn-success mt-3" id="addRow">Add More</button>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')
<script>
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
                <option value="">Select Subcategory</option>
            </select>
        </td>
        <td>
            <select class="form-control form-control-lg item-select" name="item[]" style="width: 180px;">
                <option value="">Select Item</option>
            </select>
        </td>
        <td><input type="text" class="form-control form-control-lg size" name="size[]" style="width: 100px;" readonly></td>
        <td><input type="number" class="form-control form-control-lg pcs-carton" name="pcs_carton[]" style="width: 100px;" readonly></td>
        <td><input type="number" class="form-control form-control-lg rate" name="rate[]" style="width: 100px;"></td>
        <td><input type="number" class="form-control form-control-lg carton-qty" name="carton_qty[]" style="width: 100px;"></td>
        <td><input type="number" class="form-control form-control-lg pcx" name="pcs[]" style="width: 100px;"></td>
        <td><input type="number" class="form-control form-control-lg liter" name="liter[]" step="any" style="width: 100px;"></td>
        <td><input type="number" class="form-control form-control-lg gross-total" name="gross_total[]" style="width: 100px;" readonly></td>
        <td><input type="number" class="form-control form-control-lg discount" name="discount[]" style="width: 100px;"></td>
        <td><input type="number" class="form-control form-control-lg amount" name="amount[]" style="width: 100px;" readonly></td>
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
            let itemDropdown = $(this).closest('tr').find('.item-select');
            itemDropdown.html('<option value="">Select Item</option>');

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
                            itemDropdown.append(`<option value="${item.item_name}" data-size="${item.size}" data-pcs="${item.pcs_in_carton}">${item.item_name}</option>`);
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

            let sizeValue = $(this).find(":selected").data('size') || '';
            $(this).closest('tr').find('.size').prop('readonly', false).val(sizeValue).prop('readonly', true);
        });

        $(document).on('change', '.vendor-select', function() {
            let partycode = $(this).find(":selected").data('code') || 0;
            $(".party_code").val(partycode);
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

            // ✅ **Liter Calculation (Same as Sale)**
            let litersFromCartons = cartonQty * packing * measurement;
            let litersFromPcs = pcsQty * measurement;
            let totalLiters = litersFromCartons + litersFromPcs;

            row.find('.liter').val(parseFloat(totalLiters.toFixed(2)).toString());

            // ✅ **Carton Amount Calculation**
            let cartonAmount = rate * cartonQty;

            // ✅ **Per Piece Rate Calculation**
            let perPieceRate = (packing > 0) ? (rate / packing) : 0;

            // ✅ **Pcs Amount Calculation**
            let pcsAmount = perPieceRate * pcsQty;

            // ✅ **Total Before Discount**
            let totalBeforeDiscount = cartonAmount + pcsAmount;

            // ✅ **Final Amount After Applying Discount**
            let finalAmount = totalBeforeDiscount - discount;

            row.find('.amount').val(parseFloat(finalAmount.toFixed(2)).toString());

            // ✅ **Recalculate Grand Total**
            calculateGrandTotal();
        });

        // ✅ **Calculate Grand Total (Same as Sale)**
        function calculateGrandTotal() {
            let grandTotal = 0;
            $(".amount").each(function() {
                grandTotal += parseFloat($(this).val()) || 0;
            });

            $("#grandTotal").val(parseFloat(grandTotal.toFixed(2)).toString());
        }



    });
</script>

@include('admin_panel.include.header_include')
@php
$isAdmin = auth()->user()->usertype === 'admin';
$isDistributor = auth()->user()->usertype === 'distributor';
@endphp
<style>
    table {
        font-size: 13px;
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        text-align: center;
        vertical-align: middle;
        border: 1px solid #dee2e6;
        /* Border for all cells */
    }

    th.sub-group-heading {
        background-color: #0088fb;
        color: #fff !important;
        font-weight: bold;
    }

    th.sub-heading {
        background-color: #f8f9fa;
        color: #212529;
        font-weight: bold;
    }

    tbody td {
        font-weight: 500;
        border: 1px solid #000;
    }

    .table tbody tr td {
        padding: 10px;
        color: #637381;
        font-weight: 500;
        border: 1px solid #000;
        vertical-align: middle;
        white-space: nowrap;
    }

    tbody tr:hover {
        background-color: #f1f1f1;
        /* Hover effect for rows */
    }

    tfoot td {
        font-weight: bold;
        border: 1px solid #000;

    }
</style>
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h2 class="card-title text-center fw-bold mb-4 text-primary">Item Stock Report</h2>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="fw-bold">Category</label>
                            <select class="form-control category-select">
                                <option value="all">All</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->category_name }}">{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold">Subcategory</label>
                            <select class="form-control subcategory-select">
                                <option value="all">All</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold">Item</label>
                            <select class="form-control item-select">
                                <option value="all">All</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary w-100 search-item">Search</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered mt-4" id="stockReport" style="border: 1px solid #dee2e6;">
                            <thead>
                                <tr>
                                    <th rowspan="2">Code</th>
                                    <th rowspan="2">Name</th>

                                    {{-- DISTRIBUTOR DETAILS --}}
                                    @if($isDistributor)
                                    <th colspan="2" class="sub-group-heading">Details</th>
                                    @endif

                                    {{-- ADMIN OPENING --}}
                                    @if($isAdmin)
                                    <th colspan="3" class="sub-group-heading">Opening</th>
                                    @endif

                                    {{-- PURCHASED --}}
                                    <th colspan="2" class="sub-group-heading">
                                        {{ $isDistributor ? 'Purchased (Admin Sale)' : 'Purchased' }}
                                    </th>

                                    {{-- SALE --}}
                                    <th colspan="{{ $isAdmin ? 4 : 2 }}" class="sub-group-heading">Sale</th>

                                    {{-- BALANCE --}}
                                    <th colspan="2" class="sub-group-heading">Balance</th>

                                    {{-- BALANCE AMOUNT --}}
                                    <th colspan="3" class="sub-group-heading">Balance Amount</th>
                                </tr>

                                <tr>
                                    {{-- DETAILS --}}
                                    @if($isDistributor)
                                    <th class="sub-heading">Size</th>
                                    <th class="sub-heading">Packing</th>
                                    @endif

                                    {{-- OPENING --}}
                                    @if($isAdmin)
                                    <th class="sub-heading">Size</th>
                                    <th class="sub-heading">Packing</th>
                                    <th class="sub-heading">Qty</th>
                                    @endif

                                    {{-- PURCHASED --}}
                                    @if($isDistributor)
                                    <th class="sub-heading">Ctn</th>
                                    <th class="sub-heading">Pcs</th>
                                    @else
                                    <th class="sub-heading">Qty</th>
                                    <th class="sub-heading">Returned Qty</th>
                                    @endif

                                    {{-- SALE --}}
                                    @if($isDistributor)
                                    <th class="sub-heading">Ctn</th>
                                    <th class="sub-heading">Pcs</th>
                                    @else
                                    <th class="sub-heading">Sold Qty</th>
                                    <th class="sub-heading">Return Qty</th>
                                    <th class="sub-heading">Local Qty</th>
                                    <th class="sub-heading">Local Return Qty</th>
                                    @endif

                                    {{-- BALANCE --}}
                                    <th class="sub-heading">Ctn</th>
                                    <th class="sub-heading">Litre</th>

                                    {{-- BALANCE AMOUNT --}}
                                    <th class="sub-heading">{{ $isAdmin ? 'W.Price' : 'Retail Price' }}</th>
                                    <th class="sub-heading">Pcs</th>
                                    <th class="sub-heading">Stock Value</th>
                                </tr>
                            </thead>



                            <tbody id="item-details" style="border: 1px solid #dee2e6;"></tbody>

                            <tfoot>
                            <tr>
                                @if($isDistributor)
                                    <td colspan="12" class="text-end fw-bold">Total Stock Value:</td>
                                    <td class="fw-bold" id="subtotalStockValue">0.00</td>
                                @else
                                    <td colspan="14" class="text-end fw-bold">Total Stock Value:</td>
                                    <td class="fw-bold" id="subtotalStockValue">0.00</td>
                                @endif
                            </tr>
                            </tfoot>
                        </table>
                    </div>



                    <button class="btn btn-danger mt-3" id="exportPdf">Export PDF</button>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>

<script>
    // Fetch Subcategories on Category Change
    $(document).on('change', '.category-select', function() {
        let categoryName = $(this).val();
        let subCategoryDropdown = $('.subcategory-select');

        if (categoryName !== "all") {
            $.ajax({
                url: "{{ route('get.subcategories', ':categoryname') }}".replace(':categoryname', categoryName),
                type: 'GET',
                success: function(response) {
                    subCategoryDropdown.html('<option value="all">All</option>');
                    $.each(response, function(index, name) {
                        subCategoryDropdown.append(`<option value="${name}">${name}</option>`);
                    });
                }
            });
        } else {
            subCategoryDropdown.html('<option value="all">All</option>');
        }
    });

    // Fetch Items on Subcategory Change
    $(document).on('change', '.subcategory-select', function() {
        let subCategoryName = $(this).val();
        let itemDropdown = $('.item-select');

        if (subCategoryName !== "all") {
            $.ajax({
                url: "{{ route('get.items.report', ':subcategory') }}".replace(':subcategory', subCategoryName),
                type: 'GET',
                success: function(response) {
                    itemDropdown.html('<option value="all">All</option>');
                    $.each(response, function(index, item) {
                        itemDropdown.append(`<option value="${item.item_code}">${item.item_name}</option>`);
                    });
                }
            });
        } else {
            itemDropdown.html('<option value="all">All</option>');
        }
    });

    function calculateSubtotal() {
        let totalStockValue = 0;
        $(".total-stock-value").each(function() {
            totalStockValue += parseFloat($(this).text()) || 0;
        });
        $("#subtotalStockValue").text(totalStockValue.toFixed(2));
    }
    $(document).on('click', '.search-item', function() {
        let category = $('.category-select').val();
        let subcategory = $('.subcategory-select').val();
        let itemCode = $('.item-select').val();
        let url = "{{ route('get.item.details') }}";

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                category,
                subcategory,
                itemCode
            },
            success: function(response) {
                console.log(response);

                let tableContent = '';
                let totalStockValue = 0;
                let totalPurchased = 0;
                let totalPurchaseReturn = 0; // 🔁 NEW
                let totalDistributorSold = 0;
                let totalLocalSale = 0;
                let totalCartonQty = 0;
                let openingcarton = 0;
                let totalLiters = 0;
                let totalStock = 0;
                let totalDistributorReturn = 0;
                let totalLocalReturn = 0;
                $.each(response, function(index, item) {
                    let stockValue = 0;

                    if (@json($isDistributor)) {
                        stockValue = (item.balance_carton ?? 0) * (item.retail_price ?? item.price ?? 0);
                    } else {
                        stockValue = (item.carton_quantity ?? 0) * (item.wholesale_price ?? 0);
                    }
                    totalStockValue += stockValue;

                    let sizeValue = 0;
                    let sizeText = (item.size || "").toLowerCase().trim();

                    // ✅ Enhanced Size Calculation Logic
                    if (sizeText.includes('ml')) {
                        sizeValue = parseFloat(sizeText.replace(/[^0-9.]/g, '')) / 1000; // Convert ml to liters
                    } else if (sizeText.includes('liter') || sizeText.includes('l')) {
                        sizeValue = parseFloat(sizeText.replace(/[^0-9.]/g, ''));
                    } else {
                        sizeValue = parseFloat(sizeText) || 0;
                    }

                    // ✅ Liter Calculation (including pieces in carton and carton quantity)
                    let liters = sizeValue * item.pcs_in_carton * item.carton_quantity;

                    totalPurchased += parseFloat(item.total_purchased) || 0;
                    totalPurchaseReturn += parseFloat(item.total_purchase_return) || 0; // 🔁 NEW
                    totalDistributorSold += parseFloat(item.total_distributor_sold) || 0;
                    totalLocalSale += parseFloat(item.total_local_sold) || 0;
                    totalCartonQty += parseFloat(item.carton_quantity) || 0;
                    openingcarton += parseFloat(item.opening_carton_quantity) || 0;
                    totalLiters += liters;
                    totalStock += parseFloat(item.initial_stock) || 0;
                    totalDistributorReturn += parseFloat(item.total_distributor_return) || 0;
                    totalLocalReturn += parseFloat(item.total_local_return) || 0;
                    let formattedLiters = liters % 1 === 0 ? liters.toFixed(0) : liters.toFixed(2);

                    tableContent += `<tr>
    <td>${item.item_code ?? ''}</td>
    <td>${item.item_name ?? item.item}</td>

    ${@json($isDistributor) ? `
        <!-- DETAILS (Distributor only) -->
        <td>${item.size ?? ''}</td>
        <td>${item.pcs_in_carton ?? ''}</td>
    ` : ''}

    ${@json($isAdmin) ? `
        <!-- OPENING (Admin only) -->
        <td>${item.size ?? ''}</td>
        <td>${item.pcs_in_carton ?? ''}</td>
        <td>${item.opening_carton_quantity ?? 0}</td>
    ` : ''}

    <!-- PURCHASED -->
    ${@json($isDistributor) ? `
        <td>${item.purchased_carton ?? 0}</td>
        <td>${item.purchased_pcs ?? 0}</td>
    ` : `
        <td>${item.total_purchased ?? 0}</td>
        <td>${item.total_purchase_return ?? 0}</td>
    `}

    <!-- SALE -->
    ${@json($isDistributor) ? `
        <td>${item.sold_carton ?? 0}</td>
        <td>${item.sold_pcs ?? 0}</td>
    ` : `
        <td>${item.total_distributor_sold ?? 0}</td>
        <td>${item.total_distributor_return ?? 0}</td>
        <td>${item.total_local_sold ?? 0}</td>
        <td>${item.total_local_return ?? 0}</td>
    `}

    <!-- BALANCE -->
    <td>${item.balance_carton ?? item.carton_quantity ?? 0}</td>
    <td>${formattedLiters}</td>

    <!-- BALANCE AMOUNT -->
    <td>${@json($isAdmin) ? item.wholesale_price : item.price}</td>
    <td>${item.balance_pcs ?? item.initial_stock ?? 0}</td>
    <td class="total-stock-value">${stockValue.toFixed(2)}</td>
</tr>`;

                });

                // ✅ Footer Update:
                let formattedTotalLiters = totalLiters % 1 === 0 ? totalLiters.toFixed(0) : totalLiters.toFixed(2);
                let footerContent = '';
                if (@json($isDistributor)) {

                // ✅ DISTRIBUTOR FOOTER (13 columns EXACT)
                footerContent = `
                <tr>
                    <td colspan="8" class="text-end fw-bold">Total:</td>
                    <td class="fw-bold">${totalCartonQty}</td>
                    <td class="fw-bold">${formattedTotalLiters}</td>
                    <td></td>
                    <td></td>
                    <td class="fw-bold">${totalStockValue.toFixed(2)}</td>
                </tr>`;

            } else {

                // ✅ ADMIN FOOTER (15 columns)
                footerContent = `
                <tr>
                    <td colspan="5" class="text-end fw-bold">Total:</td>
                    <td class="fw-bold">${totalPurchased}</td>
                    <td class="fw-bold">${totalPurchaseReturn}</td>
                    <td class="fw-bold">${totalDistributorSold}</td>
                    <td class="fw-bold">${totalDistributorReturn}</td>
                    <td class="fw-bold">${totalLocalSale}</td>
                    <td class="fw-bold">${totalLocalReturn}</td>
                    <td class="fw-bold">${totalCartonQty}</td>
                    <td class="fw-bold">${formattedTotalLiters}</td>
                    <td></td>
                    <td class="fw-bold">${totalStock}</td>
                    <td class="fw-bold">${totalStockValue.toFixed(2)}</td>
                </tr>`;
            }

                $('#item-details').html(tableContent);
                $('#stockReport tfoot').html(footerContent);
            }
        });
    });




    $(document).on('click', '#exportPdf', function() {
        const {
            jsPDF
        } = window.jspdf;
        let pdf = new jsPDF('l', 'pt', 'a4'); // Landscape mode

        let pageWidth = pdf.internal.pageSize.width;
        let title = "Item Stock Report";
        let textWidth = pdf.getTextWidth(title);
        let titleX = (pageWidth - textWidth) / 2; // Center title

        // ✅ Add Logo at Center
        let logoUrl = "{{ url('logo.jpeg') }}"; // Logo URL
        let logoWidth = 100; // Adjust width as needed
        let logoHeight = 30; // Adjust height as needed
        let logoX = (pageWidth - logoWidth) / 2; // Center logo

        let img = new Image();
        img.src = logoUrl;
        img.onload = function() {
            pdf.addImage(img, 'JPEG', logoX, 10, logoWidth, logoHeight); // Logo position

            // ✅ Add Title Below Logo
            pdf.setFontSize(16);
            pdf.text(title, titleX, 80); // Adjust Y position (below logo)

            // ✅ Add Table
            pdf.autoTable({
                html: '#stockReport',
                theme: 'grid',
                startY: 100, // Move table down (after logo + title)
                styles: {
                    fontSize: 8,
                    cellPadding: 4
                },
                headStyles: {
                    fillColor: [41, 128, 185] // Blue header
                }
            });

            // ✅ Save PDF
            pdf.save("Item_Stock_Report.pdf");
        };
    });
</script>
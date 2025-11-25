@include('admin_panel.include.header_include')
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
                            <thead class="bg-gray text-white">
                                <tr>
                                    <th rowspan="2">Code</th>
                                    <th rowspan="2">Name</th>
                                    <th colspan="3" class="text-center sub-group-heading">Opening</th>
                                    <th colspan="2" class="text-center sub-group-heading">Purchased</th>
                                    <th colspan="4" class="text-center sub-group-heading">Sale</th>
                                    <th colspan="2" class="text-center sub-group-heading">Balance</th>
                                    <th colspan="3" class="text-center sub-group-heading">Balance Amount</th>
                                </tr>
                                <tr>
                                    <th class="sub-heading">Size</th>
                                    <th class="sub-heading">Packing</th>
                                    <th class="sub-heading">Qty</th>
                                    <th class="sub-heading">Qty</th>
                                    <th class="sub-heading">Returned Qty</th>
                                    <th class="sub-heading">Sold Qty</th>
                                    <th class="sub-heading">Return Qty</th>
                                    <th class="sub-heading">Local Qty</th>
                                    <th class="sub-heading">Local Return Qty</th>
                                    <th class="sub-heading">Ctn</th>
                                    <th class="sub-heading">Litre</th>
                                    <th class="sub-heading">W.Price</th>
                                    <th class="sub-heading">Pcs</th>
                                    <th class="sub-heading">Stock Value</th>
                                </tr>
                            </thead>

                            <tbody id="item-details" style="border: 1px solid #dee2e6;"></tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="12" class="text-end fw-bold">Total Stock Value:</td>
                                    <td class="fw-bold" id="subtotalStockValue">0.00</td>
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
                    let stockValue = item.carton_quantity * item.wholesale_price;
                    totalStockValue += stockValue;

                    let sizeValue = 0;
                    let sizeText = item.size.toLowerCase().trim();

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
        <td>${item.item_code}</td>
        <td>${item.item_name}</td>
        <td>${item.size}</td>
        <td>${item.pcs_in_carton}</td>
        <td>${item.opening_carton_quantity}</td>
        <td>${item.total_purchased ?? 'N/A'}</td>
        <td>${item.total_purchase_return ?? 'N/A'}</td>
        <td>${item.total_distributor_sold ?? 'N/A'}</td>
        <td>${item.total_distributor_return ?? 'N/A'}</td>
        <td>${item.total_local_sold ?? 'N/A'}</td>
        <td>${item.total_local_return ?? 'N/A'}</td>
        <td>${item.carton_quantity}</td>
        <td>${formattedLiters}</td>
        <td>${item.wholesale_price}</td>
        <td>${item.initial_stock}</td>
        <td class="total-stock-value">${stockValue.toFixed(2)}</td>
    </tr>`;
                });

                // ✅ Footer Update:
                let formattedTotalLiters = totalLiters % 1 === 0 ? totalLiters.toFixed(0) : totalLiters.toFixed(2);

                let footerContent = `
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
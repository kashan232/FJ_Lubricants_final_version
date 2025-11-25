@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Product List</h4>
                    <h6>Manage Products</h6>
                </div>
                <div class="page-btn">
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <img src="assets/img/icons/plus.svg" class="me-1" alt="img">Add Product
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Sub-Category</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Opening stock Carton</th>
                                    <th>Size</th>
                                    <th>pcs_in_carton</th>
                                    <th>Purchase Price</th>
                                    <th>Sale Price</th>
                                    <th>Carton Qnty</th>
                                    <th>Initial Stock</th>
                                    <th>Alert Quantity</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $key => $product)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $product->category }}</td>
                                    <td>{{ $product->sub_category }}</td>
                                    <td>{{ $product->item_code }}</td>
                                    <td>{{ $product->item_name }}</td>
                                    <td>{{ $product->opening_carton_quantity }}</td>
                                    <td>{{ $product->size }}</td>
                                    <td>{{ $product->pcs_in_carton }}</td>
                                    <td>{{ $product->wholesale_price }}</td>
                                    <td>{{ $product->retail_price }}</td>
                                    <td>{{ $product->carton_quantity }}</td>
                                    <td>{{ $product->initial_stock }}</td>
                                    <td>{{ $product->alert_quantity }}</td>
                                    <td>
                                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary text-white">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- add product modal --}}

<div class="modal fade bd-example-modal-lg" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('store-product') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-control" name="category" id="categorySelect" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->category_name }}">{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sub-Category</label>
                            <select class="form-control" name="sub_category" id="subCategorySelect" required>
                                <option value="">Select Sub-Category</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" name="item_name" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Size</label>
                            <select class="form-control" name="size" id="sizeSelect" required>
                                <option value="">Select Size</option>
                                @foreach ($sizes as $size)
                                <option value="{{ $size->size_name }}">{{ $size->size_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Carton Quantity</label>
                            <input type="number" class="form-control" name="carton_quantity" id="carton_quantity" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pieces per Carton</label>
                            <input type="number" class="form-control" name="pcs_in_carton" id="pieces_per_carton" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Loose Pieces</label>
                            <input type="number" class="form-control" name="loose_pieces" id="loose_pieces" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Initial Stock</label>
                            <input type="number" class="form-control" name="initial_stock" id="initial_stock">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Alert Quantity</label>
                            <input type="number" class="form-control" name="alert_quantity" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purchase Price</label>
                            <input type="number" step="0.01" class="form-control" name="wholesale_price" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sale Price</label>
                            <input type="number" step="0.01" class="form-control" name="retail_price" required>
                        </div>
                    </div>


                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


@include('admin_panel.include.footer_include')

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let cartonQuantityInput = document.getElementById("carton_quantity");
        let piecesPerCartonInput = document.getElementById("pieces_per_carton");
        let initialStockInput = document.getElementById("initial_stock");
        let loosePiecesInput = document.getElementById("loose_pieces");

        function updateInitialStock() {
            let cartonQuantity = parseInt(cartonQuantityInput.value) || 0;
            let piecesPerCarton = parseInt(piecesPerCartonInput.value) || 0;
            let loosePieces = parseInt(loosePiecesInput.value) || 0;

            let initialStock = (cartonQuantity * piecesPerCarton) + loosePieces;
            initialStockInput.value = initialStock;
        }

        cartonQuantityInput.addEventListener("input", updateInitialStock);
        piecesPerCartonInput.addEventListener("input", updateInitialStock);

        // Update loose pieces dynamically
        loosePiecesInput.addEventListener("input", function() {
            let loosePieces = parseInt(this.value) || 0;
            let cartonQuantity = parseInt(cartonQuantityInput.value) || 0;
            let piecesPerCarton = parseInt(piecesPerCartonInput.value) || 0;
            let initialStock = (cartonQuantity * piecesPerCarton) + loosePieces;
            initialStockInput.value = initialStock;
        });
    });

    $(document).ready(function() {
        // Add Product Modal: Fetch Subcategories on Category Change
        $('#categorySelect').change(function() {
            var categoryId = $(this).val();
            $('#subCategorySelect').html('<option value="">Loading...</option>');

            if (categoryId) {
                $.ajax({
                    url: "{{ route('fetch-subcategories') }}",
                    type: "GET",
                    data: {
                        category_id: categoryId
                    },
                    success: function(data) {
                        $('#subCategorySelect').html('<option value="">Select Sub-Category</option>');
                        $.each(data, function(key, subCategory) {
                            $('#subCategorySelect').append('<option value="' + subCategory.sub_category_name + '">' + subCategory.sub_category_name + '</option>');
                        });
                    },
                    error: function() {
                        alert('Error fetching subcategories.');
                    }
                });
            } else {
                $('#subCategorySelect').html('<option value="">Select Sub-Category</option>');
            }
        });

        // Edit Product Modal: Fetch Subcategories when Category is Changed
        $('#edit_category').change(function() {
            var categoryId = $(this).val();
            $('#edit_sub_category').html('<option value="">Loading...</option>');

            if (categoryId) {
                $.ajax({
                    url: "{{ route('fetch-subcategories') }}",
                    type: "GET",
                    data: {
                        category_id: categoryId
                    },
                    success: function(data) {
                        $('#edit_sub_category').html('<option value="">Select Sub-Category</option>');
                        $.each(data, function(key, subCategory) {
                            $('#edit_sub_category').append('<option value="' + subCategory.sub_category_name + '">' + subCategory.sub_category_name + '</option>');
                        });
                    },
                    error: function() {
                        alert('Error fetching subcategories.');
                    }
                });
            } else {
                $('#edit_sub_category').html('<option value="">Select Sub-Category</option>');
            }
        });
    });
</script>
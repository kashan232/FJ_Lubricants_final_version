@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Edit Product</h4>
                    <h6>Manage Product Details</h6>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif
                    <form action="{{ route('product.update', $product->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-control" name="category" id="categorySelect" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->category_name }}" {{ $category->category_name == 'Oil' ? 'selected' : '' }}>{{ $category->category_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Sub Category</label>
                                    <select class="form-control" name="sub_category" id="subCategorySelect" required>
                                        <option value="">Select Sub-Category</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Item Name</label>
                                    <input type="text" class="form-control" name="item_name" value="{{ $product->item_name }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Size</label>
                                    <select class="form-control" name="size" id="sizeSelect" required>
                                        <option value="">Select Size</option>
                                        @foreach ($sizes as $size)
                                        <option value="{{ $size->size_name }}" {{ $size->size_name == '1 liter' ? 'selected' : '' }}>{{ $size->size_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Pcs in Carton</label>
                                    <input type="number" class="form-control" name="pcs_in_carton" value="{{ $product->pcs_in_carton }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Carton Quantity</label>
                                    <input type="number" class="form-control" name="carton_quantity" id="carton_quantity" value="{{ $product->carton_quantity }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Initial Stock</label>
                                    <input type="number" class="form-control" name="initial_stock" id="initial_stock" value="{{ $product->initial_stock }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Loose Pieces</label>
                                    <input type="number" class="form-control" id="loose_pieces" name="loose_pieces" value="{{ $product->loose_pieces }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Wholesale Price</label>
                                    <input type="number" class="form-control" name="wholesale_price" value="{{ $product->wholesale_price }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Retail Price</label>
                                    <input type="number" class="form-control" name="retail_price" value="{{ $product->retail_price }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Alert Quantity</label>
                                    <input type="number" class="form-control" name="alert_quantity" value="{{ $product->alert_quantity }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let cartonQuantityInput = document.getElementById("carton_quantity");
        let pcsInCartonInput = document.querySelector("input[name='pcs_in_carton']");
        let initialStockInput = document.getElementById("initial_stock");
        let loosePiecesInput = document.getElementById("loose_pieces");

        function updateInitialStock() {
            let cartonQuantity = parseInt(cartonQuantityInput.value) || 0;
            let pcsInCarton = parseInt(pcsInCartonInput.value) || 0;
            initialStockInput.value = cartonQuantity * pcsInCarton;
        }

        function updateLoosePieces() {
            let pcsInCarton = parseInt(pcsInCartonInput.value) || 0;
            let initialStock = parseInt(initialStockInput.value) || 0;
            loosePiecesInput.value = initialStock - pcsInCarton;
        }

        cartonQuantityInput.addEventListener("input", updateInitialStock);
        pcsInCartonInput.addEventListener("input", updateInitialStock);
        initialStockInput.addEventListener("input", updateLoosePieces);
        pcsInCartonInput.addEventListener("input", updateLoosePieces);
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

     document.addEventListener("DOMContentLoaded", function() {
        const categorySelect = document.getElementById("categorySelect");
        const subCategorySelect = document.getElementById("subCategorySelect");
        const selectedSubCategory = "Engine Oil";

        categorySelect.addEventListener("change", function() {
            const category = this.value;
            subCategorySelect.innerHTML = '<option value="">Loading...</option>';

            if (category) {
                $.ajax({
                    url: "{{ route('fetch-subcategories') }}",
                    type: "GET",
                    data: { category_id: category },
                    success: function(data) {
                        subCategorySelect.innerHTML = '<option value="">Select Sub-Category</option>';
                        data.forEach(function(subCategory) {
                            const selected = subCategory.sub_category_name === selectedSubCategory ? 'selected' : '';
                            subCategorySelect.innerHTML += `<option value="${subCategory.sub_category_name}" ${selected}>${subCategory.sub_category_name}</option>`;
                        });
                    },
                    error: function() {
                        alert('Error fetching subcategories.');
                    }
                });
            } else {
                subCategorySelect.innerHTML = '<option value="">Select Sub-Category</option>';
            }
        });

        // Trigger change event to load the subcategory on page load
        categorySelect.dispatchEvent(new Event("change"));
    });
</script>
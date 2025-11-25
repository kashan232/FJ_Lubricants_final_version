@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Product Stock</h4>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Sub-Category</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Size</th>
                                    <th>Prcie</th>
                                    <th>Pcs/Carton</th>
                                    <th>Carton Quantity</th>
                                    <th>Pcs</th>
                                    <th>initial Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $key => $product)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $product->category }}</td>
                                    <td>{{ $product->subcategory }}</td>
                                    <td>{{ $product->code }}</td>
                                    <td>{{ $product->item }}</td>
                                    <td>{{ $product->size }}</td>
                                    <td>{{ $product->price }}</td>
                                    <td>{{ $product->pcs_carton }}</td>
                                    <td>{{ $product->carton_quantity }}</td>
                                    <td>{{ $product->pcs }}</td>
                                    <td>{{ $product->initial_stock }}</td>
                                   
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
@include('admin_panel.include.footer_include')
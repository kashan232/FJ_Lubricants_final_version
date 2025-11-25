@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                @if(Auth::check() && Auth::user()->usertype === 'admin')
                <div class="page-title">
                    <h4>Distributor Sales Management</h4>
                    <h6>Manage Distributor Sales Efficiently</h6>
                </div>
                @elseif(Auth::check() && Auth::user()->usertype === 'distributor')
                <div class="page-title">
                    <h4>Distributor Purchased Management</h4>
                </div>
                @endif
            </div>

            <div class="card p-4">
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
                                    <th>Invoice No</th>
                                    <th>Date</th>
                                    <th>Customer | Owner</th>
                                    <th>City | Area</th>
                                    <th>Address | Phone</th>
                                    <th>Booker</th>
                                    <th>Saleman</th>
                                    <th>Category</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($Sales as $sale)
                                <tr>
                                    <td>{{ $sale->invoice_number }}
                                        @if($sale->return_status == 1)
                                        <span class="badge bg-danger text-white ms-2">Returned</span>
                                        @endif
                                    </td>
                                    <td>{{ $sale->Date }}</td>
                                    <td>{{ $sale->distributor->Customer ?? 'N/A' }} <br> {{ $sale->distributor->Owner ?? 'N/A' }}</td>
                                    <td>{{ $sale->distributor_city }} <br> {{ $sale->distributor_area }}</td>
                                    <td>{{ $sale->distributor_address }} <br> {{ $sale->distributor_phone }}</td>
                                    <td>{{ $sale->Booker }}</td>
                                    <td>{{ $sale->Saleman }}</td>
                                    <td>
                                        @php
                                        $categories = json_decode($sale->category, true);
                                        @endphp
                                        {{ is_array($categories) ? implode(', ', $categories) : $categories }}
                                    </td>
                                    <td>
                                        @php
                                        $items = json_decode($sale->item, true);
                                        @endphp
                                        {{ is_array($items) ? implode(', ', $items) : $items }}
                                    </td>
                                    <td>{{ number_format($sale->net_amount, 2) }}</td>
                                    <td>
                                        <a href="{{ route('sale.invoice', $sale->id) }}" class="btn btn-dark btn-sm text-white">
                                            Invoice
                                        </a>

                                        @if(Auth::check() && Auth::user()->usertype === 'admin')
                                        <a href="{{ route('sale.edit', $sale->id) }}" class="btn btn-primary btn-sm text-white">
                                            Edit
                                        </a>

                                        <button class="btn btn-danger btn-sm delete-sale" data-id="{{ $sale->id }}">
                                            Delete
                                        </button>
                                        @endif
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
@include('admin_panel.include.footer_include')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const deleteRoute = "{{ route('sale.delete', ['id' => '__id__']) }}";

    $('.delete-sale').on('click', function() {
        let saleId = $(this).data('id');
        let finalRoute = deleteRoute.replace('__id__', saleId);

        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the invoice and update ledger!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = finalRoute;
            }
        });
    });
</script>
@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Distributor Payments Management</h4>
                    <h6>Manage Distributor Payments Efficiently</h6>
                </div>
            </div>
            <div class="card p-4">
                <div class="card-body">
                    <form action="{{ route('Distributor.payment.store') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer" class="form-label text-dark">Received From <span class="text-danger">*</span></label>
                                <select id="distributor" name="distributor_id" class="form-select select2-basic search" required>
                                    <option selected disabled>Select Distributor</option>
                                    @foreach($distributors as $distributor)
                                    <option value="{{ $distributor->id }}">{{ $distributor->Customer }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="amount" class="form-label text-dark">Payment Amount (PKR) <span class="text-danger">*</span></label>
                                <input type="number" id="amount" name="amount" class="form-control" placeholder="Enter payment amount" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date" class="form-label text-dark">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" id="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="bank" class="form-label text-dark">Salesman <span class="text-danger">*</span></label>
                                <select class="form-control" id="salesman" name="salesman" required>
                                    <option value="" disabled>Select Salesman</option>
                                    @foreach($Salesmans as $saleman)
                                    <option value="{{ $saleman->name }}">{{ $saleman->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="detail" class="form-label text-dark">Payment Method Details (e.g. JazzCash, EasyPaisa)</label>
                            <input type="text" id="detail" name="detail" class="form-control" placeholder="Enter additional payment details">
                        </div>

                        <div class="text-end fw-bold text-secondary mb-3">
                            Customer Balance: <span id="customer_balance" class="text-dark">PKR 0</span>
                        </div>


                        <div class="d-flex justify-content-center gap-3">
                            <button type="submit" class="btn btn-success">Save & Close</button>
                            <button type="submit" class="btn btn-primary">Save & Add New</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')
<script>
    $('#distributor').on('change', function() {
        var distributorId = $(this).val();
        var url = "{{ route('get.Distributor.balance', ':id') }}";
        url = url.replace(':id', distributorId);

        $.get(url, function(data) {
            $('#customer_balance').html(`<span style="color:red; font-size: 20px; font-weight: bold;">PKR ${data.balance}</span>`);

            // Hide or clear the sales table
            $('#sales_table tbody').empty();
        });
    });
</script>
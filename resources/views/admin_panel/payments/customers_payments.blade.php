@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Customer Payments Management</h4>
                    <h6>Manage Customer Payments Efficiently</h6>
                </div>
            </div>
            <div class="card p-4">
                <div class="card-body">
                    <form action="{{ route('customer.payment.store') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer" class="form-label text-dark">
                                    Received From <span class="text-danger">*</span>
                                </label>
                                <select id="customer" name="customer_id" class="form-select select2-basic search" required>
                                    <option selected disabled>Select Customer</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->shop_name }} ({{ $customer->customer_name }}) ({{ $customer->area }})
                                    </option>
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

                        <div class="text-end fw-bold text-danger mb-4 fs-5">
                            Customer Balance: <span id="customer_balance">PKR 0</span>
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

   

    const routeTemplate = "{{ route('get.customer.balance', ['id' => 'CUSTOMER_ID']) }}";

    function fetchCustomerData(customerId) {
        let url = routeTemplate.replace('CUSTOMER_ID', customerId);

        fetch(url)
            .then(response => response.json())
            .then(data => {
                document.getElementById('customer_balance').innerText = 'PKR ' + data.balance;
            });
    }
    $(document).ready(function() {
        $('#customer').on('change', function() {
            let customerId = $(this).val();
            if (customerId) {
                fetchCustomerData(customerId);
            } else {
                document.getElementById('customer_balance').innerText = 'PKR 0';
                document.querySelector('#sales_table tbody').innerHTML = '<tr><td colspan="2">No Sales Found</td></tr>';
            }
        });
    });
</script>
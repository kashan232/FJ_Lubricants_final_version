@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Vendor Payments Management</h4>
                    <h6>Manage Vendor Payments Efficiently</h6>
                </div>
            </div>
            <div class="card p-4">
                <div class="card-body">
                    <form action="{{ route('vendor-payment-store') }}" method="POST">
                        @csrf
                        {{-- Vendor and Payment Amount --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="Vendor" class="form-label text-dark">Payment TO <span class="text-danger">*</span></label>
                                <select id="Vendor" name="Vendor_id" class="form-select select2-basic search">
                                    <option selected disabled>Select Vendor</option>
                                    @foreach($Vendors as $Vendor)
                                    <option value="{{ $Vendor->id }}">{{ $Vendor->Party_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="amount" class="form-label text-dark">Payment Amount (PKR) <span class="text-danger">*</span></label>
                                <input type="number" id="amount" name="amount" class="form-control" placeholder="Enter payment amount">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="date" class="form-label text-dark">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" id="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="detail" class="form-label text-dark">Payment Method Details (e.g. JazzCash, EasyPaisa)</label>
                            <input type="text" id="detail" name="detail" class="form-control" placeholder="Enter additional payment details">
                        </div>

                        <div class="text-end fw-bold text-secondary mb-3">
                            Vendor Balance: <span id="Vendor_balance" class="text-dark">PKR 0</span>
                        </div>

                        {{-- Buttons --}}
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
    document.addEventListener("DOMContentLoaded", function() {
        const select = document.getElementById("Vendor");
        select.addEventListener("change", function() {
            const selectedId = this.value;
            fetchVendorData(selectedId);
        });
    });

    const baseUrl = "{{ url('/get-vendor-balance') }}";

    function fetchVendorData(vendorId) {
        let url = `${baseUrl}/${vendorId}`;
        fetch(url)
            .then(res => res.json())
            .then(data => {
                console.log("Data received:", data);
                document.getElementById('Vendor_balance').innerText = 'PKR ' + (data.balance ?? 0);
            })
            .catch(err => console.error('Fetch error:', err));
    }
</script>

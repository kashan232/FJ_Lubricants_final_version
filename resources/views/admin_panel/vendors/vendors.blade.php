@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Vendor List</h4>
                    <h6>Manage Vendors</h6>
                </div>
                <div class="page-btn">
                    @if(Auth::user()->usertype === 'admin')
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                        <img src="assets/img/icons/plus.svg" class="me-1" alt="img"> Add Vendor
                    </button>
                    @else
                    <button class="btn btn-sm btn-danger" disabled>No Action</button>
                    @endif
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
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>City</th>
                                    <th>Area</th>
                                    @if(Auth::user()->usertype === 'admin')
                                        <th>Address</th>
                                        <th>Phone</th>
                                    @endif
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($Vendors as $key => $Vendor)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $Vendor->Party_code }}</td>
                                    <td>{{ $Vendor->Party_name }}</td>
                                    <td>{{ $Vendor->City }}</td>
                                    <td>{{ $Vendor->Area }}</td>
                                    @if(Auth::user()->usertype === 'admin')
                                        <td>{{ $Vendor->Party_address }}</td>
                                        <td>{{ $Vendor->Party_phone }}</td>
                                    @endif
                                    <td>
                                        <!-- Edit Button with Admin Check -->
                                        @if(Auth::user()->usertype === 'admin')
                                        <button class="btn btn-sm btn-primary editVendorBtn"
                                            data-id="{{ $Vendor->id }}"
                                            data-name="{{ $Vendor->Party_code }}"
                                            data-Party_name="{{ $Vendor->Party_name }}"
                                            data-city="{{ $Vendor->City }}"
                                            data-area="{{ $Vendor->Area }}"
                                            data-Party_address="{{ $Vendor->Party_address }}"
                                            data-Party_phone="{{ $Vendor->Party_phone }}"
                                            data-email="{{ $Vendor->email }}"
                                            data-password="{{ $Vendor->password }}"
                                            data-bs-toggle="modal" data-bs-target="#editVendorModal">
                                            Edit
                                        </button>
                                        @else
                                        <button class="btn btn-sm btn-danger" disabled>No Action</button>
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

<div class="modal fade" id="addVendorModal" tabindex="-1" aria-labelledby="addVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('store-vendors') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Party Code</label>
                            <input type="text" class="form-control" name="Party_code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Party Name</label>
                            <input type="text" class="form-control" name="Party_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <select class="form-control" name="city" id="citySelect" required>
                                <option value="" disabled selected>Select City</option>
                                @foreach($cities as $city)
                                <option value="{{ $city->city_name }}">{{ $city->city_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Area</label>
                            <select class="form-control" name="area" id="areaSelect" required>
                                <option value="">Select Area</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Opening Balance</label>
                            <input type="number" class="form-control" name="opening_balance" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="Party_address" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="Party_phone" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Vendor Modal -->
<div class="modal fade" id="editVendorModal" tabindex="-1" aria-labelledby="editVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editVendorForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_Vendor_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Party Code</label>
                            <input type="text" class="form-control" name="Party_code" id="edit_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Party Name</label>
                            <input type="text" class="form-control" name="Party_name" id="edit_Party_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <select class="form-control" name="city" id="edit_city" required>
                                <option value="" disabled selected>Select City</option>
                                @foreach($cities as $city)
                                <option value="{{ $city->city_name }}">{{ $city->city_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Area</label>
                            <select class="form-control" name="area" id="edit_area" required>
                                <option value="">Select Area</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="Party_address" value="Party_address" id="edit_Party_address" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="Party_phone" id="edit_Party_phone" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Existing Opening Balance</label>
                            <input type="text" class="form-control" id="existing_opening_balance" readonly>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Recape Type</label>
                            <select class="form-control" name="recape_type">
                                <option value="">Select Recape Type</option>
                                <option value="plus">Plus</option>
                                <option value="minus">Minus</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Recape Amount</label>
                            <input type="number" class="form-control" name="recape_opening" min="0">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>


@include('admin_panel.include.footer_include')
<script>
    function togglePassword() {
        let passwordField = document.getElementById("edit_password");
        let passwordIcon = document.getElementById("password_icon");
        if (passwordField.type === "password") {
            passwordField.type = "text";
            passwordIcon.classList.remove("fa-eye");
            passwordIcon.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            passwordIcon.classList.remove("fa-eye-slash");
            passwordIcon.classList.add("fa-eye");
        }
    }

    $(document).on("click", ".editVendorBtn", function() {
        let id = $(this).data("id");
        let name = $(this).data("name");
        let Party_name = $(this).data("party_name");
        let city = $(this).data("city");
        let area = $(this).data("area");
        let Party_address = $(this).data("party_address");
        let Party_phone = $(this).data("party_phone");
        let email = $(this).data("email") || ''; // Null Check
        let password = $(this).data("password") || ''; // Null Check

        $("#edit_Vendor_id").val(id);
        $("#edit_name").val(name);
        $("#edit_Party_name").val(Party_name);
        $("#edit_Party_address").val(Party_address);
        $("#edit_Party_phone").val(Party_phone);
        $("#edit_email").val(email);
        $("#edit_password").val(password);
        $("#edit_city").val(city).trigger("change");

        $.ajax({
            url: `{{ route('vendor.ledger', '') }}/${id}`,
            type: 'GET',
            success: function(response) {
                $("#existing_opening_balance").val(response.opening_balance);
            }
        });

        $.ajax({
            url: '{{ route("get-areas") }}',
            type: 'GET',
            data: {
                city_id: city
            },
            success: function(response) {
                $('#edit_area').html('<option value="">Select Area</option>');
                $.each(response, function(index, value) {
                    let selected = (value === area) ? 'selected' : '';
                    $('#edit_area').append('<option value="' + value + '" ' + selected + '>' + value + '</option>');
                });
            }
        });

        let updateUrl = `{{ route('vendors.update', '') }}/${id}`;
        $("#editVendorForm").attr("action", updateUrl);

    });


    $('#citySelect').change(function() {
        let cityId = $(this).val();
        $.ajax({
            url: '{{ route("get-areas") }}',
            type: 'GET',
            data: {
                city_id: cityId
            },
            success: function(response) {
                $('#areaSelect').html('<option value="">Select Area</option>');
                $.each(response, function(id, area) {
                    $('#areaSelect').append('<option value="' + area + '">' + area + '</option>');
                });
            }
        });
    });

    $('#edit_city').change(function() {
        let cityId = $(this).val();
        $.ajax({
            url: '{{ route("get-areas") }}',
            type: 'GET',
            data: {
                city_id: cityId
            },
            success: function(response) {
                $('#edit_area').html('<option value="">Select Area</option>');
                $.each(response, function(id, area) {
                    $('#edit_area').append('<option value="' + area + '">' + area + '</option>');
                });
            }
        });
    });
</script>
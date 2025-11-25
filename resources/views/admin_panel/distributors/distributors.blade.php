@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Distributor List</h4>
                    <h6>Manage Distributors</h6>
                </div>
                <div class="page-btn">
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addDistributorModal">
                        <img src="assets/img/icons/plus.svg" class="me-1" alt="img"> Add Distributor
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
                                    <th>Distributor Name</th>
                                    <th>City</th>
                                    <th>Area</th>
                                    <th>Contact</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($distributors as $key => $distributor)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $distributor->Customer }}</td>
                                    <td>{{ $distributor->City }}</td>
                                    <td>{{ $distributor->Area }}</td>
                                    <td>{{ $distributor->Contact }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary editDistributorBtn"
                                            data-id="{{ $distributor->id }}"
                                            data-name="{{ $distributor->Customer }}"
                                            data-owner="{{ $distributor->Owner }}"
                                            data-city="{{ $distributor->City }}"
                                            data-area="{{ $distributor->Area }}"
                                            data-address="{{ $distributor->Address }}"
                                            data-contact="{{ $distributor->Contact }}"
                                            data-bs-toggle="modal" data-bs-target="#editDistributorModal">
                                            Edit
                                        </button>

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

{{-- add distribtor modal --}}
<div class="modal fade" id="addDistributorModal" tabindex="-1" aria-labelledby="addDistributorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Distributor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('store-Distributor') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Distributor Name</label>
                            <input type="text" class="form-control" name="Customer" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Owner</label>
                            <input type="text" class="form-control" name="owner" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <select class="form-control" name="city" id="citySelect" required>
                                <option value="" disabled selected>Select City</option>
                                @foreach($cities as $city)
                                <option value="{{ $city->city_name }}">{{ $city->city_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Area</label>
                            <select class="form-control" name="area" id="areaSelect" required>
                                <option value="">Select Area</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Address here">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Contact</label>
                            <input type="text" class="form-control" name="contact" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Opening Balance</label>
                            <input type="text" class="form-control" name="opening_balance" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
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

<!-- Edit Distributor Modal -->
<div class="modal fade" id="editDistributorModal" tabindex="-1" aria-labelledby="editDistributorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Distributor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDistributorForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_distributor_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Distributor Name</label>
                            <input type="text" class="form-control" name="Customer" id="edit_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Owner</label>
                            <input type="text" class="form-control" name="owner" id="edit_owner" required>
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
                            <input type="text" name="address" value="address" id="edit_address" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact</label>
                            <input type="text" class="form-control" name="contact" id="edit_contact" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Existing Opening Balance</label>
                            <input type="number" class="form-control" id="existing_opening_balance" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Recape Type</label>
                            <select class="form-control" name="recape_type" id="recape_type" required>
                                <option value="" selected disabled>Select Recape Type</option>
                                <option value="plus">Plus</option>
                                <option value="minus">Minus</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Recape Opening Balance</label>
                            <input type="number" class="form-control" name="recape_opening_balance" id="recape_opening_balance" required>
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

    $(document).on("click", ".editDistributorBtn", function() {
        $("#editDistributorForm")[0].reset(); // Form Reset

        let id = $(this).data("id");
        let name = $(this).data("name");
        let owner = $(this).data("owner");
        let city = $(this).data("city");
        let area = $(this).data("area");
        let address = $(this).data("address");
        let contact = $(this).data("contact");

        console.log({
            id,
            name,
            owner,
            city,
            area,
            address,
            contact,
        });

        $("#edit_distributor_id").val(id);
        $("#edit_name").val(name);
        $("#edit_owner").val(owner);
        $("#edit_address").val(address);
        $("#edit_contact").val(contact);
        $("#edit_city").val(city).trigger("change");

        $.ajax({
            url: `{{ route('distributor.ledger', '') }}/${id}`,
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

        let updateUrl = `{{ route('Distributor.update', '') }}/${id}`;
        $("#editDistributorForm").attr("action", updateUrl);
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
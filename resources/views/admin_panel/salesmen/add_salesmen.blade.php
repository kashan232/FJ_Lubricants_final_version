    @include('admin_panel.include.header_include')
    <div class="main-wrapper">
        @include('admin_panel.include.navbar_include')
        @include('admin_panel.include.admin_sidebar_include')

        <div class="page-wrapper">
            <div class="content">
                <div class="page-header">
                    <div class="page-title">
                        <h4>Staff Management List</h4>
                        <h6>Manage Staff Management</h6>
                    </div>
                    <div class="page-btn">
                        <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addSalesmanModal">
                            <img src="assets/img/icons/plus.svg" class="me-1" alt="img">Add Staff
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
                                        <th>Designation</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>City</th>
                                        <th>Area</th>
                                        <th>Address</th>
                                        <th>Salary</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salesmen as $key => $salesman)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $salesman->designation }}</td>
                                        <td>{{ $salesman->name }}</td>
                                        <td>{{ $salesman->phone }}</td>
                                        <td>{{ $salesman->city }}</td>
                                        <td>{{ $salesman->area }}</td>
                                        <td>{{ $salesman->address }}</td>
                                        <td>{{ number_format($salesman->salary, 2) }}</td>
                                        <td>
                                            <button class="btn btn-sm toggle-status"
                                                data-id="{{ $salesman->id }}"
                                                data-status="{{ $salesman->status }}">
                                                {{ $salesman->status == 1 ? 'Active' : 'Inactive' }}
                                            </button>
                                        </td>
                                        {{-- <td>{{ $salesman->status == 1 ? 'Active' : 'Inactive' }}</td> --}}
                                        <td>
                                            <button class="btn btn-sm btn-primary editSalesmanBtn"
                                                data-id="{{ $salesman->id }}"
                                                data-name="{{ $salesman->name }}"
                                                data-phone="{{ $salesman->phone }}"
                                                data-city="{{ $salesman->city }}"
                                                data-area="{{ $salesman->area }}"
                                                data-address="{{ $salesman->address }}"
                                                data-salary="{{ $salesman->salary }}"
                                                data-status="{{ $salesman->status }}"
                                                data-bs-toggle="modal" data-bs-target="#editSalesmanModal">
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
    <!-- Add Salesman Modal -->
    <div class="modal fade" id="addSalesmanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Increased modal size -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Staff </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('store-salesman') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <!-- Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- City -->
                            <div class="col-md-6 mb-3">
                                <label for="citySelect" class="form-label">City</label>
                                <select class="form-control" name="city" id="citySelect" required>
                                    <option value="">Select City</option>
                                    @foreach($city as $city)
                                    <option value="{{ $city->city_name }}">{{ $city->city_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Area -->
                            <div class="col-md-6 mb-3">
                                <label for="areasSelect" class="form-label">Area</label>

                                <select class="form-control" name="area" id="areasSelect" required>
                                    <option value="">Select Areas</option>
                                </select>

                            </div>
                        </div>

                        <div class="row">
                            <!-- Address -->
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" required>
                            </div>

                            <!-- Salary -->
                            <div class="col-md-6 mb-3">
                                <label for="salary" class="form-label">Salary</label>
                                <input type="number" class="form-control" name="salary" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Designation -->
                            <div class="col-md-6 mb-3">
                                <label for="designationSelect" class="form-label">Designation</label>
                                <select class="form-control" name="designation" id="designationSelect" required>
                                    <option value="Order Booker">Order Booker</option>
                                    <option value="Saleman">Saleman</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" name="status" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <!-- These fields will be shown only when designation is "Saleman" -->
                            <div class="row d-none" id="loginFields">
                                <!-- Email -->
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="emailField">
                                </div>

                                <!-- Password -->
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" id="passwordField">
                                </div>
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


    <!-- Edit Salesman Modal -->
    <div class="modal fade" id="editSalesmanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('update-salesman') }}" method="POST">
                    @csrf
                    <input type="hidden" name="salesman_id" value="{{ $salesman->id ?? 'N/A' }}">
                    <input type="hidden" id="edit_salesman_id" name="salesman_id">

                    <div class="modal-body">
                        <label>Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>

                        <label>Phone</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone" required>



                        <label>Area</label>
                        <select class="form-control" id="edit_area" name="area" required></select>

                        <label>Address</label>
                        <input type="text" class="form-control" id="edit_address" name="address" required>

                        <label>Salary</label>
                        <input type="number" class="form-control" id="edit_salary" name="salary" required>


                        <label>Designation</label>
                        <select class="form-control" name="designation" id="edit_designation" required>
                            <option value="Order Booker">Order Booker</option>
                            <option value="Saleman">Saleman</option>
                        </select>


                        <label>Status</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
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
        document.addEventListener("DOMContentLoaded", function() {
            const designationSelect = document.getElementById("designationSelect");
            const loginFields = document.getElementById("loginFields");
            const emailField = document.getElementById("emailField");
            const passwordField = document.getElementById("passwordField");

            function toggleLoginFields() {
                if (designationSelect.value === "Saleman") {
                    loginFields.classList.remove("d-none");
                    emailField.setAttribute("required", "required");
                    passwordField.setAttribute("required", "required");
                } else {
                    loginFields.classList.add("d-none");
                    emailField.removeAttribute("required");
                    passwordField.removeAttribute("required");
                    emailField.value = "";
                    passwordField.value = "";
                }
            }

            designationSelect.addEventListener("change", toggleLoginFields);

            // Initial call in case default value is already selected
            toggleLoginFields();
        });
    </script>

    <script>
        $(document).ready(function() {
            // Add Product Modal: Fetch areas on Category Change
            $('#citySelect').change(function() {
                var cityId = $(this).val();
                $('#areasSelect').html('<option value="">Loading...</option>');

                if (cityId) {
                    $.ajax({
                        url: "{{ route('fetch-areas') }}",
                        type: "GET",
                        data: {
                            city_id: cityId
                        },
                        success: function(data) {
                            $('#areasSelect').html('<option value="">Select Area</option>');
                            $.each(data, function(key, area) {
                                $('#areasSelect').append('<option value="' + area.area_name + '">' + area.area_name + '</option>');
                            });
                        },
                        error: function() {
                            alert('Error fetching areas.');
                        }
                    });
                } else {
                    $('#areasSelect').html('<option value=""> Area Not Found...</option>');
                }
            });
        });

        $(document).on("click", ".editSalesmanBtn", function() {
            $("#edit_salesman_id").val($(this).data("id"));
            $("#edit_name").val($(this).data("name"));
            $("#edit_phone").val($(this).data("phone"));
            $("#edit_city").val($(this).data("city")).trigger("change");
            $("#edit_address").val($(this).data("address"));
            $("#edit_salary").val($(this).data("salary"));
            $("#edit_status").val($(this).data("status"));

            // Fetch areas based on city selection
            var selectedCity = $(this).data("city");
            var selectedArea = $(this).data("area");

            if (selectedCity) {
                $.ajax({
                    url: "{{ route('fetch-areas') }}",
                    type: "GET",
                    data: {
                        city_id: selectedCity
                    },
                    success: function(data) {
                        $("#edit_area").html('<option value="">Select Area</option>');
                        $.each(data, function(key, area) {
                            $("#edit_area").append('<option value="' + area.area_name + '">' + area.area_name + '</option>');
                        });

                        // Set the correct area
                        $("#edit_area").val(selectedArea);
                    },
                });
            }
        });

        $(".toggle-status").click(function() {
            var button = $(this);
            var salesmanId = button.data("id");
            var currentStatus = button.data("status");

            $.ajax({
                url: "{{ route('toggle-salesman-status') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    salesman_id: salesmanId,
                    status: currentStatus == 1 ? 0 : 1
                },
                success: function(response) {
                    if (response.success) {
                        let newStatus = currentStatus == 1 ? 0 : 1;
                        button.data("status", newStatus);
                        button.text(newStatus == 1 ? "Active" : "Inactive");

                        if (newStatus == 1) {
                            button.removeClass("btn-danger").addClass("btn-success");
                        } else {
                            button.removeClass("btn-success").addClass("btn-danger");
                        }
                    }
                }
            });
        });



        // When editing, fetch areas based on selected city
        function fetchAreas(cityId, selectedAreaId) {
            if (cityId) {
                $.ajax({
                    url: '/get-areas/' + cityId,
                    type: 'GET',
                    success: function(response) {
                        $('#edit_area').html('<option value="">Select Area</option>');
                        $.each(response, function(key, area) {
                            var selected = area.id == selectedAreaId ? 'selected' : '';
                            $('#edit_area').append('<option value="' + area.id + '" ' + selected + '>' + area.area_name + '</option>');
                        });
                    }
                });
            }
        }
    </script>
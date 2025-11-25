@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Area List</h4>
                    <h6>Manage Areas</h6>
                </div>
                <div class="page-btn">
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addAreaModal">
                        <img src="assets/img/icons/plus.svg" class="me-1" alt="img"> Add Area
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
                                    <th>City Name</th>
                                    <th>Area Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($Areas as $key => $area)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $area->city_name }}</td>
                                    <td>{{ $area->area_name }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary editAreaBtn" 
                                                data-id="{{ $area->id }}" 
                                                data-city="{{ $area->city_name }}" 
                                                data-area="{{ $area->area_name }}" 
                                                data-bs-toggle="modal" data-bs-target="#editAreaModal">Edit</button>
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

<!-- Add Area Modal -->
<div class="modal fade" id="addAreaModal" tabindex="-1" aria-labelledby="addAreaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('store-Area') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select City</label>
                        <select class="form-control" name="city_name" required>
                            <option value="" disabled selected>Select City</option>
                            @foreach($Citys as $city)
                                <option value="{{ $city->city_name }}">{{ $city->city_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Area Name</label>
                        <input type="text" class="form-control" name="area_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Area Modal -->
<div class="modal fade" id="editAreaModal" tabindex="-1" aria-labelledby="editAreaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('Area.update') }}" method="POST">
                @csrf
                <input type="hidden" name="area_id" id="edit_area_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select City</label>
                        <select class="form-control" name="city_name" id="edit_city_name" required>
                            @foreach($Citys as $city)
                                <option value="{{ $city->city_name }}">{{ $city->city_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Area Name</label>
                        <input type="text" class="form-control" name="area_name" id="edit_area_name" required>
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
    $(document).on("click", ".editAreaBtn", function() {
        let id = $(this).data("id");
        let city = $(this).data("city");
        let area = $(this).data("area");

        $("#edit_area_id").val(id);
        $("#edit_city_name").val(city);
        $("#edit_area_name").val(area);
    });
</script>

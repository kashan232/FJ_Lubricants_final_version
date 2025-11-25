@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Business Type List</h4>
                    <h6>Manage Business Types</h6>
                </div>
                <div class="page-btn">
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addBusinessTypeModal">
                        <img src="assets/img/icons/plus.svg" class="me-1" alt="img"> Add Business Type
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
                                    <th>Business Type Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($businessTypes as $key => $businessType)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $businessType->business_type_name }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary editBusinessTypeBtn" data-id="{{ $businessType->id }}" data-name="{{ $businessType->business_type_name }}" data-bs-toggle="modal" data-bs-target="#editBusinessTypeModal">Edit</button>
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

<!-- Add Business Type Modal -->
<div class="modal fade" id="addBusinessTypeModal" tabindex="-1" aria-labelledby="addBusinessTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Business Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('business_type.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Business Type Name</label>
                        <input type="text" class="form-control" name="business_type_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Business Type Modal -->
<div class="modal fade" id="editBusinessTypeModal" tabindex="-1" aria-labelledby="editBusinessTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Business Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('business_type.update') }}" method="POST">
                @csrf
                <input type="hidden" name="business_type_id" id="edit_business_type_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Business Type Name</label>
                        <input type="text" class="form-control" name="business_type_name" id="edit_business_type_name" required>
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
    $(document).on("click", ".editBusinessTypeBtn", function() {
        let id = $(this).data("id");
        let name = $(this).data("name");
        $("#edit_business_type_id").val(id);
        $("#edit_business_type_name").val(name);
    });
</script>

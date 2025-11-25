@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
                <div class="page-header">
                    <div class="page-title">
                        <h4>Designation Management</h4>
                        <h6>Add, View & Edit Designations</h6>
                    </div>
                    <div class="page-btn">
                        <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addDesignationModal">
                            <img src="assets/img/icons/plus.svg" class="me-1" alt="img"> Add Designation
                        </button>
                    </div>
                </div>

                @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <!-- Designation List -->
                        <div class="table-responsive">
                            <table class="table datanew">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Designation</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($designations as $key => $designation)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $designation->designation }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary editDesignationBtn"
                                                    data-id="{{ $designation->id }}"
                                                    data-name="{{ $designation->designation }}" data-bs-toggle="modal"
                                                    data-bs-target="#editDesignationModal">Edit</button>

                                                <button class="btn btn-sm btn-danger"
                                                    data-id="{{ $designation->id }}">Delete</button>
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

    <!-- Add Designation Modal -->
    <div class="modal fade" id="addDesignationModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('designation.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Designation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Designation Name</label>
                            <input type="text" class="form-control" name="designation" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Designation Modal -->
    <div class="modal fade" id="editDesignationModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('designation.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="designation_id" id="edit_designation_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Designation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Designation Name</label>
                            <input type="text" class="form-control" name="designation" id="edit_designation_name"
                                required>
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
        $(document).on("click", ".editDesignationBtn", function() {
            let id = $(this).data("id");
            let name = $(this).data("name");
            $("#edit_designation_id").val(id);
            $("#edit_designation_name").val(name);
        });

      
    </script>
        



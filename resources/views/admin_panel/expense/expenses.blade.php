@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Expense List</h4>
                    <h6>Manage Expenses</h6>
                </div>
                <div class="page-btn">
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                        <img src="assets/img/icons/plus.svg" class="me-1" alt="img">Add Expense
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
                                    <th>Expense Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenses as $key => $expense)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $expense->expense_category }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary editExpenseBtn" 
                                        data-id="{{ $expense->id }}" 
                                        data-name="{{ $expense->expense_category }}" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editExpenseModal">Edit</button>

                                        <!-- <button class="btn btn-sm btn-danger deleteAddExpenseBtn" data-id="{{ $expense->id }}">Delete</button> -->

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

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('store-expense-category') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Expense Name</label>
                        <input type="text" class="form-control" name="expense_category" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('expense.update') }}" method="POST">
                @csrf
                <input type="hidden" name="expense_id" id="edit_expense_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Expense Name</label>
                        <input type="text" class="form-control" name="expense_category" id="edit_expense_category" required>
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
    $(document).on("click", ".editExpenseBtn", function() {
        let id = $(this).data("id");
        let name = $(this).data("name");
        $("#edit_expense_id").val(id);
        $("#edit_expense_category").val(name);
    });
    

    

    $(document).on("click", ".deleteAddExpenseBtn", function(e) {
        e.preventDefault();

        let id = $(this).data("id");
        let deleteUrl = "{{ route('delete-expense-category', ':id') }}".replace(':id', id);

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    type: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") // Include CSRF token
                    },
                    success: function(response) {
                        Swal.fire("Deleted!", response.success, "success")
                            .then(() => location.reload());
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire("Error!", "Something went wrong: " + xhr.responseText, "error");
                    }
                });
            }
        });
    });
</script>


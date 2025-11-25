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
                                    <th>Date</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenses as $key => $expense)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                                    <td>{{ $expense->title }}</td>
                                    <td>{{ $expense->expense_category }}</td>
                                    <td>{{ $expense->description }}</td>
                                    <td>{{ $expense->amount }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary editExpenseBtn"
                                            data-id="{{ $expense->id }}"
                                            data-category="{{ $expense->expense_category }}"
                                            data-title="{{ $expense->title }}"
                                            data-amount="{{ $expense->amount }}"
                                            data-date="{{ $expense->date }}"
                                            data-description="{{ $expense->description }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editExpenseModal">
                                            Edit
                                        </button>

                                        <button class="btn btn-sm btn-danger deleteExpenseBtn" data-id="{{ $expense->id }}">Delete</button>

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
            <form action="{{ route('store-expense') }}" method="POST">
                @csrf
                <meta name="csrf-token" content="{{ csrf_token() }}">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Expense Category</label>
                        <select class="form-control" name="expense_category" required>
                            <option value="">Select Category</option>
                            @foreach($expenseCategories as $category)
                            <option value="{{ $category->expense_category }}">{{ $category->expense_category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description"></textarea>
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
            <form action="{{ route('update-expense') }}" method="POST">
                @csrf
                <input type="hidden" id="edit_expense_id" name="expense_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Expense Category</label>
                        <select class="form-control" id="edit_expense_category" name="expense_category" required>
                            <option value="">Select Category</option>
                            @foreach($expenseCategories as $category)
                            <option value="{{ $category->expense_category }}">{{ $category->expense_category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" id="edit_expense_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" id="edit_expense_amount" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_expense_date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="edit_expense_description" name="description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@include('admin_panel.include.footer_include')
<!-- SweetAlert2 Library -->


<script>
    // DELETE Expense with SweetAlert and AJAX
    $(document).on("click", ".deleteExpenseBtn", function(e) {
        e.preventDefault();

        let id = $(this).data("id");
        let deleteUrl = "{{ route('delete-expense', ':id') }}".replace(':id', id);

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

    // EDIT Expense - Filling Modal Data
    $(document).on("click", ".editExpenseBtn", function() {
        let id = $(this).data("id");
        let category = $(this).data("category");
        let title = $(this).data("title");
        let amount = $(this).data("amount");
        let date = $(this).data("date");
        let description = $(this).data("description");

        $("#edit_expense_id").val(id);
        $("#edit_expense_category").val(category);

        // Properly selecting the category in dropdown
        $("#edit_expense_category option").each(function() {
            $(this).prop("selected", $(this).val() == category);
        });

        $("#edit_expense_title").val(title);
        $("#edit_expense_amount").val(amount);
        $("#edit_expense_date").val(date);
        $("#edit_expense_description").val(description);
    });
</script>
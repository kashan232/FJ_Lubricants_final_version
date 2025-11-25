@include('admin_panel.include.header_include')
<style>
    .wide-input {
        min-width: 150px;
    }

    table input {
        font-size: 14px;
        padding: 6px 10px;
    }
</style>
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4">
                <h4 class="mb-4 text-center fw-bold text-primary">Purchase Return - Invoice #{{ $purchase->invoice_number }}</h4>

                <form action="{{ route('purchase.return.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                    <input type="hidden" name="invoice_number" value="{{ $purchase->invoice_number }}">

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Purchase Date</label>
                            <input type="text" class="form-control wide-input" value="{{ $purchase->purchase_date }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Party Code</label>
                            <input type="text" name="Party_code" class="form-control wide-input" value="{{ $purchase->vendor->Party_code }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Party Name</label>
                            <input type="text" class="form-control wide-input" name="party_name" value="{{ $purchase->party_name }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Return Date</label>
                            <input type="date" name="return_date" class="form-control wide-input" >
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Subcategory</th>
                                    <th>Item</th>
                                    <th>Rate</th>
                                    <th>Purchased Qty</th>
                                    <th>Return Qty</th>
                                    <th>Pcs/Carton</th>
                                    <th>Measurement</th> {{-- new --}}
                                    <th>Return Amount</th>
                                    <th>Return Liters</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->item as $index => $item)
                                <tr>
                                    <td style="width: 160px;"><input type="text" name="category[]" class="form-control wide-input" value="{{ $purchase->category[$index] }}" readonly></td>
                                    <td style="width: 160px;"><input type="text" name="subcategory[]" class="form-control wide-input" value="{{ $purchase->subcategory[$index] }}" readonly></td>
                                    <td style="width: 160px;"><input type="text" name="item[]" class="form-control wide-input" value="{{ $item }}" readonly></td>
                                    <td style="width: 160px;"><input type="text" name="rate[]" class="form-control wide-input rate" value="{{ $purchase->rate[$index] }}" readonly></td>
                                    <td style="width: 160px;"><input type="text" name="carton_qty[]" class="form-control wide-input" value="{{ $purchase->carton_qty[$index] }}" readonly></td>

                                    <td style="width: 160px;">
                                        <input type="number"
                                            name="return_qty[]"
                                            class="form-control wide-input return-qty"
                                            data-index="{{ $index }}"
                                            data-rate="{{ $purchase->rate[$index] ?? 0 }}"
                                            data-pcs="{{ $purchase->pcs_carton[$index] ?? 0 }}"
                                            data-measurement="{{ $purchase->size[$index] ?? 0 }}"
                                            max="{{ $purchase->carton_qty[$index] ?? 0 }}"
                                            min="0"
                                            value="0"
                                            required>

                                    </td>

                                    <td style="width: 160px;">
                                        <input type="text" class="form-control wide-input" value="{{ $purchase->pcs_carton[$index] }}" readonly>
                                        <input type="hidden" name="pcs_carton[]" value="{{ $purchase->pcs_carton[$index] }}">
                                    </td>

                                    <!-- measurement (size) -->
                                    <td style="width: 160px;">
                                        <input type="text" class="form-control wide-input" value="{{ $purchase->size[$index] ?? '' }}" readonly>
                                        <input type="hidden" name="size[]" value="{{ $purchase->size[$index] ?? '' }}">
                                    </td>

                                    <td style="width: 160px;">
                                        <input type="text" name="return_amount[]"
                                            class="form-control wide-input return-amount"
                                            id="return-amount-{{ $index }}"
                                            readonly>
                                    </td>

                                    <td style="width: 160px;">
                                        <input type="text" name="return_liters[]"
                                            class="form-control wide-input return-liters"
                                            id="return-liters-{{ $index }}"
                                            readonly>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary btn-lg">Submit Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
<script>
    document.querySelectorAll('.return-qty').forEach((input) => {
        input.addEventListener('input', function () {
            let index = this.dataset.index;
            let rate = parseFloat(this.dataset.rate);
            let pcs = parseFloat(this.dataset.pcs);
            let measurementRaw = this.dataset.measurement.trim().toLowerCase(); // "700 ml" or "1 liter"
            let qty = parseFloat(this.value) || 0;

            // Return Amount
            let amount = qty * rate;
            document.getElementById(`return-amount-${index}`).value = amount.toFixed(2);

            // Convert measurement to liters
            let measurementValue = 0;
            if (measurementRaw.includes('ml')) {
                measurementValue = parseFloat(measurementRaw) / 1000;
            } else if (measurementRaw.includes('liter')) {
                measurementValue = parseFloat(measurementRaw);
            } else {
                measurementValue = 0; // fallback
            }

            // Return Liters = return_qty * pcs_per_carton * measurement_in_liters
            let total = qty * pcs * measurementValue;
            let litersFormatted = (total % 1 === 0) ? total.toFixed(0) : total.toFixed(1);
            document.getElementById(`return-liters-${index}`).value = litersFormatted;
        });
    });
</script>

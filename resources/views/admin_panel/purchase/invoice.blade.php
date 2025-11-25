@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4" id="invoice">
                <div class="card-body">

                    <!-- Header Section -->
                    <div class="row mb-2 align-items-center pt-2 pb-2" style="border-bottom: 3px solid #000;">
                        <div class="col-md-4 d-flex align-items-center">
                            <img src="{{ url('small-logo.png') }}" alt="Logo" style="max-width: 120px;">
                            <h4 class="fw-bold ms-3" style="font-size: 16px;">FJL LUBRICANTS</h4>
                        </div>
                        <div class="col-md-4 text-center">
                            <h5 class="font-weight-bold">FJLUBRICANTS</h5>
                            <p class="mb-1" style="line-height: 1;">6-B Block-E, Latifabad No. 08, Hyderabad</p>
                            <p class="mb-0" style="line-height: 1;">Phone: 0314-4021603 / 0334-2611233</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <h4 class="fw-bold">MACKSOL</h4>
                        </div>
                    </div>

                    <h3 class="text-center fw-bold my-3"><span style="border-bottom: 2px solid #000;">Purchase Invoice</span></h3>
                    <div class=" p-3 mb-4" style="border: 2px solid #000;">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Invoice #: {{ $purchase->invoice_number }}</h5>
                                <h5>Purchase Date: {{ $purchase->purchase_date }}</h5>
                            </div>
                            <div class="col-md-6 text-end">
                                <h5>Party Code: {{ $purchase->party_code ?? 'N/A' }}</h5>
                                <h5>Party Name: {{ $purchase->party_name }}</h5>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="border: 1px solid #000;">Item Description</th>
                                <th style="border: 1px solid #000;">Packing</th>
                                <th style="border: 1px solid #000;">Carton Qty</th>
                                <th style="border: 1px solid #000;">Pcs Qty</th>
                                <th style="border: 1px solid #000;">Liter</th>
                                <th style="border: 1px solid #000;">Rate</th>
                                <th style="border: 1px solid #000;">Discount</th>
                                <th style="border: 1px solid #000;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(json_decode($purchase->item) as $index => $item)
                            <tr>
                                <td style="border: 1px solid #000;">{{ $item }}</td>
                                <td class="text-center" style="border: 1px solid #000;">{{ json_decode($purchase->pcs_carton)[$index] ?? 'N/A' }}</td>
                                <td class="text-center" style="border: 1px solid #000;">{{ json_decode($purchase->carton_qty)[$index] ?? 'N/A' }}</td>
                                <td class="text-center" style="border: 1px solid #000;">{{ json_decode($purchase->pcs)[$index] ?? 'N/A' }}</td>
                                <td class="text-center" style="border: 1px solid #000;">{{ json_decode($purchase->liter)[$index] ?? 'N/A' }}</td>
                                <td class="text-center" style="border: 1px solid #000;">{{ json_decode($purchase->rate)[$index] ?? 'N/A' }}</td>
                                <td class="text-center" style="border: 1px solid #000;">{{ json_decode($purchase->discount)[$index] ?? 'N/A' }}</td>
                                <td class="text-end" style="border: 1px solid #000;">{{ json_decode($purchase->amount)[$index] ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        @php
                            $cartonTotal = collect(json_decode($purchase->carton_qty))->sum();
                            $literTotal = collect(json_decode($purchase->liter))->sum();
                        @endphp
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="6"></td>
                                <td class="fw-bold text-center" style="border: 2px solid #000;">Gross Amount:</td>
                                <td class="fw-bold text-end" style="border: 2px solid #000;">{{ $purchase->gross_total_sum }}</td>
                            </tr>
                            <tr>
                                <td colspan="6"></td>
                                <td class="fw-bold text-center" style="border: 2px solid #000;">Discount:</td>
                                <td class="fw-bold text-end" style="border: 2px solid #000;">{{ $purchase->discount_total_sum }}</td>
                            </tr>
                            <tr>
                                <td colspan="6"></td>
                                <td class="fw-bold text-center" style="border: 2px solid #000;">Net Total:</td>
                                <td class="fw-bold text-end" style="border: 2px solid #000;">{{ $purchase->grand_total }}</td>
                            </tr>
                            <tr>
                                <td colspan="6"></td>
                                <td class="fw-bold text-center" style="border: 2px solid #000;">Carton Total:</td>
                                <td class="fw-bold text-end" style="border: 2px solid #000;">{{ $cartonTotal }}</td>
                            </tr>
                            <tr>
                                <td colspan="6"></td>
                                <td class="fw-bold text-center" style="border: 2px solid #000;">Liter Total:</td>
                                <td class="fw-bold text-end" style="border: 2px solid #000;">{{ $literTotal }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Footer Section -->
                    <div class="row mt-4">
                        <div class="col-md-12 text-start">
                            <h5 class="fw-bold">For FJL Signature</h5>
                            <p>Data Feeder Hyderabad</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print Button (Hidden in Print) -->
            <div class="text-end mt-4 no-print">
                <button onclick="printInvoice()" class="btn btn-danger">
                    <i class="fa fa-print"></i> Print Invoice
                </button>
            </div>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<!-- Print Styles -->

<!-- Print Styles -->
<style>
    tfoot {
        display: table-footer-group;
    }

    tbody tr:last-child td {
        padding-bottom: 10px;
        /* Last row ke niche space */
    }

    tfoot tr:first-child td {
        padding-top: 12px;
        /* Tfoot aur tbody ke beech distance */
    }

    p {
        font-size: 12px;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        #invoice,
        #invoice * {
            visibility: visible;
        }

        #invoice {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        .no-print {
            display: none !important;
        }

        tfoot tr:first-child td {
            padding-top: 15px !important;
            /* Print ke liye extra space */
        }
    }
</style>

<script>
    function printInvoice() {
        window.print();
    }
</script>
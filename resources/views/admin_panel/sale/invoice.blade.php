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
                    <!-- Invoice Title with Bottom Border -->
                    <h3 class="text-center fw-bold my-3"><span style="border-bottom: 2px solid #000;">Sale Invoice</span></h3>

                    <!-- Invoice Details Box (Black Border) -->
                    <div class="p-3 mb-2" style="border: 2px solid #000;">
                        <div class="row">
                            <div class="col-md-6 d-flex flex-column align-items-start">
                                <h5 class="w-100">Customer: {{ $sale->distributor->Customer ?? 'N/A' }}</h5>
                                <h5 class="w-100">Owner: {{ $sale->distributor->Owner ?? 'N/A' }}</h5>
                                <h5 class="w-100">City: {{ $sale->distributor_city }}</h5>
                                <h5 class="w-100">Area: {{ $sale->distributor_area }}</h5>
                                <h5 class="w-100">Phone: {{ $sale->distributor_phone }}</h5>
                            </div>
                            <div class="col-md-6 d-flex flex-column align-items-end">
                                <h5 class="w-100 text-end">Invoice #: {{ $sale->invoice_number }}</h5>
                                <h5 class="w-100 text-end">Sale Date: {{ $sale->Date }}</h5>
                                <h5 class="w-100 text-end">Booker: {{ $sale->Booker }}</h5>
                                <h5 class="w-100 text-end">Salesman: {{ $sale->Saleman }}</h5>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="border: 1px solid #000;">Code</th>
                                <th style="border: 1px solid #000;">Item Description</th>
                                <th style="border: 1px solid #000;">Packing</th>
                                <th style="border: 1px solid #000;">Carton Qty</th>
                                <th style="border: 1px solid #000;">Pcs Qty</th>
                                <th style="border: 1px solid #000;">Liter</th>
                                <th style="border: 1px solid #000;">Rate</th>
                                <th style="border: 1px solid #000;">Disc Rs</th>
                                <th style="border: 1px solid #000;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(json_decode($sale->item) as $index => $item)
                            <tr>
                                <td style="border: 1px solid #000;">{{ json_decode($sale->code)[$index] ?? 'N/A' }}</td>
                                <td style="border: 1px solid #000;">{{ $item }}</td>
                                <td class="text-center" style="border: 1px solid #000;">{{ json_decode($sale->pcs_carton)[$index] ?? '' }}</td>
                                <td class="text-center" style="border: 1px solid #000;">{{ json_decode($sale->carton_qty)[$index] ?? '' }}</td>
                                <td class="text-center" style="border: 1px solid #000;">{{ json_decode($sale->pcs)[$index] ?? '' }}</td>
                                <td class="text-center" style="border: 1px solid #000;">{{ json_decode($sale->liter)[$index] ?? '' }}</td>
                                <td class="text-center" style="border: 1px solid #000;">{{ json_decode($sale->rate)[$index] ?? '' }}</td>
                                <td class="text-center" style="border: 1px solid #000;">{{ json_decode($sale->discount)[$index] ?? '0' }}</td>
                                <td class="text-end" style="border: 1px solid #000;">{{ json_decode($sale->amount)[$index] ?? '' }}</td>
                            </tr>
                            @endforeach

                            @php
                            $closing = $distributorLedger->closing_balance ?? 0;
                            $netAmount = $sale->net_amount ?? 0;
                            $calculatedPrevious = $closing - $netAmount;
                            @endphp

                            @if($distributorLedger)
                            <tr>
                                <td colspan="5"></td>
                                <td class="fw-bold text-danger" colspan="3" style="border: 2px solid #000;">Previous Balance:</td>
                                <td class="fw-bold text-end text-danger" style="border: 2px solid #000;">
                                    {{ number_format($calculatedPrevious, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5"></td>
                                <td class="fw-bold text-danger" colspan="3" style="border: 2px solid #000;">Closing Balance:</td>
                                <td class="fw-bold text-end text-danger" style="border: 2px solid #000;">
                                    {{ number_format($closing, 2) }}
                                </td>
                            </tr>
                            @endif

                        </tbody>
                        @php
                        $cartonTotal = collect(json_decode($sale->carton_qty))->sum();
                        $literTotal = collect(json_decode($sale->liter))->sum();
                        @endphp
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5"></td>
                                <td class="fw-bold" colspan="3" style="border: 2px solid #000;">Gross Amount:</td>
                                <td class="fw-bold text-end" style="border: 2px solid #000;">{{ $sale->grand_total }}</td>
                            </tr>
                            <tr>
                                <td colspan="5"></td>
                                <td class="fw-bold" colspan="3" style="border: 2px solid #000;">Discount Amount:</td>
                                <td class="fw-bold text-end" style="border: 2px solid #000;">{{ $sale->discount_value }}</td>
                            </tr>
                            <tr>
                                <td colspan="5"></td>
                                <td class="fw-bold" colspan="3" style="border: 2px solid #000;">Scheme Amount:</td>
                                <td class="fw-bold text-end" style="border: 2px solid #000;">{{ $sale->scheme_value	 }}</td>
                            </tr>
                            <tr>
                                <td colspan="5"></td>
                                <td class="fw-bold" colspan="3" style="border: 2px solid #000;">Net Amount:</td>
                                <td class="fw-bold text-end" style="border: 2px solid #000;">{{ $sale->net_amount }}</td>
                            </tr>
                            <tr>
                                <td colspan="5"></td>
                                <td class="fw-bold" colspan="3" style="border: 2px solid #000;">Carton Total:</td>
                                <td class="fw-bold text-end" style="border: 2px solid #000;">{{ $cartonTotal }}</td>
                            </tr>
                            <tr>
                                <td colspan="5"></td>
                                <td class="fw-bold" colspan="3" style="border: 2px solid #000;">Liter Total:</td>
                                <td class="fw-bold text-end" style="border: 2px solid #000;">{{ $literTotal }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    <!-- Footer Section -->
                    <div class="row">
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
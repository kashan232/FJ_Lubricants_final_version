<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
        }
        th {
            background: #eee;
        }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>

<body>

<h3 style="text-align:center">Vendor Detailed Ledger Statement</h3>

<p>
    <strong>Vendor:</strong> {{ $vendor->Party_name }} <br>
    <strong>Duration:</strong>
    From {{ date('d-m-Y', strtotime($startDate)) }}
    to
    {{ date('d-m-Y', strtotime($endDate)) }}
</p>

@php
    /* ===== TOTALS (PDF ONLY – SAME AS LEDGER) ===== */
    $totalCartons = 0;
    $totalPcs     = 0;
    $totalLiters  = 0;
    $grandAmount  = 0;   // Rate column total
    $totalDebit   = 0;
    $totalCredit  = 0;
    $balance      = $openingBalance;
@endphp

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>INV-No</th>
            <th>Item</th>
            <th>Description</th>
            <th>Carton</th>
            <th>PCS</th>
            <th>Liters</th>
            <th>Rate</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>Balance</th>
        </tr>
    </thead>

    <tbody>
        {{-- OPENING BALANCE --}}
        <tr>
            <td>{{ date('d-m-Y', strtotime($startDate)) }}</td>
            <td>-</td>
            <td>-</td>
            <td><strong>Opening Balance</strong></td>
            <td>-</td><td>-</td><td>-</td><td>-</td>
            <td>-</td><td>-</td>
            <td class="right"><strong>{{ number_format($balance,2) }}</strong></td>
        </tr>

        @foreach($entries as $e)

            {{-- PURCHASE --}}
            @if($e['type'] === 'purchase')
                @foreach($e['items'] as $i => $item)

                    @php
                        $carton = (float) ($e['cartons'][$i] ?? 0);
                        $pcs    = (float) ($e['pcs'][$i] ?? 0);
                        $liter  = (float) ($e['liters'][$i] ?? 0);
                        $rate   = (float) ($e['rates'][$i] ?? 0);

                        $qty = $carton ?: ($liter ?: ($pcs ?: 1));
                        $debit = $rate * $qty;

                        $totalCartons += $carton;
                        $totalPcs     += $pcs;
                        $totalLiters  += $liter;
                        $grandAmount  += $debit;
                        $totalDebit   += $debit;

                        $balance += $debit;
                    @endphp

                    <tr>
                        <td>{{ date('d-m-Y', strtotime($e['date'])) }}</td>
                        <td>{{ $e['invoice'] }}</td>
                        <td>{{ $item }}</td>
                        <td>To Purchase A/c</td>
                        <td class="center">{{ $carton ?: '-' }}</td>
                        <td class="center">{{ $pcs ?: '-' }}</td>
                        <td class="center">{{ $liter ?: '-' }}</td>
                        <td class="right">{{ $rate }}</td>
                        <td class="right">{{ number_format($debit,2) }}</td>
                        <td>-</td>
                        <td class="right">{{ number_format($balance,2) }}</td>
                    </tr>

                @endforeach
            @endif

            {{-- VENDOR PAYMENT --}}
            @if($e['type'] === 'recovery')
                @php
                    $totalCredit += $e['amount'];
                    $balance -= $e['amount'];
                @endphp
                <tr>
                    <td>{{ date('d-m-Y', strtotime($e['date'])) }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>{{ $e['remarks'] ?? 'Payment' }}</td>
                    <td>-</td><td>-</td><td>-</td><td>-</td>
                    <td>-</td>
                    <td class="right">{{ number_format($e['amount'],2) }}</td>
                    <td class="right">{{ number_format($balance,2) }}</td>
                </tr>
            @endif

            {{-- PURCHASE RETURN --}}
            @if($e['type'] === 'return')
                @php
                    $totalCredit += $e['amount'];
                    $grandAmount -= $e['amount'];
                    $balance -= $e['amount'];
                @endphp
                <tr>
                    <td>{{ date('d-m-Y', strtotime($e['date'])) }}</td>
                    <td>{{ $e['invoice'] }}</td>
                    <td>{{ implode(', ', $e['items'] ?? []) }}</td>
                    <td><strong>Purchase Return</strong></td>
                    <td>-</td><td>-</td><td>-</td><td>-</td>
                    <td>-</td>
                    <td class="right">{{ number_format($e['amount'],2) }}</td>
                    <td class="right">{{ number_format($balance,2) }}</td>
                </tr>
            @endif

            {{-- BUILTY --}}
            @if($e['type'] === 'builty')
                @php
                    $totalDebit += $e['amount'];
                    $balance += $e['amount'];
                @endphp
                <tr>
                    <td>{{ date('d-m-Y', strtotime($e['date'])) }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>{{ $e['description'] ?? 'Builty' }}</td>
                    <td>-</td><td>-</td><td>-</td><td>-</td>
                    <td class="right">{{ number_format($e['amount'],2) }}</td>
                    <td>-</td>
                    <td class="right">{{ number_format($balance,2) }}</td>
                </tr>
            @endif

        @endforeach
    </tbody>

    {{-- TOTALS (EXACT SAME AS LEDGER) --}}
    <tfoot>
        <tr>
            <td colspan="4" class="right"><strong>Totals:</strong></td>
            <td class="center"><strong>{{ $totalCartons }}</strong></td>
            <td class="center"><strong>{{ $totalPcs }}</strong></td>
            <td class="center"><strong>{{ $totalLiters }}</strong></td>
            <td class="right"><strong>Rs. {{ number_format($grandAmount,2) }}</strong></td>
            <td class="right"><strong>Rs. {{ number_format($totalDebit,2) }}</strong></td>
            <td class="right"><strong>Rs. {{ number_format($totalCredit,2) }}</strong></td>
            <td class="right"><strong>Rs. {{ number_format($balance,2) }}</strong></td>
        </tr>
    </tfoot>

</table>

</body>
</html>

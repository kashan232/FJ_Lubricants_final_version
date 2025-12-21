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

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
        }

        th {
            background: #eee;
            font-weight: 700;
        }

        .right {
            text-align: right
        }

        .center {
            text-align: center
        }

        .left {
            text-align: left
        }

        .bold {
            font-weight: bold
        }
    </style>
</head>

<body>

    <h3 style="text-align:center">Customer Detailed Ledger Statement</h3>

    <p>
        <strong>Customer:</strong> {{ $customer->shop_name }} ({{ $customer->customer_name }}) <br>
        <strong>Period:</strong>
        {{ date('d-m-Y', strtotime($startDate)) }}
        to
        {{ date('d-m-Y', strtotime($endDate)) }}
    </p>

    @php
    $balance = $openingBalance;

    $totalDebit = 0;
    $totalCredit = 0;
    $totalCartons = 0;
    $totalPcs = 0;
    $totalLiters = 0;
    $grandAmount = 0;
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
                <td class="bold">Opening Balance</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td class="right bold">{{ number_format($balance,2) }}</td>
            </tr>

            @foreach($entries as $e)

            {{-- ================= SALE ================= --}}
            @if($e['type'] === 'sale')
            @foreach($e['items'] as $i => $item)

            @php
            $carton = $e['cartons'][$i] ?? 0;
            $pcs = $e['pcs'][$i] ?? 0;
            $liter = $e['liters'][$i] ?? 0;
            $rate = $e['rates'][$i] ?? 0;

            $qty = $carton ?: ($liter ?: ($pcs ?: 1));
            $debit = $rate * $qty;

            $balance += $debit;

            $totalDebit += $debit;
            $totalCartons += $carton;
            $totalPcs += $pcs;
            $totalLiters += $liter;
            $grandAmount += $debit;
            @endphp

            <tr>
                <td>{{ date('d-m-Y', strtotime($e['date'])) }}</td>
                <td>{{ $e['invoice'] }}</td>
                <td class="left">{{ $item }}</td>
                <td class="left">To Sale A/c ({{ $e['saleman'] ?? '-' }})</td>
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

            {{-- ================= RECOVERY ================= --}}
            @if($e['type'] === 'recovery')
            @php
            $credit = $e['amount'];
            $balance -= $credit;
            $totalCredit += $credit;
            @endphp

            <tr>
                <td>{{ date('d-m-Y', strtotime($e['date'])) }}</td>
                <td>-</td>
                <td>-</td>
                <td class="left">{{ $e['desc'] ?? '-' }}</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td class="right">{{ number_format($credit,2) }}</td>
                <td class="right">{{ number_format($balance,2) }}</td>
            </tr>
            @endif

            {{-- ================= SALE RETURN ================= --}}
            @if($e['type'] === 'sale_return')
            @php
            $credit = $e['amount'];
            $balance -= $credit;
            $totalCredit += $credit;
            $grandAmount -= $credit;
            @endphp

            <tr>
                <td>{{ date('d-m-Y', strtotime($e['date'])) }}</td>
                <td>{{ $e['invoice'] }}</td>
                <td class="left">{{ implode(', ', $e['items'] ?? []) }}</td>
                <td class="left bold">Sale Return</td>
                <td>{{ implode(', ', $e['cartons'] ?? []) }}</td>
                <td>{{ implode(', ', $e['pcs'] ?? []) }}</td>
                <td>{{ implode(', ', $e['liters'] ?? []) }}</td>
                <td>{{ implode(', ', $e['rates'] ?? []) }}</td>
                <td>-</td>
                <td class="right">{{ number_format($credit,2) }}</td>
                <td class="right">{{ number_format($balance,2) }}</td>
            </tr>
            @endif

            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="4" class="right bold">Totals:</td>
                <td class="center bold">{{ $totalCartons }}</td>
                <td class="center bold">{{ $totalPcs }}</td>
                <td class="center bold">{{ $totalLiters }}</td>
                <td class="right bold">{{ number_format($grandAmount,2) }}</td>
                <td class="right bold">{{ number_format($totalDebit,2) }}</td>
                <td class="right bold">{{ number_format($totalCredit,2) }}</td>
                <td class="right bold">{{ number_format($balance,2) }}</td>
            </tr>
        </tfoot>

    </table>

</body>

</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report - {{ $startDate->format('M d, Y') }} to {{ $endDate->format('M d, Y') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #06448a;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #06448a;
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }
        .summary-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            flex: 1;
            min-width: 200px;
            background: #f9f9f9;
        }
        .card-title {
            font-size: 11px;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .card-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-header {
            background-color: #06448a;
            color: white;
            padding: 10px 15px;
            border-radius: 5px 5px 0 0;
            font-weight: bold;
            margin-bottom: 0;
        }
        .section-content {
            border: 1px solid #ddd;
            border-top: none;
            padding: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background-color: #f8f9fa;
            color: #06448a;
            font-weight: 600;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #ddd;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .positive {
            color: #28a745;
        }
        .negative {
            color: #dc3545;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SAR EQUIP - Financial Report</h1>
        <div class="subtitle">
            Period: {{ $startDate->format('M d, Y') }} to {{ $endDate->format('M d, Y') }}
        </div>
        <div class="subtitle">
            Generated on: {{ $exportDate }}
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="section">
        <h3 class="section-header">Summary Statistics</h3>
        <div class="section-content">
            <div class="summary-cards">
                <div class="card">
                    <div class="card-title">Net Revenue</div>
                    <div class="card-value positive">₱{{ number_format($financialData['profitLoss']['net_revenue'], 2) }}</div>
                </div>
                <div class="card">
                    <div class="card-title">Gross Profit</div>
                    <div class="card-value positive">₱{{ number_format($financialData['profitLoss']['grossProfit'], 2) }}</div>
                </div>
                <div class="card">
                    <div class="card-title">Gross Margin</div>
                    <div class="card-value {{ $financialData['profitLoss']['grossMargin'] >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($financialData['profitLoss']['grossMargin'], 2) }}%
                    </div>
                </div>
                <div class="card">
                    <div class="card-title">Avg. Transaction</div>
                    <div class="card-value">₱{{ number_format($financialData['additionalMetrics']['average_transaction_value'], 2) }}</div>
                </div>
                <div class="card">
                    <div class="card-title">Returns Rate</div>
                    <div class="card-value negative">{{ number_format($financialData['additionalMetrics']['returns_percentage'], 2) }}%</div>
                </div>
                <div class="card">
                    <div class="card-title">Gross COGS</div>
                    <div class="card-value">₱{{ number_format($financialData['profitLoss']['gross_cogs'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit & Loss Summary -->
    <div class="section">
        <h3 class="section-header">Profit & Loss Summary</h3>
        <div class="section-content">
            <table>
                <tbody>
                    <tr>
                        <td><strong>Gross Revenue</strong></td>
                        <td class="text-end">₱{{ number_format($financialData['profitLoss']['gross_revenue'], 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Returns & Refunds</strong></td>
                        <td class="text-end negative">-₱{{ number_format($financialData['profitLoss']['returns_amount'], 2) }}</td>
                    </tr>
                    <tr style="background-color: #f8f9fa;">
                        <td><strong>Net Revenue</strong></td>
                        <td class="text-end positive"><strong>₱{{ number_format($financialData['profitLoss']['net_revenue'], 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Cost of Goods Sold</strong></td>
                        <td class="text-end negative">₱{{ number_format($financialData['profitLoss']['net_cogs'], 2) }}</td>
                    </tr>
                    <tr style="background-color: #f8f9fa;">
                        <td><strong>Gross Profit</strong></td>
                        <td class="text-end positive"><strong>₱{{ number_format($financialData['profitLoss']['grossProfit'], 2) }}</strong></td>
                    </tr>
                    <tr style="background-color: #f8f9fa;">
                        <td><strong>Gross Margin</strong></td>
                        <td class="text-end {{ $financialData['profitLoss']['grossMargin'] >= 0 ? 'positive' : 'negative' }}">
                            <strong>{{ number_format($financialData['profitLoss']['grossMargin'], 2) }}%</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- COGS Analysis by Category -->
    <div class="section">
        <h3 class="section-header">COGS Analysis by Category</h3>
        <div class="section-content">
            @if($financialData['cogsAnalysis']->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="text-end">COGS</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Margin</th>
                        <th class="text-end">Margin %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($financialData['cogsAnalysis'] as $analysis)
                    @php
                        $profit = $analysis->total_revenue - $analysis->total_cogs;
                        $margin = $analysis->total_revenue > 0 ? ($profit / $analysis->total_revenue) * 100 : 0;
                    @endphp
                    <tr>
                        <td>{{ $analysis->category_name }}</td>
                        <td class="text-end">₱{{ number_format($analysis->total_cogs, 2) }}</td>
                        <td class="text-end">₱{{ number_format($analysis->total_revenue, 2) }}</td>
                        <td class="text-center">{{ $analysis->total_quantity }}</td>
                        <td class="text-end {{ $profit >= 0 ? 'positive' : 'negative' }}">
                            ₱{{ number_format($profit, 2) }}
                        </td>
                        <td class="text-end {{ $margin >= 0 ? 'positive' : 'negative' }}">
                            {{ number_format($margin, 2) }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p style="text-align: center; color: #666; padding: 20px;">No COGS data available for the selected period.</p>
            @endif
        </div>
    </div>

    <!-- Payment Methods Analysis -->
    <div class="section">
        <h3 class="section-header">Payment Methods Analysis</h3>
        <div class="section-content">
            @if($financialData['paymentMethods']->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Payment Method</th>
                        <th class="text-center">Transactions</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Average per Transaction</th>
                        <th class="text-center">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalAmount = $financialData['paymentMethods']->sum('total_amount');
                    @endphp
                    @foreach($financialData['paymentMethods'] as $payment)
                    <tr>
                        <td>{{ $payment->payment_method }}</td>
                        <td class="text-center">{{ $payment->transaction_count }}</td>
                        <td class="text-end">₱{{ number_format($payment->total_amount, 2) }}</td>
                        <td class="text-end">₱{{ number_format($payment->total_amount / $payment->transaction_count, 2) }}</td>
                        <td class="text-center">{{ $totalAmount > 0 ? number_format(($payment->total_amount / $totalAmount) * 100, 2) : 0 }}%</td>
                    </tr>
                    @endforeach
                    @if($totalAmount > 0)
                    <tr style="background-color: #f8f9fa;">
                        <td class="text-end"><strong>Total</strong></td>
                        <td class="text-center"><strong>{{ $financialData['paymentMethods']->sum('transaction_count') }}</strong></td>
                        <td class="text-end"><strong>₱{{ number_format($totalAmount, 2) }}</strong></td>
                        <td class="text-end"><strong>₱{{ number_format($totalAmount / $financialData['paymentMethods']->sum('transaction_count'), 2) }}</strong></td>
                        <td class="text-center"><strong>100%</strong></td>
                    </tr>
                    @endif
                </tbody>
            </table>
            @else
            <p style="text-align: center; color: #666; padding: 20px;">No payment data available for the selected period.</p>
            @endif
        </div>
    </div>

    <div class="footer">
        SAR EQUIP Financial Report | Generated on {{ $exportDate }} | Page 1 of 1
    </div>
</body>
</html>
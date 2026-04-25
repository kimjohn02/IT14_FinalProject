<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - Sale #{{ $sale->id }}</title>
    <style>
    /* Key change: Remove fixed height, use auto */
    @page {
        size: 80mm auto;
        margin: 0;
    }
    
    body {
        width: 72mm; /* 80mm total width with 4mm margins each side */
        margin: 0 auto;
        padding: 10px 0;
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 11px;
        line-height: 1.2;
        color: #000;
        /* Remove height constraints */
        min-height: 0;
        height: auto;
    }
    
    /* Keep everything together */
    * {
        page-break-inside: avoid;
        page-break-before: avoid;
        page-break-after: avoid;
    }
    
    .receipt-container {
        width: 100%;
        display: block;
        /* Let content determine height */
        min-height: 100mm; /* Minimum receipt height */
    }
    
    table { 
        width: 100%; 
        border-collapse: collapse; 
        margin: 0;
        padding: 0;
    }
    
    .line { 
        border-top: 1px dashed #000; 
        margin: 5px 0; 
    }
    
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    
    /* Optimize item display */
    .item-name {
        display: inline-block;
        max-width: 45mm;
        word-wrap: break-word;
        white-space: normal;
        line-height: 1.1;
    }
    
    /* Adjust spacing for compact display */
    tr {
        line-height: 1.1;
    }
    
    td, th {
        padding: 2px 0;
        vertical-align: top;
    }
    
    /* Compact the layout when there are few items */
    .compact-mode {
        padding: 5px 0;
    }
    
    .compact-mode .items-table tr {
        padding: 1px 0;
    }
    
    /* Footer stays at bottom */
    .footer {
        margin-top: auto;
        padding-top: 10px;
    }
    .total-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    font-size: 11px;
    margin-top: 6px;
    }

    .total-section .label {
        text-align: left;
    }

    .total-section .amount {
        text-align: right;
        white-space: nowrap;
    }

</style>
</head>
<body>
    <div class="receipt-header text-center">
        <strong>SAR EQUIP</strong><br>
        Door 3 Corner Guerrero St., Ramon Magsaysay Ave.<br>
        Poblacion District, Davao City, 8000 Davao del Sur<br>
        (082) 286 6300
    </div>
    
    <div class="line"></div>
    <div class="text-center"><strong>SALES RECEIPT</strong></div>
    <div class="line"></div>

    <div>
        <strong>Transaction #:</strong> {{ $sale->id }}<br>
        <strong>Date:</strong> {{ $sale->sale_date ? $sale->sale_date->format('M d, Y') : now()->format('M d, Y') }}<br>
        <strong>Time:</strong> {{ $sale->sale_date ? $sale->sale_date->format('h:i A') : now()->format('h:i A') }}<br>
        <strong>Cashier:</strong> {{ $sale->user->full_name ?? 'N/A' }}<br>
        @if($sale->customer_name)
        <strong>Customer:</strong> {{ $sale->customer_name }}<br>
        @endif
        @if($sale->customer_contact)
        <strong>Contact:</strong> {{ $sale->customer_contact }}<br>
        @endif
    </div>

    <div class="line"></div>

    <table class="items-table">
        <thead>
            <tr>
                <th class="col-qty">QTY</th>
                <th class="col-item">ITEM</th>
                <th class="col-total">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td class="col-qty">{{ $item->quantity_sold }}</td>
                <td class="col-item">
                    <span class="item-name">{{ $item->product->name ?? 'N/A' }}</span>
                    @if(isset($item->product->model) && $item->product->model)
                        <br><small style="word-break: break-word; display: inline-block; max-width: 45mm;">Model: {{ $item->product->model }}</small>
                    @endif
                </td>
                <td class="col-total text-right">₱{{ number_format($item->quantity_sold * $item->unit_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    @php
        $total = $sale->items->sum(fn($item) => $item->quantity_sold * $item->unit_price);
        $vatRate = 0.12;
        $vatableSales = $total / (1 + $vatRate);
        $vatAmount = $total - $vatableSales;
    @endphp

    <table class="amount-table">
        <tr>
            <td>AMOUNT DUE:</td>
            <td class="text-right">₱{{ number_format($total, 2) }}</td>
        </tr>
        <tr>
            <td>VAT SALES:</td>
            <td class="text-right">₱{{ number_format($vatableSales, 2) }}</td>
        </tr>
        <tr>
            <td>VAT 12%:</td>
            <td class="text-right">₱{{ number_format($vatAmount, 2) }}</td>
        </tr>
    </table>

    <div class="total-section">
        <span class="label">GRAND TOTAL</span>
        <span class="amount">₱{{ number_format($total, 2) }}</span>
    </div>    

    <div class="line"></div>
    <div class="text-start"><strong>PAYMENT INFO</strong></div>
    <div class="line"></div>

    <div class="payment-info">
        @if($sale->payment)
            @php $payment = $sale->payment; @endphp
            <strong>Method:</strong> {{ $payment->payment_method ?? 'N/A' }}<br>
            <strong>Amount Tendered:</strong> ₱{{ number_format($payment->amount_tendered ?? 0, 2) }}<br>
            <strong>Change Given:</strong> ₱{{ number_format($payment->change_given ?? 0, 2) }}<br>
            @if(!empty($payment->reference_no))
                <strong>Reference #:</strong> {{ $payment->reference_no }}<br>
            @endif
        @else
            <em>No payment data recorded.</em>
        @endif
    </div>

    <div class="line"></div>
    <div class="footer text-center">
        Thank You For Shopping With Us!<br>
        Please Come Again.
    </div>
    <div class="footer text-center" style="font-style: italic; margin-top: 2px;">
        Keep this invoice for return/refund.<br>
        Return/refund within 7 days from purchase date. test
    </div>

    <script>
        window.onload = function () {
            window.print();
            window.onafterprint = function () {
                window.close();
            };
        };
    </script>
    
</body>
</html>
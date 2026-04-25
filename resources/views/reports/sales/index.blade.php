@extends('layouts.app')

@section('title', 'SAR EQUIP - Sales Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4" style="padding-top: 16px;">
    <h2 class="fw-bold">
        <i class="bi bi-graph-up me-2 pt-3"></i>Sales Reports
    </h2>
</div>

<!-- Date Range Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form id="reportFilterForm" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Date Range</label>
                <select class="form-select" name="date_range" id="dateRange">
                    <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $dateRange == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="thisweek" {{ $dateRange == 'thisweek' ? 'selected' : '' }}>This Week</option>
                    <option value="lastweek" {{ $dateRange == 'lastweek' ? 'selected' : '' }}>Last Week</option>
                    <option value="thismonth" {{ $dateRange == 'thismonth' ? 'selected' : '' }}>This Month</option>
                    <option value="lastmonth" {{ $dateRange == 'lastmonth' ? 'selected' : '' }}>Last Month</option>
                    <option value="thisyear" {{ $dateRange == 'thisyear' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>
            <div class="col-md-2" id="customDateRange" style="{{ $dateRange == 'custom' ? 'display: block;' : 'display: none;' }}">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-2" id="customDateRangeEnd" style="{{ $dateRange == 'custom' ? 'display: block;' : 'display: none;' }}">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-5">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-filter me-1"></i>Apply Filter
                </button>
                <button type="button" class="btn btn-outline-success" onclick="exportSummaryPDF()">
                    <i class="bi bi-file-pdf me-1"></i>Export Summary (PDF)
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4"> 
    <div class="col-md-4 mb-3">
        <div class="card report-card border-primary h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <i class="bi bi-receipt text-primary me-2"></i>Total Transactions
                </h6>
                <h3 class="fw-bold text-primary mb-0 text-end">{{ $salesData['summaryStats']->total_transactions ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-card border-success h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <i class="bi bi-box-seam text-primary me-2"></i>Items Sold
                </h6>
                <h3 class="fw-bold text-primary mb-0 text-end">{{ $salesData['summaryStats']->total_items_sold ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-card border-info h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <span class="text-success me-2 fs-5">₱</span>Gross Revenue
                </h6>
                <h3 class="fw-bold text-success mb-0 text-end">₱{{ number_format($salesData['summaryStats']->gross_revenue ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card report-card border-danger h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <i class="bi bi-arrow-return-left text-danger me-2"></i>Total Refunds
                </h6>
                <h3 class="fw-bold text-danger mb-0 text-end">-₱{{ number_format($salesData['summaryStats']->total_returns ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-card border-success border-3 h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <i class="bi bi-wallet2 text-success me-2"></i>Net Revenue
                </h6>
                <h3 class="fw-bold text-success mb-0 text-end">₱{{ number_format($salesData['summaryStats']->net_revenue ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-card border-warning h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <i class="bi bi-calculator text-success me-2"></i>Average Transaction
                </h6>
                <h3 class="fw-bold text-success mb-0 text-end">₱{{ number_format($salesData['summaryStats']->avg_transaction_value ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Sales Reports Content -->
<div class="row">
    <!-- Adaptive Sales Summary -->
    <div class="col-6 mb-3">
        <div class="card report-card h-100">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        @if($daysDiff <= 1)
                            <i class="bi bi-clock me-2"></i>Hourly Sales
                        @elseif($daysDiff <= 31)
                            <i class="bi bi-calendar-day me-2"></i>Daily Sales
                        @elseif($daysDiff <= 365)
                            <i class="bi bi-calendar-month me-2"></i>Monthly Sales
                        @else
                            <i class="bi bi-calendar me-2"></i>Yearly Sales
                        @endif
                    </h5>
                    <small>{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</small>
                </div>
                <div>
                    <span class="text-light">
                        @if($daysDiff <= 1)
                            24 Hours
                        @elseif($daysDiff <= 7)
                            {{ ceil($daysDiff) }} Days
                        @elseif($daysDiff <= 31)
                            {{ ceil($daysDiff / 7) }} Weeks
                        @elseif($daysDiff <= 365)
                            {{ ceil($daysDiff / 30.44) }} Months
                        @else
                            {{ ceil($daysDiff / 365.25) }} Years
                        @endif
                    </span>
                </div>
            </div>
            <div class="card-body">
                @if($salesData['salesSummary']->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>
                                    @if($startDate->isSameDay($endDate))
                                        Hour
                                    @elseif($daysDiff <= 31)
                                        Date
                                    @elseif($daysDiff <= 366)
                                        Month
                                    @else
                                        Year
                                    @endif
                                </th>
                                <th style="width: 120px;" class="text-end">Transactions</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesData['salesSummary'] as $sale)
                            <tr>
                                <td>
                                    @if($startDate->isSameDay($endDate))
                                        {{ $sale->period }} <!-- e.g., "08 AM" -->
                                    @elseif($daysDiff <= 31)
                                        {{ \Carbon\Carbon::parse($sale->period)->format('M d') }}
                                    @elseif($daysDiff <= 366) <!-- Changed from 365 to 366 -->
                                        {{ $sale->period }} <!-- e.g., "January 2024" -->
                                    @else
                                        {{ $sale->period }} <!-- e.g., "2024" -->
                                    @endif
                                </td>
                                <td class="text-end">{{ $sale->transaction_count }}</td>
                                <td class="text-end">₱{{ number_format($sale->total_revenue, 0) }}</td>
                                <td class="text-end">₱{{ $sale->transaction_count > 0 ? number_format($sale->total_revenue / $sale->transaction_count, 0) : 0 }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    @if($salesData['salesSummary']->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            Showing {{ $salesData['salesSummary']->firstItem() }} to {{ $salesData['salesSummary']->lastItem() }} 
                            of {{ $salesData['salesSummary']->total() }} periods
                        </small>
                        <div>
                            {{ $salesData['salesSummary']->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                    @endif
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                    <h6 class="mt-3">No Sales Data</h6>
                    <p class="small">No sales transactions found for this period.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-6 mb-3">
        <div class="card report-card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Payment Methods Analysis</h5>
            </div>
            <div class="card-body">
                @if(isset($salesData['paymentMethods']) && $salesData['paymentMethods']->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Payment Method</th>
                                <th class="text-end">Transactions</th>
                                <th class="text-end">Total Amount</th>
                                <th class="text-end">Average per Transaction</th>
                                <th class="text-end">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalAmount = $salesData['paymentMethods']->sum('total_amount');
                            @endphp
                            @foreach($salesData['paymentMethods'] as $payment)
                            <tr>
                                <td>{{ $payment->payment_method }}</td>
                                <td class="text-end">{{ $payment->transaction_count }}</td>
                                <td class="text-end">₱{{ number_format($payment->total_amount, 2) }}</td>
                                <td class="text-end">₱{{ number_format($payment->total_amount / $payment->transaction_count, 2) }}</td>
                                <td class="text-end">{{ $totalAmount > 0 ? number_format(($payment->total_amount / $totalAmount) * 100, 2) : 0 }}%</td>
                            </tr>
                            @endforeach
                            @if($totalAmount > 0)
                            <tr class="table-light">
                                <td class="fw-bold">Total</td>
                                <td class="text-end fw-bold">{{ $salesData['paymentMethods']->sum('transaction_count') }}</td>
                                <td class="text-end fw-bold">₱{{ number_format($totalAmount, 2) }}</td>
                                <td class="text-end fw-bold">₱{{ number_format($totalAmount / $salesData['paymentMethods']->sum('transaction_count'), 2) }}</td>
                                <td class="text-end fw-bold">100%</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-credit-card " style="font-size: 3rem;"></i>
                    <h5 class="mt-3">No Payment Data</h5>
                    <p>No payment transactions available for the selected period.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card report-card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Top 10 Products by Items Sold</h5>
            </div>
            <div class="card-body">
                @if(count($salesData['topProductsByQuantity']) > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Qty Sold</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Avg Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesData['topProductsByQuantity'] as $product)
                            <tr>
                                <td style="word-break: break-word; max-width: 150px; overflow-wrap: break-word; line-height: 1.4;">
                                    {{ $product->name }}
                                </td>
                                <td class="text-end">{{ $product->total_quantity }}</td>
                                <td class="text-end">₱{{ number_format($product->total_revenue, 2) }}</td>
                                <td class="text-end">₱{{ number_format($product->avg_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-box" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">No Product Sales Data</h5>
                    <p>No product sales found for the selected period.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card report-card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Top 10 Products by Revenue</h5>
            </div>
            <div class="card-body">
                @if(count($salesData['topProductsByRevenue']) > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Qty Sold</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Avg Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesData['topProductsByRevenue'] as $product)
                            <tr>
                                <td style="word-break: break-word; max-width: 150px; overflow-wrap: break-word; line-height: 1.4;">
                                    {{ $product->name }}
                                </td>
                                <td class="text-end">{{ $product->total_quantity }}</td>
                                <td class="text-end">₱{{ number_format($product->total_revenue, 2) }}</td>
                                <td class="text-end">₱{{ number_format($product->avg_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <span style="font-size: 3rem;">₱</span>
                    <h5 class="mt-3">No Revenue Data</h5>
                    <p>No product revenue found for the selected period.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card report-card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Sales by Category</h5>
            </div>
            <div class="card-body">
                @if(count($salesData['categoryAnalysis']) > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Qty Sold</th>
                                <th class="text-end">Transactions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesData['categoryAnalysis'] as $category)
                            <tr>
                                <td style="word-break: break-word; max-width: 150px; overflow-wrap: break-word; line-height: 1.4;">
                                    {{ $category->category_name }}
                                </td>
                                <td class="text-end">₱{{ number_format($category->total_revenue, 2) }}</td>
                                <td class="text-end">{{ $category->total_quantity }}</td>
                                <td class="text-end">{{ $category->transaction_count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-tags" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">No Category Sales Data</h5>
                    <p>No sales found for the selected period.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

   <!-- Detailed Sales Table -->
    <div class="col-12 mb-4">
        <div class="card report-card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Detailed Sales Transactions</h5>
                    <small>Individual sales records for the selected period</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('sales.index') }}" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-list me-1"></i> See All Sales
                    </a>
                    <button type="button" class="btn btn-outline-light btn-sm" onclick="exportDetailedCSV()">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV  
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Sale ID</th>
                                <th>Date & Time</th>
                                <th >Cashier</th>
                                <th class="text-end">Items Sold</th>
                                <th class="text-end">Total Amount</th>
                                <th class="text-center">Payment Method</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesData['detailedSales'] as $sale)
                            <tr>
                                <td><strong>#{{ $sale->id }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y h:i A') }}</td>
                                <td>{{ $sale->f_name ?? 'N/A' }} {{ $sale->l_name ?? '' }}</td>
                                <td class="text-end">{{ $sale->items_count }} items</td>
                                <td class="text-end">₱{{ number_format($sale->total_amount, 2) }}</td>
                                <td class="text-center">{{ $sale->payment_method ?? 'N/A' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info btn-action view-sale" data-id="{{ $sale->id }}" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success btn-action"
                                            title="Print Receipt"
                                            onclick="printSaleReceipt({{ $sale->id }})">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                    <a href="{{ route('sales.receipt', $sale->id) }}" class="btn btn-sm btn-outline-secondary btn-action" title="Download PDF" target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">No Sales Transactions</h5>
                                    <p>No sales records found for the selected period.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($salesData['detailedSales']->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $salesData['detailedSales']->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

<!-- View Sale Modal -->
<div class="modal fade" id="viewSaleModal" tabindex="-1" aria-labelledby="viewSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSaleModalLabel">
                    <i class="bi bi-eye me-2"></i>
                    Sale Details - #<span id="viewSaleId"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Sale Information -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Sale Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item px-0">
                                        <small class="text-muted d-block">Sale ID</small>
                                        <span class="fw-semibold" id="viewSaleNumber"></span>
                                    </div>
                                    <div class="list-group-item px-0">
                                        <small class="text-muted d-block">Date & Time</small>
                                        <span class="fw-semibold" id="viewSaleDate"></span>
                                    </div>
                                    <div class="list-group-item px-0">
                                        <small class="text-muted d-block">Cashier</small>
                                        <span class="fw-semibold" id="viewSaleCashier"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Customer Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item px-0">
                                        <small class="text-muted d-block">Customer Name</small>
                                        <span class="fw-semibold" id="viewSaleCustomer"></span>
                                    </div>
                                    <div class="list-group-item px-0">
                                        <small class="text-muted d-block">Contact</small>
                                        <span class="fw-semibold" id="viewSaleContact"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Items Sold</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="padding-left: 16px;">Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th style="padding-right: 16px;" class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="viewSaleItems">
                                    <!-- Items will be populated by JavaScript -->
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                        <td style="padding-right: 16px;" class="text-end fw-bold text-success" id="viewSaleTotal"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Payment Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush" id="viewSalePayment">
                            <!-- Payment info will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="printReceiptBtn">
                    <i class="bi bi-printer me-1"></i> Reprint Receipt
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .report-card {
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
        transition: transform 0.2s;
    }
    .report-card:hover {
        transform: translateY(-2px);
    }
    .table th {
        background-color: #f8f9fa;
        color: #06448a;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
    // Show/hide custom date range
    document.getElementById('dateRange').addEventListener('change', function() {
        const isCustom = this.value === 'custom';
        document.getElementById('customDateRange').style.display = isCustom ? 'block' : 'none';
        document.getElementById('customDateRangeEnd').style.display = isCustom ? 'block' : 'none';
    });

    function exportSummaryPDF() {
    // Get current filter parameters
    const form = document.getElementById('reportFilterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Show loading state
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';
    btn.disabled = true;
    
    // Trigger PDF download
    window.location.href = `/reports/sales/export-summary?${params.toString()}`;
    
    // Reset button after a delay
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }, 3000);
}

function exportDetailedCSV() {
    // Get current filter parameters
    const form = document.getElementById('reportFilterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Show loading state
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';
    btn.disabled = true;
    
    // Trigger CSV download
    window.location.href = `/reports/sales/export-detailed?${params.toString()}`;
    
    // Reset button after a delay
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }, 3000);
}

    // Auto-submit form when date range changes (except custom)
    document.getElementById('dateRange').addEventListener('change', function() {
        if (this.value !== 'custom') {
            document.getElementById('reportFilterForm').submit();
        }
    });

    // View Sale Details
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.view-sale').forEach(button => {
            button.addEventListener('click', function() {
                const saleId = this.getAttribute('data-id');
                
                fetch(`/sales/${saleId}/details`)
                    .then(response => response.json())
                    .then(sale => {
                        // Update modal title and basic info
                        document.getElementById('viewSaleId').textContent = sale.id;
                        document.getElementById('viewSaleNumber').textContent = '#' + sale.id;
                        document.getElementById('viewSaleDate').textContent = new Date(sale.sale_date).toLocaleString();
                        document.getElementById('viewSaleCashier').textContent = sale.user ? (sale.user.f_name + ' ' + sale.user.l_name) : 'N/A';
                        document.getElementById('viewSaleCustomer').textContent = sale.customer_name || 'N/A';
                        document.getElementById('viewSaleContact').textContent = sale.customer_contact || 'N/A';
                        
                        // Update items table
                        const itemsContainer = document.getElementById('viewSaleItems');
                        itemsContainer.innerHTML = '';
                        
                        let total = 0;
                        sale.items.forEach(item => {
                            const itemTotal = item.quantity_sold * item.unit_price;
                            total += itemTotal;
                            
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                 <td style="padding-left: 16px;">
                                    <div class="fw-medium" style="word-break: break-word;">${item.product ? item.product.name : 'N/A'}</div>
                                </td>
                                <td class="text-center">${item.quantity_sold}</td>
                                <td class="text-end">₱${parseFloat(item.unit_price).toFixed(2)}</td>
                                <td style="padding-right: 16px;" class="text-end">₱${itemTotal.toFixed(2)}</td>
                            `;
                            itemsContainer.appendChild(row);
                        });
                        
                        // Update total
                        document.getElementById('viewSaleTotal').textContent = `₱${total.toFixed(2)}`;
                        
                        // Update payment information
                        const paymentContainer = document.getElementById('viewSalePayment');
                        paymentContainer.innerHTML = '';

                        if (sale.payment) {
                            const payment = sale.payment;
                            paymentContainer.innerHTML = `
                                <div class="list-group-item px-0">
                                    <small class="text-muted d-block">Payment Method</small>
                                    <span class="fw-semibold">${payment.payment_method}</span>
                                </div>
                                <div class="list-group-item px-0">
                                    <small class="text-muted d-block">Amount Tendered</small>
                                    <span class="fw-semibold">₱${parseFloat(payment.amount_tendered).toFixed(2)}</span>
                                </div>
                                <div class="list-group-item px-0">
                                    <small class="text-muted d-block">Change Given</small>
                                    <span class="fw-semibold">₱${parseFloat(payment.change_given).toFixed(2)}</span>
                                </div>
                                ${payment.reference_no ? `
                                <div class="list-group-item px-0">
                                    <small class="text-muted d-block">Reference Number</small>
                                    <span class="fw-semibold">${payment.reference_no}</span>
                                </div>
                                ` : ''}
                            `;
                        } else {
                            paymentContainer.innerHTML = `
                                <div class="list-group-item px-0">
                                    <span class="text-muted">No payment information available</span>
                                </div>
                            `;
                        }
                        
                        // Update print receipt button
                        document.getElementById('printReceiptBtn').onclick = () => {
                            printSaleReceipt(sale.id);
                        };

                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('viewSaleModal'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching sale details:', error);
                        alert('Error loading sale details');
                    });
            });
        });
    });

    function printSaleReceipt(id) {
        const url = "{{ route('sales.receipt.print', ['id' => '__ID__']) }}"
            .replace('__ID__', id);

        const win = window.open(
            url,
            '_blank',
            'width=600,height=600,top=100,left=100,scrollbars=yes'
        );

        if (!win) {
            alert('Popup blocked. Please allow popups for this site.');
            return;
        }

        // Grey overlay (same UX as POS)
        const overlay = document.createElement('div');
        overlay.style = `
            position:fixed;
            inset:0;
            background:rgba(0,0,0,.3);
            z-index:1050;
        `;
        document.body.appendChild(overlay);

        win.onload = () => win.print();

        const timer = setInterval(() => {
            if (win.closed) {
                clearInterval(timer);
                overlay.remove();
            }
        }, 300);
    }

</script>
@endpush
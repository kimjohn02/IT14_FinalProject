@extends('layouts.app')

@section('title', 'SAR EQUIP - Financial Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold" style="color: #06448a;">
        <i class="bi bi-cash-coin me-2"></i>Financial Reports
    </h2>
</div>

<!-- Date Range Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form id="reportFilterForm" method="GET" action="{{ url()->current() }}" class="row g-3 align-items-end">
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
            <div class="col-md-3" id="customDateRange" style="{{ $dateRange == 'custom' ? 'display: block;' : 'display: none;' }}">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-3" id="customDateRangeEnd" style="{{ $dateRange == 'custom' ? 'display: block;' : 'display: none;' }}">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-1"></i>Apply Filter
                </button>
                <button type="button" class="btn btn-outline-success" onclick="exportFullReport()">
                    <i class="bi bi-file-pdf me-1"></i>Export PDF
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card report-card border-success h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <span class="text-success me-2 fs-5">₱</span>Net Revenue
                </h6>
                <h3 class="fw-bold text-success mb-0 text-end">₱{{ number_format($financialData['profitLoss']['net_revenue'], 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-card border-primary h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <i class="bi bi-graph-up-arrow text-primary me-2"></i>Gross Profit
                </h6>
                <h3 class="fw-bold text-primary mb-0 text-end">₱{{ number_format($financialData['profitLoss']['grossProfit'], 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-card border-info h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <i class="bi bi-percent text-info me-2"></i>Gross Margin
                </h6>
                <h3 class="fw-bold text-info mb-0 text-end">{{ number_format($financialData['profitLoss']['grossMargin'], 2) }}%</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-card border-warning h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <i class="bi bi-receipt text-warning me-2"></i>Avg. Transaction
                </h6>
                <h3 class="fw-bold text-warning mb-0 text-end">₱{{ number_format($financialData['additionalMetrics']['average_transaction_value'], 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-card border-danger h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <i class="bi bi-arrow-return-left text-danger me-2"></i>Returns Rate
                </h6>
                <h3 class="fw-bold text-danger mb-0 text-end">{{ number_format($financialData['additionalMetrics']['returns_percentage'], 2) }}%</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-card border-secondary h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <i class="bi bi-box-seam text-secondary me-2"></i>Gross COGS
                </h6>
                <h3 class="fw-bold text-secondary mb-0 text-end">₱{{ number_format($financialData['profitLoss']['gross_cogs'], 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card report-card">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Profit & Loss Summary</h5>
                    <small>{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</small>
                </div>
                <button type="button" class="btn btn-outline-light btn-sm" onclick="exportProfitLoss()">
                    <i class="bi bi-file-pdf me-1"></i>Export
                </button>
            </div>
            <div class="card-body">
                <div class="financial-summary">
                    <div class="financial-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Gross Revenue</span>
                            <span class="fw-bold">₱{{ number_format($financialData['profitLoss']['gross_revenue'], 2) }}</span>
                        </div>
                    </div>
                    <div class="financial-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Returns & Refunds</span>
                            <span class="fw-bold text-danger">-₱{{ number_format($financialData['profitLoss']['returns_amount'], 2) }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="financial-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Net Revenue</span>
                            <span class="fw-bold text-success">₱{{ number_format($financialData['profitLoss']['net_revenue'], 2) }}</span>
                        </div>
                    </div>
                    <div class="financial-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Cost of Goods Sold</span>
                            <span class="fw-bold text-danger">₱{{ number_format($financialData['profitLoss']['net_cogs'], 2) }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="financial-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Gross Profit</span>
                            <span class="fw-bold text-primary">₱{{ number_format($financialData['profitLoss']['grossProfit'], 2) }}</span>
                        </div>
                    </div>
                    <div class="financial-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Gross Margin</span>
                            <span class="fw-bold {{ $financialData['profitLoss']['grossMargin'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($financialData['profitLoss']['grossMargin'], 2) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="card report-card">
            <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">COGS Analysis by Category</h5>
                <button type="button" class="btn btn-outline-light btn-sm" onclick="exportCogsAnalysis()">
                    <i class="bi bi-file-pdf me-1"></i>Export
                </button>
            </div>
            <div class="card-body">
                @if($financialData['cogsAnalysis']->count() > 0)
                <div class="table-responsive">
                    <table class="table">
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
                                <td class="text-end {{ $profit >= 0 ? 'text-success' : 'text-danger' }}">
                                    ₱{{ number_format($profit, 2) }}
                                </td>
                                <td class="text-end {{ $margin >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($margin, 2) }}%
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-pie-chart text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">No COGS Data</h5>
                    <p class="text-muted">No sales data available for the selected period.</p>
                </div>
                @endif
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
       function toggleCustomDateRange() {
        const dateRange = document.getElementById('dateRange');
        const isCustom = dateRange.value === 'custom';
        
        document.getElementById('customDateRange').style.display = isCustom ? 'block' : 'none';
        document.getElementById('customDateRangeEnd').style.display = isCustom ? 'block' : 'none';
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initial toggle
        toggleCustomDateRange();
        
        // Add change event listener to date range
        const dateRangeSelect = document.getElementById('dateRange');
        if (dateRangeSelect) {
            dateRangeSelect.addEventListener('change', function() {
                toggleCustomDateRange();
                
                if (this.value !== 'custom') {
                    document.getElementById('reportFilterForm').submit();
                }
            });
        }
    });

    // Export functions (keep your existing ones)
    function exportFullReport() {
        const form = document.getElementById('reportFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        
        const btn = event.target;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';
        btn.disabled = true;
        
        window.location.href = `/reports/financial/export-full?${params.toString()}`;
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }, 3000);
    }

    function exportProfitLossCSV() {
        const form = document.getElementById('reportFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        
        const btn = event.target;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';
        btn.disabled = true;
        
        window.location.href = `/reports/financial/export-profit-loss?${params.toString()}`;
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }, 3000);
    }

    function exportCogsAnalysisCSV() {
        const form = document.getElementById('reportFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        
        const btn = event.target;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';
        btn.disabled = true;
        
        window.location.href = `/reports/financial/export-cogs-analysis?${params.toString()}`;
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }, 3000);
    }

</script>
@endpush
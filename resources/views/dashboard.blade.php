@extends('layouts.app')

@section('title', 'SAR EQUIP - Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold" style="color: #06448a;">Dashboard</h2>
    <div class="d-flex">
        <div class="dropdown me-2">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-filter me-1"></i> 
                <span id="currentFilterLabel">
                    @if(request('filter_type') == 'custom')
                        Custom: {{ request('start_date') }} to {{ request('end_date') }}
                    @else
                        {{ ucfirst(str_replace('_', ' ', request('filter', 'this month'))) }}
                    @endif
                </span>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item filter-option" href="#" data-filter="today">Today</a></li>
                <li><a class="dropdown-item filter-option" href="#" data-filter="this_week">This Week</a></li>
                <li><a class="dropdown-item filter-option" href="#" data-filter="this_month">This Month</a></li>
                <li><a class="dropdown-item filter-option" href="#" data-filter="last_month">Last Month</a></li>
                <li><a class="dropdown-item filter-option" href="#" data-filter="this_year">This Year</a></li>
                <li><a class="dropdown-item filter-option" href="#" data-filter="all_time">All Time</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#customDateModal">Custom Range</a></li>
            </ul>
        </div>
        @if(request('filter_type') == 'custom' || request('filter'))
        <a href="{{ route('dashboard') }}" class="btn btn-outline-danger">
            <i class="bi bi-x-circle me-1"></i> Clear Filter
        </a>
        @endif
    </div>
</div>

@if($lowStockAlerts['out_of_stock_count'] > 0)
<div class="alert alert-danger d-flex align-items-center mb-4">
    <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
    <div class="flex-grow-1">
        <strong>Urgent: {{ $lowStockAlerts['out_of_stock_count'] }} product(s) out of stock!</strong>
        <p class="mb-0">Restock immediately to prevent lost sales.</p>
    </div>
    <a href="{{ route('products.index') }}?stock_filter=out_of_stock" class="btn btn-outline-danger">
        <i class="bi bi-box-arrow-in-down me-1"></i> Restock Now
    </a>
</div>
@endif


<!-- KEY METRICS -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <a href="{{ route('reports.sales.index') }}" class="text-decoration-none">
            <div class="card dashboard-card bg-success text-white h-100 clickable-card">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-cash-stack me-2" style="font-size: 1.8rem; opacity: 0.8;"></i>
                        <span class="stat-label m-0">Total Revenue</span>
                    </div>
                    <div class="stat-value text-end m-0">
                        ₱{{ number_format($totalRevenue, 0) }}
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-3 mb-3">
        <a href="{{ route('reports.sales.index') }}" class="text-decoration-none">
            <div class="card dashboard-card bg-success text-white h-100">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-graph-up me-2" style="font-size: 1.8rem; opacity: 0.8;"></i>
                        <span class="stat-label m-0">Gross Profit</span>
                    </div>
                    <div class="stat-value text-end m-0">
                        ₱{{ number_format($grossProfit, 0) }}
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 mb-3">
        <a href="{{ route('reports.sales.index') }}" class="text-decoration-none">
            <div class="card dashboard-card bg-secondary text-white h-100">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-receipt me-2" style="font-size: 1.8rem; opacity: 0.8;"></i>
                        <span class="stat-label m-0">Total Transactions</span>
                    </div>
                    <div class="stat-value text-end m-0">
                        {{ number_format($totalTransactions, 0) }}
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-3 mb-3">
        <a href="{{ route('reports.sales.index') }}" class="text-decoration-none">
            <div class="card dashboard-card bg-secondary text-white h-100">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-cart-check me-2" style="font-size: 1.8rem; opacity: 0.8;"></i>
                        <span class="stat-label m-0">Average Order</span>
                    </div>
                    <div class="stat-value text-end m-0">
                        ₱{{ number_format($averageOrderValue, 0) }}
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!--
<div class="row mb-3">
    <div class="col-md-4 mb-3">
        <a href="{{ route('reports.inventory.index') }}" class="text-decoration-none">
            <div class="card dashboard-card bg-secondary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="stat-label">Inventory Value</span>
                        <i class="bi bi-box-seam" style="font-size: 1.2rem; opacity: 0.8;"></i>
                    </div>
                    <div class="stat-value text-end" style="font-size: 1.6rem; font-weight: bold;">
                        ₱{{ number_format($inventoryValue, 0) }}
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4 mb-3">
        <a href="{{ route('products.index') }}?stock_filter=low_stock" class="text-decoration-none">
            <div class="card dashboard-card {{ $lowStockAlerts['total_count'] > 0 ? 'bg-warning text-white' : 'bg-success text-white' }} h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="stat-label">Low Stock Alerts</span>
                        <i class="bi bi-exclamation-triangle" style="font-size: 1.2rem; opacity: 0.8;"></i>
                    </div>
                    <div class="stat-value text-start" style="font-size: 1.6rem; font-weight: bold;">
                        {{ $lowStockAlerts['total_count'] }}
                    </div>
                    @if($lowStockAlerts['out_of_stock_count'] > 0)
                        <small class="opacity-75 d-block mt-1" style="font-size: 0.8rem;">
                            {{ $lowStockAlerts['out_of_stock_count'] }} out of stock
                        </small>
                    @endif
                </div>
            </div>
        </a>
    </div>
</div>
-->

<!-- CHARTS SECTION -->
<div class="row mb-4">
    <div class="col-md-8 mb-3">
        <div class="card dashboard-card">
            <div class="card-header d-flex justify-content-between align-items-center">
            <span>Sales Overview</span>
            <div class="btn-group btn-group-sm" id="salesChartType">
                @php
                    $filter = request('filter', 'this_month');
                @endphp
                
                @if($filter === 'today')
                <button type="button" class="btn btn-outline-light {{ ($currentChartType ?? '') === 'hourly' ? 'active' : '' }}" 
                        data-type="hourly">By Hour</button>
                @endif
                
                @if(!in_array($filter, ['today']))
                <button type="button" class="btn btn-outline-light {{ ($currentChartType ?? '') === 'daily' ? 'active' : '' }}" 
                        data-type="daily">By Day</button>
                @endif
                
                @if(!in_array($filter, ['today', 'this_week']))
                <button type="button" class="btn btn-outline-light {{ ($currentChartType ?? '') === 'weekly' ? 'active' : '' }}" 
                        data-type="weekly">By Week</button>
                @endif
                
                @if(!in_array($filter, ['today', 'this_week', 'this_month', 'last_month']))
                <button type="button" class="btn btn-outline-light {{ ($currentChartType ?? '') === 'monthly' ? 'active' : '' }}" 
                        data-type="monthly">By Month</button>
                @endif
                
                @if(in_array($filter, ['all_time']))
                <button type="button" class="btn btn-outline-light {{ ($currentChartType ?? '') === 'yearly' ? 'active' : '' }}" 
                        data-type="yearly">By Year</button>
                @endif
            </div>
        </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Items Sold by Category Chart Section -->
    <div class="col-md-4 mb-3">
        <a href="{{ route('reports.sales.index') }}" class="text-decoration-none chart-clickable">
            <div class="card dashboard-card">
                <div class="card-header">Items Sold by Category</div>
                <div class="card-body">
                    @empty($categorySales['data'])
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-pie-chart fs-1 opacity-50 mb-3"></i>
                            <p class="mb-0">No items sold by category in this period</p>
                        </div>
                    @else
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    @endempty
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8 mb-3">
        <a href="{{ route('reports.sales.index') }}" class="text-decoration-none chart-clickable">
            <div class="card dashboard-card">
                <div class="card-header">Top 5 Bestselling Products</div>
                <div class="card-body">
                    @empty($topProducts['data'])
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-bar-chart fs-1 opacity-50 mb-3"></i>
                            <p class="mb-0">No bestselling products in this period</p>
                        </div>
                    @else
                        <div class="chart-container">
                            <canvas id="topProductsChart"></canvas>
                        </div>
                    @endempty
                </div>
            </div>
        </a>
    </div>

    <!-- Payment Methods Pie Chart (col-6) -->
    <div class="col-md-4 mb-3">
        <a href="{{ route('reports.sales.index') }}" class="text-decoration-none chart-clickable">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Payment Methods Distribution</span>
                    <span class="badge bg-success">₱{{ number_format($paymentMethods->sum('total_amount'), 0) }}</span>
                </div>
                <div class="card-body">
                    @empty($paymentMethods)
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-credit-card fs-1 opacity-50 mb-3"></i>
                            <p class="mb-0">No payment data in this period</p>
                        </div>
                    @else
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="paymentMethodsChart"></canvas>
                        </div>
                    @endempty
                </div>
            </div>
        </a>
    </div>
</div>

<!-- TABLES SECTION -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <a href="{{ route('products.index') }}?stock_filter=low_stock" class="text-decoration-none chart-clickable">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Low Stock Alerts</span>
                    <span class="badge bg-danger">{{ $lowStockAlerts['total_count'] }} Alerts</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th class="text-end">Current Stock</th>
                                    <th class="text-end">Reorder Level</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockAlerts['alerts'] as $product)
                                    <tr class="{{ $product->current_stock == 0 ? 'out-of-stock' : 'low-stock' }}">
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->category_name }}</td>
                                        <td class="text-end">{{ $product->current_stock }}</td>
                                        <td class="text-end">{{ $product->reorder_level }}</td>
                                        <td>
                                            @if($product->current_stock == 0)
                                                <span class="fw-bold text-danger">Out of Stock</span>
                                            @else
                                                <span class="fw-bold text-warning">Low Stock</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No low stock alerts</td>
                                    </tr>
                                @endforelse
                                
                                @if($lowStockAlerts['total_count'] > 10)
                                    <tr class="bg-light">
                                        <td colspan="5" class="text-center py-2">
                                            <small class="text-muted">
                                                Showing 10 of {{ $lowStockAlerts['total_count'] }} alerts. 
                                                <a href="{{ route('products.index') }}?stock_filter=low_stock" class="text-primary">View all</a>
                                            </small>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 mb-3">
        <a href="{{ route('stock-adjustments.index') }}" class="text-decoration-none chart-clickable">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recent Adjustments</span>
                    <span class="badge bg-warning">{{ $recentAdjustments->count() }} Recent</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Qty</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAdjustments as $adjustment)
                                    <tr class="{{ $adjustment->quantity_change < 0 ? 'table-danger' : 'table-info' }}">
                                        <td>{{ $adjustment->adjustment_date->format('M d, H:i') }}</td>
                                        <td>{{ $adjustment->product_name }}</td>
                                        <td>{{ $adjustment->adjustment_type }}</td>
                                        <td>
                                            <span class="{{ $adjustment->quantity_change < 0 ? 'text-danger' : 'text-success' }}">
                                                {{ $adjustment->quantity_change > 0 ? '+' : '' }}{{ $adjustment->quantity_change }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ Str::limit($adjustment->reason_notes, 10) }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No recent adjustments</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- QUICK ACTIONS SECTION -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card dashboard-card">
            <div class="card-header">Quick Actions</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 col-4 mb-3">
                        <a href="{{ route('pos.index') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-cart-plus fs-4 mb-2"></i>
                            <span>New Sale</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <a href="{{ route('products.create') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-plus-circle fs-4 mb-2"></i>
                            <span>Add Product</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <a href="{{ route('stock-ins.create') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-box-arrow-in-down fs-4 mb-2"></i>
                            <span>Stock In</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <a href="{{ route('stock-adjustments.create') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-sliders fs-4 mb-2"></i>
                            <span>Adjust Stock</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <a href="{{ route('reports.sales.index') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-graph-up fs-4 mb-2"></i>
                            <span>Sales Reports</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <a href="{{ route('reports.inventory.index') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-box-seam fs-4 mb-2"></i>
                            <span>Inventory Reports</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CUSTOM DATE MODAL -->
<div class="modal fade" id="customDateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Custom Date Range</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('dashboard') }}">
                <div class="modal-body">
                    <input type="hidden" name="filter_type" value="custom">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                            value="{{ request('start_date', Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}" 
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                            value="{{ request('end_date', Carbon\Carbon::now()->format('Y-m-d')) }}" 
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .dashboard-card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        overflow: hidden;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    
    .card-header {
        background-color: #06448a;
        color: white;
        font-weight: 600;
        border-bottom: none;
    }
    
    .stat-value {
        font-size: 1.4rem !important;  
        font-weight: 700;
        margin: 5px 0; 
    }

    .stat-label {
        font-size: 0.7rem !important; 
        color: rgba(255, 255, 255, 0.9);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-icon {
        font-size: 1.5rem !important; 
        margin-bottom: 10px;  
        opacity: 0.9;
    }

    
    .low-stock {
        background-color: rgba(255, 193, 7, 0.1);
        border-left: 4px solid #ffc107;
    }
    
    .out-of-stock {
        background-color: rgba(220, 53, 69, 0.1);
        border-left: 4px solid #dc3545;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: #06448a;
    }
    
    .badge-low {
        background-color: #ffc107;
        color: #212529;
    }
    
    .badge-out {
        background-color: #e20615;
        color: white;
    }

    .quick-action-btn {
        height: 100px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .quick-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .btn-group-sm .btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-group-sm .btn {
        position: relative;
    }

    .btn-group-sm .btn[title]:hover::after {
        content: attr(title);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #333;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/vendor/chart.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let salesChart;
        let topProductsChart;
        let categoryChart;
        let paymentMethodsChart; 

        // Filter functionality
        const filterOptions = document.querySelectorAll('.filter-option');
        const currentFilterLabel = document.getElementById('currentFilterLabel');
        
        filterOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                const filter = this.getAttribute('data-filter');
                
                // Update URL with filter parameter
                const url = new URL(window.location.href);
                url.searchParams.set('filter', filter);
                url.searchParams.delete('filter_type');
                url.searchParams.delete('start_date');
                url.searchParams.delete('end_date');
                
                window.location.href = url.toString();
            });
        });

        // Date validation for custom range
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        
        if (startDateInput && endDateInput) {
            startDateInput.addEventListener('change', function() {
                endDateInput.min = this.value;
                if (new Date(endDateInput.value) < new Date(this.value)) {
                    endDateInput.value = this.value;
                }
            });
            
            endDateInput.addEventListener('change', function() {
                if (new Date(this.value) < new Date(startDateInput.value)) {
                    this.value = startDateInput.value;
                }
            });
        }

        // Sales Chart Type Selector
        const salesChartTypeButtons = document.querySelectorAll('#salesChartType button');
        salesChartTypeButtons.forEach(button => {
            button.addEventListener('click', function() {
                if (this.classList.contains('disabled')) return;
                
                // Remove active class from all buttons
                salesChartTypeButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update chart data based on selected type
                const chartType = this.getAttribute('data-type');
                updateSalesChart(chartType);
            });
        });

        // Initialize Charts
        initializeCharts();

        function initializeCharts() {
            // Sales Chart (Revenue Trend)
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: @json($salesTrend['labels']),
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: @json($salesTrend['data']),
                        borderColor: '#06448a',
                        backgroundColor: 'rgba(6, 68, 138, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Revenue: ₱${context.parsed.y.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Top Products Chart (Bar Chart)
            const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
            topProductsChart = new Chart(topProductsCtx, {
                type: 'bar',
                data: {
                    labels: @json($topProducts['labels']),
                    datasets: [{
                        label: 'Quantity Sold',
                        data: @json($topProducts['data']),
                        backgroundColor: [
                            '#06448a',  '#28a745',  '#ffc107',  '#dc3545',  '#6f42c1',  
                            '#17a2b8',  '#fd7e14',  '#20c997',  '#e83e8c',  '#6c757d'   
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            },
                            ticks: {
                                precision: 0 
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Payment Methods Chart (Pie/Doughnut)
            const paymentCtx = document.getElementById('paymentMethodsChart').getContext('2d');
            paymentMethodsChart = new Chart(paymentCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($paymentMethods->pluck('payment_method')),
                    datasets: [{
                        data: @json($paymentMethods->pluck('total_amount')),
                        backgroundColor: [
                            '#28a745',  // Cash - Green
                            '#06448a',  // Card - Blue
                            '#6f42c1',  // GCash - Purple
                        ],
                        borderWidth: 1,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = Number(context.raw) || 0; 
                                    const total = context.dataset.data
                                        .map(x => Number(x))     
                                        .reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ₱${value.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });

            // Category Chart (Pie Chart)
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($categorySales['labels']),
                    datasets: [{
                        data: @json($categorySales['data']),
                        backgroundColor: [
                            '#06448a',
                            '#28a745',
                            '#ffc107',
                            '#dc3545',
                            '#6f42c1',
                            '#17a2b8',
                            '#6c757d'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function updateSalesChart(chartType) {
            // Get current filter parameters
            const urlParams = new URLSearchParams(window.location.search);
            const filter = urlParams.get('filter');
            const filterType = urlParams.get('filter_type');
            const startDate = urlParams.get('start_date');
            const endDate = urlParams.get('end_date');

            // Show loading state
            const salesChartCanvas = document.getElementById('salesChart');
            salesChartCanvas.style.opacity = '0.5';

            // Fetch updated chart data
            fetch(`/dashboard/sales-chart-data?chart_type=${chartType}&filter=${filter}&filter_type=${filterType}&start_date=${startDate}&end_date=${endDate}`)
                .then(response => response.json())
                .then(data => {
                    // Update chart data
                    salesChart.data.labels = data.labels;
                    salesChart.data.datasets[0].data = data.data;
                    salesChart.update();
                    
                    // Restore opacity
                    salesChartCanvas.style.opacity = '1';
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                    salesChartCanvas.style.opacity = '1';
                    alert('Error loading chart data. Please try again.');
                });
        }
    });
</script>
@endpush
@extends('layouts.app')

@section('title', 'SAR EQUIP - Inventory Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4" style="padding-top: 16px;">
    <h2 class="fw-bold">
        <i class="bi bi-box-seam me-2"></i>Inventory Reports
    </h2>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card report-card border-primary h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <i class="bi bi-box text-primary me-2"></i>Total Products
                </h6>
                <h3 class="fw-bold text-primary mb-0 text-end">{{ $inventoryData['summaryStats']->total_products ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-card border-success h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <i class="bi bi-layers text-primary me-2"></i>Total Quantity
                </h6>
                <h3 class="fw-bold text-primary mb-0 text-end">{{ $inventoryData['summaryStats']->total_quantity ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-card border-primary h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <span class="text-success me-2 fs-5">₱</span>Inventory Value
                </h6>
                <h3 class="fw-bold text-success mb-0 text-end">₱{{ number_format($inventoryData['summaryStats']->total_inventory_value ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-card border-danger h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>Low Stock Items
                </h6>
                <h3 class="fw-bold text-danger mb-0 text-end">{{ $inventoryData['summaryStats']->low_stock_count ?? 0 }}</h3>
                <small class="text-muted text-end d-block">{{ $inventoryData['summaryStats']->out_of_stock_count ?? 0 }} out of stock</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-card border-info h-100">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">
                    <i class="bi bi-repeat text-primary me-2"></i>Turnover Rate
                </h6>
                <h3 class="fw-bold text-primary mb-0 text-end">{{ $inventoryData['inventoryTurnover']['turnover_rate'] ?? 0 }}x</h3>
                <small class="text-muted text-end d-block">{{ $inventoryData['inventoryTurnover']['period'] ?? '' }}</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <div class="card report-card">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Low Stock Alerts</h5>
                </div>
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="exportLowStockCSV()">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
                </button>
            </div>
            <div class="card-body">
                @if($inventoryData['lowStockAlerts']->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th class="text-end">Current Stock</th>
                                <th class="text-end">Reorder Level</th>
                                <th class="text-end">Unit Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryData['lowStockAlerts'] as $product)
                            <tr class="{{ $product->quantity_in_stock == 0 ? 'out-of-stock' : 'low-stock' }}">
                                <td style="word-break: break-word; max-width: 150px; overflow-wrap: break-word;">
                                    {{ $product->name }}
                                </td>
                                <td style="word-break: break-word; max-width: 120px; overflow-wrap: break-word;">
                                    {{ $product->category_name }}
                                </td>
                                <td class="text-end">{{ $product->quantity_in_stock }}</td>
                                <td class="text-end">{{ $product->reorder_level }}</td>
                                <td class="text-end">₱{{ number_format($product->latest_unit_cost, 2) }}</td>
                                <td>
                                    @if($product->quantity_in_stock == 0)
                                        <span class="fw-bold text-danger">Out of Stock</span>
                                    @else
                                        <span class="fw-bold text-warning">Low Stock</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($inventoryData['lowStockAlerts']->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $inventoryData['lowStockAlerts']->links('pagination::bootstrap-4') }}
                    </div>
                    @endif
                @else
                <div class="text-center py-4">
                    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">No Low Stock Alerts</h5>
                    <p class="text-muted">All products are sufficiently stocked.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card report-card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div><h5 class="mb-0">Recent Stock Ins</h5></div>
                <div>
                    <a href="{{ route('stock-ins.index') }}" class="btn btn-outline-light btn-sm me-2">
                        <i class="bi bi-list-ul me-1"></i> View All
                    </a>
                    <button type="button" class="btn btn-outline-light btn-sm" onclick="exportStockMovementCSV()">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($inventoryData['stockMovement']->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Date</th>
                                <th class="text-end">Qty Received</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryData['stockMovement'] as $movement)
                            <tr>
                                 <td style="word-break: break-word; max-width: 150px; overflow-wrap: break-word;">
                                    {{ $movement->name }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($movement->stock_in_date)->format('M d, Y') }}</td>
                                <td class="text-end">{{ $movement->quantity_received }}</td>
                                <td class="text-end">₱{{ number_format($movement->actual_unit_cost, 2) }}</td>
                                <td class="text-end">₱{{ number_format($movement->total_cost, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">No Recent Stock Ins</h5>
                    <p class="text-muted">No stock received recently.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card report-card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Inventory Valuation by Category</h5>
                <button type="button" class="btn btn-outline-light btn-sm" onclick="exportValuationCSV()">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
                </button>
            </div>
            <div class="card-body">
                @if($inventoryData['valuationReport']->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Products</th>
                                <th class="text-end">Total Quantity</th>
                                <th class="text-end">Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryData['valuationReport'] as $valuation)
                            <tr>
                                <td style="word-break: break-word; max-width: 150px; overflow-wrap: break-word;">
                                    {{ $valuation->category_name }}
                                </td>
                                <td class="text-end">{{ $valuation->product_count }}</td>
                                <td class="text-end">{{ $valuation->total_quantity }}</td>
                                <td class="text-end">₱{{ number_format($valuation->total_value, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-pie-chart text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">No Valuation Data</h5>
                    <p class="text-muted">No products available for valuation.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card report-card">
            <div class="card-header bg-secondary text-white d-flex justify-content-between">
                <div><h5 class="mb-0">Recent Stock Adjustments</h5></div>
                <div>
                    <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-light btn-sm me-2">
                        <i class="bi bi-list-ul me-1"></i> View All
                    </a>
                    <button type="button" class="btn btn-outline-light btn-sm" onclick="exportAdjustmentsCSV()">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if(count($inventoryData['stockAdjustments']) > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th class="text-end">Change</th>
                                <th>Type</th>
                                <th>Processed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryData['stockAdjustments'] as $adj)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($adj->adjustment_date)->format('M d, Y') }}</td>
                                <td style="word-break: break-word; max-width: 150px; overflow-wrap: break-word;">
                                    {{ $adj->product_name }}
                                </td>
                                <td class="text-end">
                                    @if($adj->quantity_change > 0)
                                        <span class="text-success fw-bold">+{{ $adj->quantity_change }}</span>
                                    @else
                                        <span class="text-danger fw-bold">{{ $adj->quantity_change }}</span>
                                    @endif
                                </td>
                                <td>{{ $adj->adjustment_type }}</td>
                                <td>{{ $adj->processed_by }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-sliders" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">No Stock Adjustments</h5>
                    <p>No stock adjustments found for the selected period.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card report-card">
            <div class="card-header bg-danger text-white d-flex justify-content-between">
                <h5 class="mb-0">Returns Stock Impact</h5>
                <button type="button" class="btn btn-outline-light btn-sm" onclick="exportReturnsCSV()">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
                </button>
            </div>
            <div class="card-body">
                @if(count($inventoryData['returns']) > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th class="text-end">Qty Returned</th>
                                <th>Stock Impact</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryData['returns'] as $ret)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($ret->created_at)->format('M d, Y') }}</td>
                                <td style="word-break: break-word; max-width: 150px; overflow-wrap: break-word;">
                                    {{ $ret->product_name }}
                                </td>
                                <td class="text-end">{{ $ret->quantity_returned }}</td>
                                <td>
                                    @if($ret->inventory_adjusted)
                                        <span class="text-success">Added Back</span>
                                    @else
                                        <span class="text-danger">Not Added (Loss)</span>
                                    @endif
                                </td>
                                <td>{{ $ret->return_reason }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-arrow-return-left" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">No Product Returns</h5>
                    <p>No product returns found for the selected period.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card report-card">
            <div class="card-header bg-success text-white d-flex justify-content-between">
                <h5 class="mb-0">Top 10 Products by Sales Volume</h5>
                <button type="button" class="btn btn-outline-light btn-sm" onclick="exportBestSellersCSV()">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
                </button>
            </div>
            <div class="card-body">
                @if(count($inventoryData['bestSellers']) > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th class="text-end">Qty Sold</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Avg Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryData['bestSellers'] as $product)
                            <tr>
                                <td style="word-break: break-word; max-width: 150px; overflow-wrap: break-word;">
                                    {{ $product->name }}
                                </td>
                                <td><small class="text-muted">{{ $product->sku }}</small></td>
                                <td class="text-end">{{ $product->total_quantity_sold }}</td>
                                <td class="text-end">₱{{ number_format($product->total_revenue, 2) }}</td>
                                <td class="text-end">₱{{ number_format($product->avg_selling_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-trophy" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">No Best Sellers Data</h5>
                    <p>No product sales data available for the selected period.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card report-card">
            <div class="card-header bg-danger text-white d-flex justify-content-between">
                <h5 class="mb-0">Dead Stock / Slow Movers</h5>
                <button type="button" class="btn btn-outline-light btn-sm" onclick="exportDeadStockCSV()">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
                </button>
            </div>
            <div class="card-body">
                @if($inventoryData['deadStock']->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th class="text-end">Current Stock</th>
                                <th class="text-end">Stock Value</th>
                                <th>Last Sale</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryData['deadStock'] as $product)
                            <tr class="dead-stock">
                                <td style="word-break: break-word; max-width: 150px; overflow-wrap: break-word;">
                                    {{ $product->name }}
                                </td>
                                <td style="word-break: break-word; max-width: 120px; overflow-wrap: break-word;">
                                    {{ $product->category_name }}
                                </td>
                                <td class="text-end">{{ $product->quantity_in_stock }}</td>
                                <td class="text-end">₱{{ number_format($product->stock_value, 2) }}</td>
                                <td>
                                    @if($product->last_sale_date)
                                        {{ \Carbon\Carbon::parse($product->last_sale_date)->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">Never Sold</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($inventoryData['deadStock']->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $inventoryData['deadStock']->links('pagination::bootstrap-4') }}
                    </div>
                    @endif
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">No Dead Stock</h5>
                    <p class="text-muted">All products have recent sales activity.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Complete Stock Levels Table -->
    <div class="col-12 mb-4">
        <div class="card report-card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Complete Stock Levels</h5>
                <button type="button" class="btn btn-outline-light btn-sm" onclick="exportStockLevelsCSV()">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
                </button>
            </div>
            <div class="card-body">
                @if($inventoryData['stockLevels']->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th class="text-end">Current Stock</th>
                                <th class="text-end">Reorder Level</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">Stock Value</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryData['stockLevels'] as $product)
                            <tr class="{{ $product->quantity_in_stock == 0 ? 'out-of-stock' : ($product->quantity_in_stock <= $product->reorder_level ? 'low-stock' : '') }}">
                                <td style="word-break: break-word; max-width: 150px; overflow-wrap: break-word;">
                                    {{ $product->name }}
                                </td>
                                <td style="word-break: break-word; max-width: 120px; overflow-wrap: break-word;">
                                    {{ $product->category_name }}
                                </td>
                                <td class="text-end">{{ $product->quantity_in_stock }}</td>
                                <td class="text-end">{{ $product->reorder_level }}</td>
                                <td class="text-end">₱{{ number_format($product->latest_unit_cost, 2) }}</td>
                                <td class="text-end">₱{{ number_format($product->stock_value, 2) }}</td>
                                <td>
                                    @if($product->quantity_in_stock == 0)
                                        <span class="fw-bold text-danger">Out of Stock</span>
                                    @elseif($product->quantity_in_stock <= $product->reorder_level)
                                        <span class="fw-bold text-warning">Low Stock</span>
                                    @else
                                        <span class="fw-bold text-success">In Stock</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($inventoryData['stockLevels']->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $inventoryData['stockLevels']->links('pagination::bootstrap-4') }}
                </div>
                @endif
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-box-seam" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">No Stock Levels Data</h5>
                    <p>No products found in inventory.</p>
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
    .low-stock {
        background-color: rgba(255, 193, 7, 0.1);
    }
    .out-of-stock {
        background-color: rgba(220, 53, 69, 0.1);
    }
       .btn-outline-warning {
        color: #b45309 !important;
        border-color: #b45309 !important;
    }

    .btn-outline-warning:hover {
        background-color: #b45309 !important;
        color: #fff !important;
    }

    .text-warning {
        color: #b45309 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function exportLowStockCSV() { 
        showLoading('Low Stock Alerts');
        window.location.href = '/reports/inventory/export-low-stock-csv'; 
    }

    function exportStockMovementCSV() { 
        showLoading('Stock Movement');
        window.location.href = '/reports/inventory/export-stock-movement-csv'; 
    }

    function exportValuationCSV() { 
        showLoading('Inventory Valuation');
        window.location.href = '/reports/inventory/export-valuation-csv'; 
    }

    function exportAdjustmentsCSV() { 
        showLoading('Stock Adjustments');
        window.location.href = '/reports/inventory/export-adjustments-csv'; 
    }

    function exportReturnsCSV() { 
        showLoading('Returns Impact');
        window.location.href = '/reports/inventory/export-returns-csv'; 
    }

    function exportBestSellersCSV() { 
        showLoading('Best Sellers');
        window.location.href = '/reports/inventory/export-best-sellers-csv'; 
    }

    function exportDeadStockCSV() { 
        showLoading('Dead Stock');
        window.location.href = '/reports/inventory/export-dead-stock-csv'; 
    }

    function exportStockLevelsCSV() { 
        showLoading('Complete Stock Levels');
        window.location.href = '/reports/inventory/export-stock-levels-csv'; 
    }

    function showLoading(reportName) {
        // Optional: Add a small loading indicator
        console.log(`Exporting ${reportName}...`);
    }
</script>
@endpush
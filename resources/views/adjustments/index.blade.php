@extends('layouts.app')
@section('title', 'Stock Adjustments - SAR EQUIP')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
<style>
    .no-negative {
        color: #dc3545 !important;
    }
    .no-positive {
        color: #198754 !important;
    }
</style>
@endpush

@section('content')
    @include('components.alerts')
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <b>Stock Adjustments</b>
            </h2>
            <a href="{{ route('stock-adjustments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>
                New Adjustment
            </a>
        </div>
    </div>

    <!-- Search & Filter Card -->
        <!-- Search & Filter Card -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('stock-adjustments.index') }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">
                    
                    <div class="row g-3 align-items-center">
                        <!-- Search & Clear -->
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="input-group search-box w-100 me-2">
                                    <input type="text" class="form-control" name="search" placeholder="Search by product or SKU..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                
                                @if(request('search') || request('adjustment_type') || request('date_filter') || request('start_date') || request('end_date'))
                                    <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-danger flex-shrink-0" title="Clear filters">
                                        <i class="bi bi-x-circle"></i> Clear
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="col-md-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" style="min-width: 140px;">
                                        <i class="bi bi-sort-down me-1"></i>Sort
                                        @if($sort)
                                            <small class="ms-1">({{ $direction == 'asc' ? '↑' : '↓' }})</small>
                                        @endif
                                    </button>
                                    <ul class="dropdown-menu" style="min-width: 220px;">
                                        <li>
                                            <a class="dropdown-item d-flex justify-content-between align-items-center {{ $sort == 'id' ? 'active' : '' }}" 
                                            href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => $sort == 'id' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                                <span>ID</span>
                                                @if($sort == 'id')
                                                    <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                                                @endif
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex justify-content-between align-items-center {{ $sort == 'adjustment_date' ? 'active' : '' }}" 
                                            href="{{ request()->fullUrlWithQuery(['sort' => 'adjustment_date', 'direction' => $sort == 'adjustment_date' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                                <span>Date</span>
                                                @if($sort == 'adjustment_date')
                                                    <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                                                @endif
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex justify-content-between align-items-center {{ $sort == 'net_qty_change' ? 'active' : '' }}" 
                                            href="{{ request()->fullUrlWithQuery(['sort' => 'net_qty_change', 'direction' => $sort == 'net_qty_change' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                                <span>Quantity Change</span>
                                                @if($sort == 'net_qty_change')
                                                    <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                                                @endif
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex justify-content-between align-items-center {{ $sort == 'financial_impact' ? 'active' : '' }}" 
                                            href="{{ request()->fullUrlWithQuery(['sort' => 'financial_impact', 'direction' => $sort == 'financial_impact' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                                <span>Financial Impact</span>
                                                @if($sort == 'financial_impact')
                                                    <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                                                @endif
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- Additional Filters -->
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label class="form-label">Adjustment Type</label>
                            <select class="form-select" name="adjustment_type" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                @foreach($adjustmentTypes as $type)
                                    <option value="{{ $type }}" {{ request('adjustment_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Quick Date Filters -->
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <select class="form-select" name="date_filter" id="dateFilter" onchange="handleDateFilterChange(this)">
                                <option value="">Custom Date Range</option>
                                <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="this_week" {{ request('date_filter') == 'this_week' ? 'selected' : '' }}>This Week</option>
                                <option value="this_month" {{ request('date_filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                                <option value="this_year" {{ request('date_filter') == 'this_year' ? 'selected' : '' }}>This Year</option>
                            </select>
                        </div>
                        
                        <!-- Custom Date Range Filters (hidden by default) -->
                        <div id="customDateRange" class="col-md-4" style="{{ !request('date_filter') ? '' : 'display: none;' }}">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" class="form-control" name="start_date" id="startDate" value="{{ request('start_date') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" name="end_date" id="endDate" value="{{ request('end_date') }}">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Apply Filters Button (only show when custom date range is active) -->
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" id="applyFiltersBtn" class="btn btn-primary w-100" 
                                    style="{{ !request('date_filter') ? '' : 'display: none;' }}">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    <!-- Stock Adjustments Table -->
    <div class="table-container">    
        <div class="table-responsive">
            <!-- Results Count -->
            <div class="text-muted mb-3">
                @if(request('search'))
                    Showing {{ $stockAdjustments->firstItem() }}–{{ $stockAdjustments->lastItem() }}
                    of {{ $stockAdjustments->total() }} results for
                    "<strong>{{ request('search') }}</strong>"
                @else
                    Showing {{ $stockAdjustments->firstItem() }}–{{ $stockAdjustments->lastItem() }}
                    of {{ $stockAdjustments->total() }} adjustment records
                @endif            
            </div>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Reason</th>
                        <th class="text-end">Items</th>
                        <th class="text-end">Quantity Change</th>
                        <th class="text-end">Financial Impact</th>
                        <th>Processed By</th>
                        <th>Adjustment Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockAdjustments as $adjustment)
                    @php
                        $totalQtyChange = 0;
                        $totalFinancialImpact = 0;
                        foreach($adjustment->items as $item) {
                            $totalQtyChange += $item->quantity_change;
                            $totalFinancialImpact += $item->quantity_change * $item->unit_cost_at_adjustment;
                        }
                    @endphp
                    <tr>
                        <td>{{ $adjustment->id }}</td>
                        <td class="
                            @if($adjustment->adjustment_type == 'Damage/Scrap') text-danger
                            @elseif($adjustment->adjustment_type == 'Found Stock') text-success
                            @elseif($adjustment->adjustment_type == 'Physical Count') text-primary
                            @elseif($adjustment->adjustment_type == 'Internal Use') text-warning
                            @else text-secondary
                            @endif">
                            {{ $adjustment->adjustment_type }}
                        </td>
                        <td>
                            <span title="{{ $adjustment->reason_notes }}">
                                {{ Str::limit($adjustment->reason_notes, 10) }}
                            </span>
                        </td>
                        <td class="text-end">{{ $adjustment->items->count() }}</td>
                        <td class="text-end {{ $totalQtyChange < 0 ? 'no-negative' : ($totalQtyChange > 0 ? 'no-positive' : '') }}">
                            {{ $totalQtyChange > 0 ? '+' : '' }}{{ $totalQtyChange }}
                        </td>
                        <td class="{{ $totalFinancialImpact < 0 ? 'no-negative' : ($totalFinancialImpact > 0 ? 'no-positive' : '') }} text-end">
                            ₱{{ number_format($totalFinancialImpact, 2) }}
                        </td>
                        <td>{{ $adjustment->processedBy ? $adjustment->processedBy->full_name : 'Unknown User' }}</td>
                        <td>{{ $adjustment->adjustment_date->format('M d, Y h:i A') }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info btn-action view-adjustment" data-id="{{ $adjustment->id }}" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <i class="bi bi-clipboard-minus display-4 text-muted"></i>
                            <p class="mt-3 mb-0">No stock adjustment records found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $stockAdjustments->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- View Adjustment Modal -->
    <div class="modal fade" id="viewAdjustmentModal" tabindex="-1" aria-labelledby="viewAdjustmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAdjustmentModalLabel">
                        <i class="bi bi-clipboard-check me-2"></i>
                        Stock Adjustment Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Adjustment ID:</small>
                                    <span class="fw-semibold" id="viewAdjustmentId"></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Date:</small>
                                    <span class="fw-semibold" id="viewAdjustmentDate"></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Type:</small>
                                    <span class="fw-semibold" id="viewAdjustmentType"></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Processed By:</small>
                                    <span class="fw-semibold" id="viewProcessedBy"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Total Items:</small>
                                    <span class="fw-semibold" id="viewTotalItems"></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Quantity Change:</small>
                                    <span class="fw-semibold" id="viewNetQtyChange"></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Financial Impact:</small>
                                    <span class="fw-semibold" id="viewFinancialImpact"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reason Notes -->
                    <div class="mt-3">
                        <small class="text-muted">Reason Notes:</small>
                        <div class="border rounded p-3 bg-light" style="word-break: break-word; white-space: normal;">
                            <span id="viewReasonNotes"></span>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="mt-4">
                        <h6 class="mb-3">Adjustment Items</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th class="text-end">Quantity Change</th>
                                        <th class="text-end">Unit Cost</th>
                                        <th class="text-end">Total Value</th>
                                    </tr>
                                </thead>
                                <tbody id="viewItemsTable">
                                    <!-- Items will be populated here -->
                                </tbody>
                                <tfoot class="table-active">
                                    <tr>
                                        <td colspan="2" class="text-end"><strong>Totals:</strong></td>
                                        <td id="viewTotalQtyChange" class="fw-bold"></td>
                                        <td></td>
                                        <td id="viewTotalValue" class="fw-bold"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function handleDateFilterChange(selectElement) {
            const customDateRange = document.getElementById('customDateRange');
            const applyFiltersBtn = document.getElementById('applyFiltersBtn');
            
            if (selectElement.value === '') {
                // Show custom date range and apply button when "Custom Date Range" is selected
                customDateRange.style.display = 'block';
                applyFiltersBtn.style.display = 'block';
                
                // Clear the date inputs (optional)
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';
            } else {
                // Hide custom date range and apply button
                customDateRange.style.display = 'none';
                applyFiltersBtn.style.display = 'none';
                
                // Clear any custom date values
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';
                
                // Submit the form immediately for quick filters
                selectElement.form.submit();
            }
        }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const dateFilter = document.getElementById('dateFilter');
        const customDateRange = document.getElementById('customDateRange');
        const applyFiltersBtn = document.getElementById('applyFiltersBtn');
        
        // Show/hide based on current selection
        if (dateFilter && customDateRange && applyFiltersBtn) {
            if (dateFilter.value === '') {
                customDateRange.style.display = 'block';
                applyFiltersBtn.style.display = 'block';
            } else {
                customDateRange.style.display = 'none';
                applyFiltersBtn.style.display = 'none';
            }
        }
    });
        // View Adjustment
        document.querySelectorAll('.view-adjustment').forEach(button => {
            button.addEventListener('click', function() {
                const adjustmentId = this.getAttribute('data-id');
                
                fetch(`/stock-adjustments/${adjustmentId}`)
                    .then(response => response.json())
                    .then(adjustment => {
                        // Populate header information
                        document.getElementById('viewAdjustmentId').textContent = adjustment.id;
                        document.getElementById('viewAdjustmentDate').textContent = new Date(adjustment.adjustment_date).toLocaleDateString('en-US', {
                            month: 'short', day: '2-digit',  year: 'numeric'
                        }) + ' ' + new Date(adjustment.adjustment_date).toLocaleTimeString('en-US', {
                            hour: '2-digit', minute: '2-digit', hour12: true
                        });
                        document.getElementById('viewAdjustmentType').textContent = adjustment.adjustment_type;
                        document.getElementById('viewProcessedBy').textContent = adjustment.processed_by.full_name;
                        document.getElementById('viewTotalItems').textContent = adjustment.items.length;
                        document.getElementById('viewReasonNotes').textContent = adjustment.reason_notes;
                        
                        // Calculate totals
                        let totalQtyChange = 0;
                        let totalFinancialImpact = 0;
                        
                        adjustment.items.forEach(item => {
                            totalQtyChange += parseInt(item.quantity_change);
                            totalFinancialImpact += (item.quantity_change * item.unit_cost_at_adjustment);
                        });
                        
                        document.getElementById('viewNetQtyChange').textContent = totalQtyChange > 0 ? `+${totalQtyChange}` : totalQtyChange;
                        document.getElementById('viewNetQtyChange').className = totalQtyChange < 0 ? 'no-negative fw-semibold' : (totalQtyChange > 0 ? 'no-positive fw-semibold' : 'fw-semibold');
                        
                        document.getElementById('viewFinancialImpact').textContent = '₱' + parseFloat(totalFinancialImpact).toFixed(2);
                        document.getElementById('viewFinancialImpact').className = totalFinancialImpact < 0 ? 'no-negative fw-semibold' : (totalFinancialImpact > 0 ? 'no-positive fw-semibold' : 'fw-semibold');
                        
                        // Populate items table
                        const itemsTable = document.getElementById('viewItemsTable');
                        itemsTable.innerHTML = '';
                        
                        let tableTotalQty = 0;
                        let tableTotalValue = 0;
                        
                        adjustment.items.forEach(item => {
                            const row = document.createElement('tr');
                            const itemTotalValue = item.quantity_change * item.unit_cost_at_adjustment;
                            tableTotalQty += item.quantity_change;
                            tableTotalValue += itemTotalValue;
                            
                            row.innerHTML = `
                                <td style="word-break: break-word; white-space: normal; max-width: 200px;">
                                    <div style="word-break: break-word; line-height: 1.3;">
                                        ${item.product.name}
                                    </div>
                                </td>
                                <td>${item.product.sku}</td>
                                <td class="text-end ${item.quantity_change < 0 ? 'no-negative' : (item.quantity_change > 0 ? 'no-positive' : '')}">
                                    ${item.quantity_change > 0 ? '+' : ''}${item.quantity_change}
                                </td>
                                <td style="text-align: right;">₱${parseFloat(item.unit_cost_at_adjustment).toFixed(2)}</td>
                                <td style="text-align: right;" class="${itemTotalValue < 0 ? 'no-negative' : (itemTotalValue > 0 ? 'no-positive' : '')}">
                                    ₱${parseFloat(itemTotalValue).toFixed(2)}
                                </td>
                            `;
                            itemsTable.appendChild(row);
                        });
                        
                        // Update table footer totals
                        document.getElementById('viewTotalQtyChange').textContent = tableTotalQty > 0 ? `+${tableTotalQty}` : tableTotalQty;
                        document.getElementById('viewTotalQtyChange').className = tableTotalQty < 0 ? 'no-negative' : (tableTotalQty > 0 ? 'no-positive' : '');
                        
                        document.getElementById('viewTotalValue').textContent = '₱' + parseFloat(tableTotalValue).toFixed(2);
                        document.getElementById('viewTotalValue').className = `text-end ${tableTotalValue < 0 ? 'no-negative' : (tableTotalValue > 0 ? 'no-positive' : '')}`;
                        
                        const modal = new bootstrap.Modal(document.getElementById('viewAdjustmentModal'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching adjustment:', error);
                        alert('Error loading adjustment details');
                    });
            });
        });
    </script>
    @endpush
@endsection
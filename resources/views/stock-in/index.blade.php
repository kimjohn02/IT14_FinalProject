@extends('layouts.app')
@section('title', 'Stock In - SAR EQUIP')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
@endpush
@section('content')
    @include('components.alerts')
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <b>Stock In</b>
            </h2>
            <a href="{{ route('stock-ins.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>
                New Stock In
            </a>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('stock-ins.index') }}" id="filterForm">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                
                <div class="row g-3 align-items-center">
                    <!-- Search & Clear -->
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="input-group search-box w-100 me-2">
                                <input type="text" class="form-control" name="search" placeholder="Search by reference or product..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            
                            @if(request('search') || request('date_filter') || request('start_date') || request('end_date'))
                                <a href="{{ route('stock-ins.index') }}" class="btn btn-outline-danger flex-shrink-0" title="Clear filters">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Sort -->
                    <div class="col-md-4">
                        <div class="d-flex gap-2 justify-content-end">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-sort-down me-1"></i>Sort
                                    @if($sort)
                                        <small class="ms-1">({{ $direction == 'asc' ? '↑' : '↓' }})</small>
                                    @endif
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item {{ $sort == 'id' ? 'active' : '' }}" 
                                        href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => $sort == 'id' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                        ID @if($sort == 'id') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                    </a></li>
                                    <li><a class="dropdown-item {{ $sort == 'stock_in_date' ? 'active' : '' }}" 
                                        href="{{ request()->fullUrlWithQuery(['sort' => 'stock_in_date', 'direction' => $sort == 'stock_in_date' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                        Date @if($sort == 'stock_in_date') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                    </a></li>
                                    <li><a class="dropdown-item {{ $sort == 'reference_no' ? 'active' : '' }}" 
                                        href="{{ request()->fullUrlWithQuery(['sort' => 'reference_no', 'direction' => $sort == 'reference_no' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                        Reference @if($sort == 'reference_no') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date Filters Section -->
                <div class="row mt-3">
                    <!-- Quick Date Filters -->
                    <div class="col-md-3">
                        <label class="form-label">  Date Range</label>
                        <select class="form-select" name="date_filter" id="dateFilter" onchange="handleDateFilterChange(this)">
                            <option value="">Custom Date Range</option>
                            <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="this_week" {{ request('date_filter') == 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="this_month" {{ request('date_filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="this_year" {{ request('date_filter') == 'this_year' ? 'selected' : '' }}>This Year</option>
                        </select>
                    </div>
                    
                    <!-- Custom Date Range Filters (hidden by default) -->
                    <div id="customDateRange" class="col-md-6" style="{{ !request('date_filter') ? '' : 'display: none;' }}">
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
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" id="applyFiltersBtn" class="btn btn-primary w-100" 
                                style="{{ !request('date_filter') ? '' : 'display: none;' }}">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stock In Table -->
    <div class="table-container">    
        <div class="table-responsive">
            <!-- Results Count -->
            <div class="text-muted mb-3">
                @if(request('search'))
    Showing {{ $stockIns->firstItem() }}–{{ $stockIns->lastItem() }}
    of {{ $stockIns->total() }} results for
    "<strong>{{ request('search') }}</strong>"
@else
    Showing {{ $stockIns->firstItem() }}–{{ $stockIns->lastItem() }}
    of {{ $stockIns->total() }} stock-in records
@endif

            </div>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Reference No</th>
                        <th class="text-end">Items</th>
                        <th class="text-end">Total Quantity</th>
                        <th class="text-end">Total Cost</th>
                        <th>Received By</th>
                        <th>Stock-In Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockIns as $stockIn)
                    <tr>
                        <td>{{ $stockIn->id }}</td>
                        <td>{{ $stockIn->reference_no ?? 'N/A' }}</td>
                        <td class="text-end">{{ $stockIn->items->count() }}</td>
                        <td class="text-end">{{ $stockIn->items->sum('quantity_received') }}</td>
                        <td class="text-end">₱{{ number_format($stockIn->items->sum(function($item) { return $item->quantity_received * $item->actual_unit_cost; }), 2) }}</td>
                        <td>{{ $stockIn->receivedBy ? $stockIn->receivedBy->full_name : 'Unknown User' }}</td>
                        <td>{{ $stockIn->stock_in_date->format('M d, Y h:i A') }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info btn-action view-stock-in" data-id="{{ $stockIn->id }}" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <i class="bi bi-box-arrow-in-down display-4 text-muted"></i>
                            <p class="mt-3 mb-0">No stock in records found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $stockIns->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- View Stock In Modal -->
    <div class="modal fade" id="viewStockInModal" tabindex="-1" aria-labelledby="viewStockInModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewStockInModalLabel">
                        <i class="bi bi-box-arrow-in-down me-2"></i>
                        Stock In Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Stock In ID:</small>
                                    <span class="fw-semibold" id="viewStockInId"></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Date:</small>
                                    <span class="fw-semibold" id="viewStockInDate"></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Reference No:</small>
                                    <span class="fw-semibold" id="viewReferenceNo">N/A</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Received By:</small>
                                    <span class="fw-semibold" id="viewReceivedBy"></span>
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
                                    <small class="text-muted">Total Quantity:</small>
                                    <span class="fw-semibold" id="viewTotalQuantity"></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Total Cost:</small>
                                    <span class="fw-semibold" id="viewTotalCost"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="mt-4">
                        <h6 class="mb-3">Items Received:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Supplier</th>
                                        <th class="text-end">Quantity</th>
                                        <th class="text-end">Unit Cost</th>
                                        <th class="text-end">Total Cost</th>
                                    </tr>
                                </thead>
                                <tbody id="viewItemsTable">
                                    <!-- Items will be populated here -->
                                </tbody>
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
        // View Stock In
        document.querySelectorAll('.view-stock-in').forEach(button => {
            button.addEventListener('click', function() {
                const stockInId = this.getAttribute('data-id');
                
                fetch(`/stock-ins/${stockInId}`)
                    .then(response => response.json())
                    .then(stockIn => {
                        document.getElementById('viewStockInId').textContent = stockIn.id;
                        document.getElementById('viewStockInDate').textContent = new Date(stockIn.stock_in_date).toLocaleDateString('en-US', {
                            month: 'short', day: '2-digit',  year: 'numeric'
                        }) + ' ' + new Date(stockIn.stock_in_date).toLocaleTimeString('en-US', {
                            hour: '2-digit', minute: '2-digit', hour12: true
                        });
                        document.getElementById('viewReferenceNo').textContent = stockIn.reference_no || 'N/A';
                        document.getElementById('viewReceivedBy').textContent = stockIn.received_by.full_name;
                        document.getElementById('viewTotalItems').textContent = stockIn.items.length;
                        
                        // Calculate totals
                        const totalQuantity = stockIn.items.reduce((sum, item) => sum + parseInt(item.quantity_received), 0);
                        const totalCost = stockIn.items.reduce((sum, item) => sum + (item.quantity_received * item.actual_unit_cost), 0);
                        
                        document.getElementById('viewTotalQuantity').textContent = totalQuantity;
                        document.getElementById('viewTotalCost').textContent = '₱' + parseFloat(totalCost).toFixed(2);
                        
                        // Populate items table
                        const itemsTable = document.getElementById('viewItemsTable');
                        itemsTable.innerHTML = '';
                        
                        stockIn.items.forEach(item => {
                            const row = document.createElement('tr');
                            const totalCost = item.quantity_received * item.actual_unit_cost;
                            row.innerHTML = `
                                <td style="word-break: break-word; white-space: normal; max-width: 200px;">${item.product.name}</td>
                                <td style="word-break: break-word; white-space: normal; max-width: 200px;">${item.supplier ? item.supplier.supplier_name : 'N/A'}</td> 
                                <td class="text-end">${item.quantity_received}</td>
                                <td class="text-end">₱${parseFloat(item.actual_unit_cost).toFixed(2)}</td>
                                <td class="text-end">₱${parseFloat(totalCost).toFixed(2)}</td>
                            `;
                            itemsTable.appendChild(row);
                        });
                        
                        const modal = new bootstrap.Modal(document.getElementById('viewStockInModal'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching stock in:', error);
                    });
            });
        });
    </script>
    @endpush
@endsection
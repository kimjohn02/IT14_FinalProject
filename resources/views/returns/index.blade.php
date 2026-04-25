@extends('layouts.app')
@section('title', 'Product Returns - SAR EQUIP')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
@endpush
@section('content')
    @include('components.alerts')

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <b>Product Returns</b>
            </h2>
            <a href="{{ route('returns.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>
                Process New Return
            </a>
        </div>
    </div>

    <!-- Search & Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('returns.index') }}" id="filterForm">
            <!-- Hidden sort fields -->
            <input type="hidden" name="sort" value="{{ $sort ?? 'created_at' }}">
            <input type="hidden" name="direction" value="{{ $direction ?? 'desc' }}">
            
            <div class="row g-3 align-items-center">
                <!-- Search & Clear -->
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="input-group search-box w-100 me-2">
                            <input type="text" class="form-control" name="search" placeholder="Search by Sale ID..." value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        
                        @if(request('search') || request('return_reason') || request('date_filter') || request('start_date') || request('end_date') || (request('sort') && request('sort') != 'created_at') || (request('direction') && request('direction') != 'desc'))
                            <a href="{{ route('returns.index') }}" class="btn btn-outline-danger flex-shrink-0" title="Clear filters">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Sort Dropdown -->
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
                                <li><a class="dropdown-item {{ $sort == 'created_at' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => $sort == 'created_at' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    Date @if($sort == 'created_at') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                                <li><a class="dropdown-item {{ $sort == 'total_refund_amount' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['sort' => 'total_refund_amount', 'direction' => $sort == 'total_refund_amount' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    Total Refund @if($sort == 'total_refund_amount') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                                <li><a class="dropdown-item {{ $sort == 'id' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => $sort == 'id' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    Return ID @if($sort == 'id') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Filters -->
            <div class="row mt-3">
                <!-- Return Reason Filter -->
                <div class="col-md-3">
                    <label class="form-label">Return Reason</label>
                    <select class="form-select" name="return_reason" onchange="this.form.submit()">
                        <option value="">All Reasons</option>
                        <option value="Defective" {{ request('return_reason') == 'Defective' ? 'selected' : '' }}>Defective</option>
                        <option value="Wrong Item" {{ request('return_reason') == 'Wrong Item' ? 'selected' : '' }}>Wrong Item</option>
                        <option value="Customer Change Mind" {{ request('return_reason') == 'Customer Change Mind' ? 'selected' : '' }}>Customer Change Mind</option>
                        <option value="Other" {{ request('return_reason') == 'Other' ? 'selected' : '' }}>Other</option>
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

    <!-- Returns Table -->
    <div class="table-container"> 
        <div class="table-responsive">
            <!-- Results Count -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="text-muted">
                    @if(request('search'))
                        Showing {{ $returns->firstItem() }}–{{ $returns->lastItem() }}
                        of {{ $returns->total() }} results for
                        "<strong>{{ request('search') }}</strong>"
                    @else
                        Showing {{ $returns->firstItem() }}–{{ $returns->lastItem() }}
                        of {{ $returns->total() }} returns
                    @endif
                </div>
                <div class="text-muted">
                    Total Refunded: ₱{{ number_format($totalRefunded, 2) }}
                </div>
            </div>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Return ID</th>
                        <th>Sale ID</th>
                        <th>Return Reason</th>
                        <th class="text-end">
                            Total Refund
                            @if($sort == 'total_refund_amount')
                                <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} ms-1"></i>
                            @endif
                        </th>
                        <th class="text-end">Items Returned</th>
                        <th>Processed By</th>
                        <th>
                            Return Date
                            @if($sort == 'created_at')
                                <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} ms-1"></i>
                            @endif
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                    <tr>
                        <td>{{ $return->id }}</td>
                        <td>{{ $return->sale_id }}</td>
                        <td>{{ $return->return_reason }}</td>
                        <td class="text-end">₱{{ number_format($return->total_refund_amount, 2) }}</td>
                        <td class="text-end">{{ $return->returnItems->count() }} item(s)</td>
                        <td>{{ $return->user->f_name ?? 'N/A' }}</td>
                        <td>{{ $return->created_at->format('M d, Y h:i A') }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info btn-action view-return" data-id="{{ $return->id }}" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="mt-3 mb-0">No returns found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $returns->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- View Return Modal -->
    <div class="modal fade" id="viewReturnModal" tabindex="-1" aria-labelledby="viewReturnModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReturnModalLabel">
                        <i class="bi bi-eye me-2"></i>
                        Return Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Return Information</h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item px-0">
                                    <small class="text-muted d-block">Return ID</small>
                                    <span class="fw-semibold" id="viewReturnId"></span>
                                </div>
                                <div class="list-group-item px-0">
                                    <small class="text-muted d-block">Sale ID</small>
                                    <span class="fw-semibold" id="viewSaleId"></span>
                                </div>
                                <div class="list-group-item px-0">
                                    <small class="text-muted d-block">Customer</small>
                                    <span class="fw-semibold" id="viewCustomerName"></span>
                                </div>
                                <div class="list-group-item px-0">
                                    <small class="text-muted d-block">Customer Contact</small>
                                    <span class="fw-semibold" id="viewCustomerContact"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Financial Details</h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item px-0">
                                    <small class="text-muted d-block">Total Refund</small>
                                    <span class="fw-semibold text-danger" id="viewTotalRefund"></span>
                                </div>
                                <div class="list-group-item px-0">
                                    <small class="text-muted d-block">Refund Method</small>
                                    <span class="fw-semibold" id="viewRefundMethod"></span>
                                </div>
                                <div class="list-group-item px-0">
                                    <small class="text-muted d-block">Return Reason</small>
                                    <span class="fw-semibold" id="viewReturnReason"></span>
                                </div>
                                <div class="list-group-item px-0">
                                    <small class="text-muted d-block">Reference No</small>
                                    <span class="fw-semibold" id="viewReferenceNo"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6>Returned Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm" id="returnItemsTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th class="text-end">Total Refund</th>
                                    <th>Condition</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Items will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <h6>Notes</h6>
                        <div class="card">
                            <div class="card-body">
                                <p class="mb-0" id="viewReturnNotes"></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">Processed by: <span id="viewProcessedBy"></span> on <span id="viewReturnDate"></span></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        // View Return Details
        document.querySelectorAll('.view-return').forEach(button => {
            button.addEventListener('click', function() {
                const returnId = this.getAttribute('data-id');
                
                fetch(`/returns/${returnId}`)
                    .then(response => response.json())
                    .then(returnData => {
                        // Populate basic info
                        document.getElementById('viewReturnId').textContent = returnData.id;
                        document.getElementById('viewSaleId').textContent = returnData.sale_id;
                        document.getElementById('viewCustomerName').textContent = returnData.sale.customer_name || 'N/A';
                        document.getElementById('viewCustomerContact').textContent = returnData.sale.customer_contact || 'N/A';
                        document.getElementById('viewTotalRefund').textContent = '-₱' + parseFloat(returnData.total_refund_amount).toFixed(2);
                        document.getElementById('viewRefundMethod').textContent = returnData.refund_payment.payment_method;
                        document.getElementById('viewReturnReason').textContent = returnData.return_reason;
                        document.getElementById('viewReferenceNo').textContent = returnData.refund_payment.reference_no || 'Not applicable (Cash refund)';
                        document.getElementById('viewProcessedBy').textContent = returnData.user.f_name + ' ' + returnData.user.l_name;
                        document.getElementById('viewReturnDate').textContent = new Date(returnData.created_at).toLocaleDateString('en-US', {
                            month: 'short', day: '2-digit', year: 'numeric'
                        }) + ' ' + new Date(returnData.created_at).toLocaleTimeString('en-US', {
                            hour: '2-digit', minute: '2-digit', hour12: true
                        });
                        document.getElementById('viewReturnNotes').textContent = returnData.notes || 'No notes provided.';

                        // Populate return items
                        const itemsTable = document.getElementById('returnItemsTable').querySelector('tbody');
                        itemsTable.innerHTML = '';
                        
                        returnData.return_items.forEach(item => {
                            const row = itemsTable.insertRow();
                            row.innerHTML = `
                                <td style="word-break: break-word; max-width: 200px; overflow-wrap: break-word;">
                                    ${item.product.name}
                                </td>
                                <td>${item.product.sku}</td>
                                <td>${item.quantity_returned}</td>
                                <td class="text-end">₱${parseFloat(item.refunded_price_per_unit).toFixed(2)}</td>
                                <td class="text-end">₱${parseFloat(item.total_line_refund).toFixed(2)}</td>
                                <td>${item.inventory_adjusted ? 'Resaleable' : 'Damaged'}</td>
                                <td>${item.inventory_adjusted ? 'Restocked' : 'Scrapped/Loss'}</td>
                            `;
                        });

                        const modal = new bootstrap.Modal(document.getElementById('viewReturnModal'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching return details:', error);
                        alert('Error loading return details. Please try again.');
                    });
            });
        });
    </script>
    @endpush
@endsection
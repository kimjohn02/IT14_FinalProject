@extends('layouts.app')
@section('title', 'Transaction History - SAR EQUIP')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
@endpush
@section('content')
    @include('components.alerts')

    <div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-0">
            <b>Transaction History</b>
        </h2>
        <button type="button" class="btn btn-outline-success" onclick="exportSalesCSV()">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </button>
    </div>
</div>

    <!-- Search & Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sales.index') }}">
                <div class="row g-3 align-items-center">
                    <!-- Search & Clear -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="input-group search-box w-100 me-2">
                                <input type="text" class="form-control" name="search" placeholder="Search by sale ID..." value="{{ request('search') }}">                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            
                            @if(request('search') || request('date_filter') || request('start_date') || request('end_date') || request('payment_method'))
                                <a href="{{ route('sales.index') }}" class="btn btn-outline-danger flex-shrink-0" title="Clear filters">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Sort -->
                    <div class="col-md-6">
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
                                            <span>Sale ID</span>
                                            @if($sort == 'id')
                                                <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex justify-content-between align-items-center {{ $sort == 'sale_date' ? 'active' : '' }}" 
                                        href="{{ request()->fullUrlWithQuery(['sort' => 'sale_date', 'direction' => $sort == 'sale_date' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                            <span>Date</span>
                                            @if($sort == 'sale_date')
                                                <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex justify-content-between align-items-center {{ $sort == 'total_amount' ? 'active' : '' }}" 
                                        href="{{ request()->fullUrlWithQuery(['sort' => 'total_amount', 'direction' => $sort == 'total_amount' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                            <span>Total Amount</span>
                                            @if($sort == 'total_amount')
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
                    <!-- Payment Method Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" name="payment_method" onchange="this.form.submit()">
                            <option value="">All Methods</option>
                            @foreach(['Cash', 'GCash', 'Card'] as $method)
                                <option value="{{ $method }}" {{ request('payment_method') == $method ? 'selected' : '' }}>
                                    {{ $method }}
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
                            <option value="last_week" {{ request('date_filter') == 'last_week' ? 'selected' : '' }}>Last Week</option>
                            <option value="last_month" {{ request('date_filter') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="last_year" {{ request('date_filter') == 'last_year' ? 'selected' : '' }}>Last Year</option>
                        </select>
                    </div>
                    
                    <!-- Custom Date Range Filters -->
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
                    
                    <!-- Apply Filters Button -->
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

    <!-- Sales Table -->
    <div class="table-container">
        <div class="table-responsive">
            <!-- Results Count -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="text-muted">
                    @if(request('search'))
                        Showing {{ $sales->firstItem() }}–{{ $sales->lastItem() }}
                        of {{ $sales->total() }} results for
                        "<strong>{{ request('search') }}</strong>"
                    @else
                        Showing {{ $sales->firstItem() }}–{{ $sales->lastItem() }}
                        of {{ $sales->total() }} sales
                    @endif
                </div>
            </div>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Sale ID</th>
                        <th>Date & Time</th>
                        <th>Cashier</th>
                        <th class="text-end">Items</th>
                        <th class="text-end">Total Amount</th>
                        <th>Payment Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td><strong>#{{ $sale->id }}</strong></td>
                        <td>{{ $sale->sale_date->format('M d, Y h:i A') }}</td>
                        <td>{{ $sale->user->f_name ?? '' }} {{ $sale->user->l_name ?? 'N/A' }}</td>
                        <td class="text-end">{{ $sale->items->count() }} items</td>
                        <td class="fw-bold text-success text-end">₱{{ number_format($sale->items->sum(function($item) { return $item->quantity_sold * $item->unit_price; }), 2) }}</td>
                        <td>{{ $sale->payment->payment_method }}</td>
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
                        <td colspan="7" class="text-center py-4">
                            <i class="bi bi-receipt display-4 text-muted"></i>
                            <p class="mt-3 mb-0">No transactions found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $sales->appends(request()->query())->links('pagination::bootstrap-4') }}
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
                    <div class="row mb-4">
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

    @push('scripts')
    <script>
        function handleDateFilterChange(selectElement) {
            const customDateRange = document.getElementById('customDateRange');
            const applyFiltersBtn = document.getElementById('applyFiltersBtn');
            
            if (selectElement.value === '') {
                customDateRange.style.display = 'block';
                applyFiltersBtn.style.display = 'block';
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';
            } else {
                customDateRange.style.display = 'none';
                applyFiltersBtn.style.display = 'none';
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';
                selectElement.form.submit();
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const dateFilter = document.getElementById('dateFilter');
            const customDateRange = document.getElementById('customDateRange');
            const applyFiltersBtn = document.getElementById('applyFiltersBtn');
            
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
        // View Sale Details
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
                                    <small class="text-muted">${item.product ? (item.product.sku || '') : ''}</small>
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

        function exportSalesCSV() {
            const params = new URLSearchParams(window.location.search);
            
            const btn = event.target;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';
            btn.disabled = true;
            
            window.location.href = '{{ route("sales.export.csv") }}?' + params.toString();
            
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }, 3000);
        }

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
@endsection
@extends('layouts.app')
@section('title', 'Today\'s Transactions - POS')
@push('styles')
<style>
    .stats-card {
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .stat-label {
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .stat-value {
        font-size: 1.8rem;
        margin-top: 4px;
    }
    
    .transaction-row:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
        border-left-color: #007bff;
    }
    
    .transaction-row.recent {
        border-left-color: #28a745;
        background-color: #f8fff9;
    }
    
    .search-card {
        background: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .empty-state i {
        font-size: 64px;
        color: #dee2e6;
        margin-bottom: 15px;
    }
    
    .recent-badge {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><b>My Sales Today</b></h2>
            <p class="text-muted mb-0">Showing transactions you made on {{ now()->format('l, F d, Y') }}</p>
        </div>
        <div>
            <a href="{{ route('pos.index') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> New Sale
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <!-- Today's Sales -->
        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="stat-label text-muted">Today's Sales</span>
                        <i class="bi bi-cash-stack text-muted"></i>
                    </div>
                    <div class="stat-value text-end fw-bold text-success">
                        ₱{{ number_format($todaySummary, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions -->
        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="stat-label text-muted">Transactions</span>
                        <i class="bi bi-receipt text-muted"></i>
                    </div>
                    <div class="stat-value text-end fw-bold text-dark">
                        {{ $totalSalesToday }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Sold -->
        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="stat-label text-muted">Items Sold</span>
                        <i class="bi bi-box-seam text-muted"></i>
                    </div>
                    <div class="stat-value text-end fw-bold text-dark">
                        {{ $totalItemsToday }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Box -->
    <div class="search-card">
        <form method="GET" action="{{ route('pos.my-transactions') }}">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" 
                       class="form-control" 
                       name="search" 
                       placeholder="Search by Sale ID..."
                       value="{{ request('search') }}"
                       autofocus>
                @if(request('search'))
                    <a href="{{ route('pos.my-transactions') }}" class="btn btn-outline-secondary">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Transactions List -->
    @if($sales->count() > 0)
    
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-list-check me-2"></i>
                    My Recent Transactions
                </h5>
            </div>
            <div class="card-body p-0">
                <div style="padding: 16px" class="table-responsive">

                <div class="text-muted">
                    Showing {{ $sales->firstItem() }}–{{ $sales->lastItem() }} of {{ $sales->total() }} transactions
                </div>
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sale ID</th>
                                <th>Time</th>
                                <th class="text-end">Items</th>
                                <th class="text-end">Total Amount</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $sale)
                                @php
                                    $isRecent = $sale->sale_date->gt(now()->subMinutes(10));
                                    $total = $sale->items->sum(function($item) {
                                        return $item->quantity_sold * $item->unit_price;
                                    });
                                    $itemCount = $sale->items->sum('quantity_sold');
                                @endphp
                                <tr class="transaction-row {{ $isRecent ? 'recent' : '' }}" 
                                    data-id="{{ $sale->id }}">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <strong>#{{ $sale->id }}</strong>
                                            @if($isRecent)
                                                <span class="badge bg-success recent-badge">NEW</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $sale->sale_date->format('h:i A') }}</td>
                                    <td class="text-end">{{ $itemCount }} {{ $itemCount == 1 ? 'item' : 'items' }}</td>
                                    <td class="fw-bold text-success text-end">
                                        ₱{{ number_format($total, 2) }}
                                    </td>
                                    <td>
                                        <span class="payment-badge {{ strtolower($sale->payment->payment_method ?? 'cash') }}">
                                            {{ $sale->payment->payment_method ?? 'Cash' }}
                                        </span>
                                    </td>
                                    <td>
                 
                                        <button class="btn btn-outline-info btn-sm btn-action data-id="{{ $sale->id }}"
                                                title="View Details"
                                                onclick="event.stopPropagation(); loadSaleDetails({{ $sale->id }})">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    
                                        <button class="btn btn-outline-success btn-sm"
                                                title="Print Receipt"
                                                onclick="event.stopPropagation(); printReceipt({{ $sale->id }});">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                    
                                        <a href="{{ route('pos.receipt.pdf', $sale->id) }}" 
                                           class="btn btn-outline-secondary btn-sm" 
                                           target="_blank"
                                           title="Download PDF"
                                           onclick="event.stopPropagation()">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    </td>
                                    
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $sales->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-receipt"></i>
            <h4 class="mt-3">No transactions today</h4>
            <p class="text-muted">
                @if(request('search'))
                    No sales found for "{{ request('search') }}"
                @else
                    You haven't made any sales today yet
                @endif
            </p>
        </div>
    @endif
</div>

<!-- View Sale Modal -->
<div class="modal fade" id="viewSaleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i>
                    Sale #<span id="viewSaleId"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Sale Info -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card border-light">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Sale Information</h6>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Sale ID</small>
                                    <span class="fw-semibold" id="viewSaleNumber"></span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Time</small>
                                    <span class="fw-semibold" id="viewSaleTime"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-light">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Customer Information</h6>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Customer Name</small>
                                    <span class="fw-semibold" id="viewSaleCustomer"></span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Contact</small>
                                    <span class="fw-semibold" id="viewSaleContact"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div class="card border-light shadow-sm">
                    <div class="card-header bg-white">
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
                                <tbody id="viewSaleItems"></tbody>
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

                <!-- Payment -->
                <div class="card border-light shadow-sm mt-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Payment Information</h6>
                    </div>
                    <div class="card-body">
                        <div id="viewSalePayment"></div>
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

@push('scripts')
<script>
    function loadSaleDetails(saleId) {
        fetch(`/pos/sale/${saleId}/details`)
            .then(response => response.json())
            .then(sale => {
                // Basic info
                document.getElementById('viewSaleId').textContent = sale.id;
                document.getElementById('viewSaleNumber').textContent = '#' + sale.id;
                document.getElementById('viewSaleTime').textContent = new Date(sale.sale_date).toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
                document.getElementById('viewSaleCustomer').textContent = sale.customer_name || 'N/A';
                document.getElementById('viewSaleContact').textContent = sale.customer_contact || 'N/A';
                
                // Items
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
                
                document.getElementById('viewSaleTotal').textContent = `₱${total.toFixed(2)}`;
                
                // Payment info
                const paymentContainer = document.getElementById('viewSalePayment');
                if (sale.payment) {
                    paymentContainer.innerHTML = `
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Payment Method</small>
                                <span class="fw-semibold">
                                    ${sale.payment.payment_method}
                                </span>
                            </div>
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Amount Tendered</small>
                                <span class="fw-semibold">₱${parseFloat(sale.payment.amount_tendered).toFixed(2)}</span>
                            </div>
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Change Given</small>
                                <span class="fw-semibold">₱${parseFloat(sale.payment.change_given).toFixed(2)}</span>
                            </div>
                        </div>
                        ${sale.payment.reference_no ? `
                        <div class="row">
                            <div class="col-md-12">
                                <small class="text-muted d-block">Reference Number</small>
                                <span class="fw-semibold">${sale.payment.reference_no}</span>
                            </div>
                        </div>
                        ` : ''}
                    `;
                }
                
                // Print button
                document.getElementById('printReceiptBtn').onclick = function () {
                    printReceipt(saleId);
                };

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('viewSaleModal'));
                modal.show();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading sale details');
            });
    }

    function printReceipt(id) {
        const url = "{{ route('receipt.print', ['id' => '__ID__']) }}".replace('__ID__', id);

        const win = window.open(url, '_blank', 'width=600,height=600,top=100,left=100,scrollbars=yes');
        if (!win) return alert('Popup blocked! Please allow popups for this site.');

        // Add overlay
        const overlay = Object.assign(document.createElement('div'), {
            id: 'printOverlay',
            style: 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.3);z-index:1050;'
        });
        document.body.appendChild(overlay);

        // Wait for popup to load and print
        win.onload = () => win.focus() || win.print();

        // Remove overlay when popup closes
        const checkClosed = setInterval(() => {
            if (win.closed) {
                clearInterval(checkClosed);
                overlay.remove();
            }
        }, 300);
    }
</script>
@endpush
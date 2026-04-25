<div class="row">
    <div class="col-12 mb-4">
        <div class="card report-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Sales by Date Range</h5>
                <small>{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transactions</th>
                                <th>Total Revenue</th>
                                <th>Average per Transaction</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesData['salesByDate'] as $sale)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($sale->date)->format('M d, Y') }}</td>
                                <td>{{ $sale->transaction_count }}</td>
                                <td>₱{{ number_format($sale->total_revenue, 2) }}</td>
                                <td>₱{{ number_format($sale->total_revenue / $sale->transaction_count, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Sales Table -->
    <div class="col-12 mb-4">
        <div class="card report-card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Detailed Sales Transactions</h5>
                <small>Individual sales records for the selected period</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Sale ID</th>
                                <th>Date & Time</th>
                                <th>Cashier</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Payment Method</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesData['detailedSales'] as $sale)
                            <tr>
                                <td><strong>#{{ $sale->id }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y h:i A') }}</td>
                                <td>{{ $sale->f_name ?? 'N/A' }} {{ $sale->l_name ?? '' }}</td>
                                <td>{{ $sale->items_count }} items</td>
                                <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                                <td>{{ $sale->payment_method ?? 'N/A' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info btn-action view-sale" data-id="{{ $sale->id }}" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('sales.receipt', $sale->id) }}" class="btn btn-sm btn-outline-success btn-action" title="Print Receipt" target="_blank">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    No sales found for the selected period
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

    <div class="col-md-6 mb-4">
        <div class="card report-card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Top 10 Products by Revenue</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Revenue</th>
                                <th>Avg Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesData['productPerformance'] as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->total_quantity }}</td>
                                <td>₱{{ number_format($product->total_revenue, 2) }}</td>
                                <td>₱{{ number_format($product->avg_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card report-card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Sales by Category</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Revenue</th>
                                <th>Quantity</th>
                                <th>Transactions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesData['categoryAnalysis'] as $category)
                            <tr>
                                <td>{{ $category->category_name }}</td>
                                <td>₱{{ number_format($category->total_revenue, 2) }}</td>
                                <td>{{ $category->total_quantity }}</td>
                                <td>{{ $category->transaction_count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
                                        <th>Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="viewSaleItems">
                                    <!-- Items will be populated by JavaScript -->
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                        <td class="text-end fw-bold text-success" id="viewSaleTotal"></td>
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
                <a href="#" class="btn btn-success" id="printReceiptBtn" target="_blank">
                    <i class="bi bi-receipt me-1"></i> Print Receipt
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
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
                                <td>${item.product ? item.product.name : 'N/A'}</td>
                                <td class="text-center">${item.quantity_sold}</td>
                                <td class="text-end">₱${parseFloat(item.unit_price).toFixed(2)}</td>
                                <td class="text-end">₱${itemTotal.toFixed(2)}</td>
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
                        document.getElementById('printReceiptBtn').href = `/sales/${sale.id}/receipt`;
                        
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
</script>
@endpush
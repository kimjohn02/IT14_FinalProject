@extends('layouts.app')
@section('title', 'Product Prices - SAR EQUIP')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
<style>
    .product-image {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
    }
    .no-price {
        color: #6c757d;
        font-style: italic;
    }
</style>
@endpush
@section('content')
    @include('components.alerts')
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <b>Product Prices</b>
                @if($showArchived)
                    <span class="text-secondary small ms-2">(Phased-Out View)</span>
                @endif
            </h2>
            <div>
                @if(!$showArchived)
                    <a href="{{ route('stock-ins.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>
                        New Stock In
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <!-- Search & Clear -->
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <form action="{{ route('product-prices.index') }}" method="GET" class="d-flex flex-grow-1 gap-2 align-items-center">
                            <input type="hidden" name="sort" value="{{ $sort }}">
                            <input type="hidden" name="direction" value="{{ $direction }}">
                            <div class="input-group search-box flex-grow-1">
                                <input type="text" class="form-control" name="search" placeholder="Search by product name or SKU..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            @if(request('search'))
                                <a href="{{ route('product-prices.index') }}" class="btn btn-outline-danger flex-shrink-0" title="Clear search">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                <!-- Sort & Filters -->
                <div class="col-md-6">
                    <div class="d-flex gap-2 justify-content-end">
                        <!-- Archive Toggle -->
                        @if($showArchived)
                            <a href="{{ route('product-prices.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Back to Active
                            </a>
                        @else
                            <a href="{{ route('product-prices.index', ['archived' => true]) }}" class="btn btn-outline-warning">
                                <i class="bi bi-archive me-1"></i>
                                View Phased-Out
                            </a>
                        @endif
                        
                        <!-- Sort Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-sort-down me-1"></i>Sort
                                @if($sort)
                                    <small class="ms-1">({{ $direction == 'asc' ? '↑' : '↓' }})</small>
                                @endif
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item {{ $sort == 'name' ? 'active' : '' }}" 
                                    href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => $sort == 'name' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    Name @if($sort == 'name') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                                <li><a class="dropdown-item {{ $sort == 'retail_price' ? 'active' : '' }}" 
                                    href="{{ request()->fullUrlWithQuery(['sort' => 'retail_price', 'direction' => $sort == 'retail_price' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    Retail Price @if($sort == 'retail_price') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                                <li><a class="dropdown-item {{ $sort == 'cost_price' ? 'active' : '' }}" 
                                    href="{{ request()->fullUrlWithQuery(['sort' => 'cost_price', 'direction' => $sort == 'cost_price' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    Cost Price @if($sort == 'cost_price') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Prices Table -->
    <div class="table-container">    
        <div class="table-responsive">
            <!-- Results Count -->
            <div class="text-muted mb-3">
                @if(request('search'))
                    Showing {{ $products->firstItem() }}–{{ $products->lastItem() }}
                    of {{ $products->total() }} results for
                    "<strong>{{ request('search') }}</strong>"
                @else
                    Showing {{ $products->firstItem() }}–{{ $products->lastItem() }}
                    of {{ $products->total() }} products
                @endif
            </div>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>SKU</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th class="text-end">Cost Price</th>
                        <th class="text-end">Retail Price</th>
                        <th class="text-end">Margin (%)</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr class="{{ $showArchived ? 'archived-row' : '' }}">
                        <td>{{ $product->sku }}</td>
                        <td>
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-image">
                        </td>
                        <td class="text-truncate" style="max-width: 150px;">
                            <strong>{{ $product->name }}</strong>
                            @if($product->manufacturer_barcode)
                                <br><small class="text-muted">{{ $product->manufacturer_barcode }}</small>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($product->latestStockInItem)
                                <span class="fw-semibold text-primary">
                                    ₱{{ number_format($product->latestStockInItem->actual_unit_cost, 2) }}
                                </span>
                                <br>
                                <small class="text-muted">
                                    {{ $product->latestStockInItem->stockIn->reference_no ?? 'N/A' }}
                                </small>
                            @else
                                <span class="no-price">N/A</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($product->latestProductPrice)
                                <span class="fw-bold text-success">₱{{ number_format($product->latestProductPrice->retail_price, 2) }}</span>
                            @else
                                <span class="no-price">N/A</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($product->latestProductPrice && $product->latestStockInItem && $product->latestStockInItem->actual_unit_cost > 0)
                                @php
                                    $cost = $product->latestStockInItem->actual_unit_cost;
                                    $retail = $product->latestProductPrice->retail_price;
                                    $margin = (($retail - $cost) / $cost) * 100;
                                @endphp
                                <span class="fw-bold {{ $margin >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($margin, 1) }}%
                                </span>
                            @else
                                <span class="no-price">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($product->latestProductPrice)
                                <span title="{{ $product->latestProductPrice->updated_at->format('Y-m-d') }}">
                                    {{ $product->latestProductPrice->updated_at->format('M j, Y') }}
                                </span>
                                <br>
                                <small class="text-muted">
                                    by {{ $product->latestProductPrice->updatedBy->full_name ?? 'System' }}
                                </small>
                            @else
                                <span class="no-price">Never</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($product->productPrice)
                                    <button class="btn btn-sm btn-outline-info view-price-history" 
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            title="View Price History">
                                        <i class="bi bi-clock-history"></i>
                                    </button>
                                @endif
                                @if(!$showArchived && $product->is_active)
                                    <button class="btn btn-sm btn-outline-warning edit-price" 
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            data-current-price="{{ $product->latestProductPrice ? $product->latestProductPrice->retail_price : '' }}"
                                            data-cost-price="{{ $product->latestStockInItem ? $product->latestStockInItem->actual_unit_cost : '0' }}"
                                            title="Edit Price">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="bi bi-box display-4 text-muted"></i>
                            <p class="mt-3 mb-0">No products found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $products->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- Edit Price Modal -->
    <div class="modal fade" id="editPriceModal" tabindex="-1" aria-labelledby="editPriceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editPriceForm" method="POST" action="{{ route('product-prices.update') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPriceModalLabel">
                            <span class="me-2 fs-3">₱</span>
                            Update Product Price
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control" id="editProductName" readonly>
                            <input type="hidden" id="editProductId" name="product_id">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Cost Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="text" class="form-control bg-light" id="costPrice" readonly style="cursor: not-allowed;">
                                    </div>
                                    <div class="form-text text-muted">Latest stock in cost</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="retail_price" class="form-label">Retail Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="retail_price" name="retail_price" 
                                            step="0.01" min="0" max="1000000" required
                                            oninput="validatePrice(this)">
                                    </div>
                                    <div class="form-text">Enter new selling price (max: ₱1,000,000)</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Margin Display -->
                        <div class="mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body py-2">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <label class="form-label mb-0">Profit Margin:</label>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <span id="marginDisplay" class="fw-bold fs-5">0.0%</span>
                                            <br>
                                            <small id="profitAmount" class="text-muted">₱0.00 profit</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            This will create a new price record in the price history.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Price</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Price History Modal -->
    <div class="modal fade" id="priceHistoryModal" tabindex="-1" aria-labelledby="priceHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="priceHistoryModalLabel">
                        <i class="bi bi-clock-history me-2"></i>
                        Price History
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 id="historyProductName" class="mb-3" style="word-break: break-word; white-space: normal;"></h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Retail Price</th>
                                    <th>Updated By</th>
                                    <th>Stock In Reference</th>
                                    <th>Date Updated</th>
                                </tr>
                            </thead>
                            <tbody id="priceHistoryTable">
                                <!-- Price history will be populated here -->
                            </tbody>
                        </table>
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
        // Price validation function
        function validatePrice(input) {
            const maxPrice = 1000000;
            const value = parseFloat(input.value);
            
            if (value > maxPrice) {
                input.value = maxPrice;

            } else if (value < 0) {
                input.value = 0;
            }
            
            // Calculate margin after validation
            calculateMargin();
        }

        // Edit Price
        document.querySelectorAll('.edit-price').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');
                const currentPrice = this.getAttribute('data-current-price');
                const costPrice = this.getAttribute('data-cost-price') || '0';
                
                document.getElementById('editProductId').value = productId;
                document.getElementById('editProductName').value = productName;
                document.getElementById('retail_price').value = currentPrice;
                document.getElementById('costPrice').value = parseFloat(costPrice).toFixed(2);
                document.getElementById('editPriceForm').action = '{{ route("product-prices.update") }}';
                
                // Remove any existing warning
                const existingWarning = document.getElementById('priceWarning');
                if (existingWarning) {
                    existingWarning.remove();
                }
                
                // Calculate initial margin
                calculateMargin();
                
                const modal = new bootstrap.Modal(document.getElementById('editPriceModal'));
                modal.show();
            });
        });

        // Function to calculate margin
        function calculateMargin() {
            const costPrice = parseFloat(document.getElementById('costPrice').value) || 0;
            const retailPrice = parseFloat(document.getElementById('retail_price').value) || 0;
            const marginDisplay = document.getElementById('marginDisplay');
            const profitAmount = document.getElementById('profitAmount');
            
            if (costPrice > 0 && retailPrice > 0) {
                const profit = retailPrice - costPrice;
                const marginPercentage = (profit / costPrice) * 100;
                
                // Update margin display
                marginDisplay.textContent = marginPercentage.toFixed(1) + '%';
                
                // Update profit amount
                profitAmount.textContent = `₱${profit.toFixed(2)} ${profit >= 0 ? 'profit' : 'loss'}`;
                
                // Color code based on margin
                if (marginPercentage > 0) {
                    marginDisplay.className = 'fw-bold fs-5 text-success';
                    profitAmount.className = 'text-muted text-success';
                } else if (marginPercentage < 0) {
                    marginDisplay.className = 'fw-bold fs-5 text-danger';
                    profitAmount.className = 'text-muted text-danger';
                } else {
                    marginDisplay.className = 'fw-bold fs-5 text-secondary';
                    profitAmount.className = 'text-muted';
                }
            } else {
                marginDisplay.textContent = '0.0%';
                profitAmount.textContent = '₱0.00 profit';
                marginDisplay.className = 'fw-bold fs-5 text-secondary';
                profitAmount.className = 'text-muted';
            }
        }

        // Add event listener for retail price input changes
        document.getElementById('retail_price').addEventListener('input', function() {
            validatePrice(this);
        });
        
        document.getElementById('retail_price').addEventListener('change', calculateMargin);

        // View Price History
        document.querySelectorAll('.view-price-history').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');
                
                document.getElementById('historyProductName').textContent = productName;
                
                // Fetch price history
                fetch(`/api/product-prices/${productId}/history`)
                    .then(response => response.json())
                    .then(history => {
                        const table = document.getElementById('priceHistoryTable');
                        table.innerHTML = '';
                        
                        if (history.length > 0) {
                            history.forEach(price => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td class="fw-bold">₱${parseFloat(price.retail_price).toFixed(2)}</td>
                                    <td>${price.updated_by ? price.updated_by.full_name : 'System'}</td>
                                    <td>${price.stock_in ? price.stock_in.reference_no : 'Manual Update'}</td>
                                    <td>${new Date(price.updated_at).toLocaleDateString('en-US', { 
                                        month: 'short', 
                                        day: 'numeric', 
                                        year: 'numeric' 
                                    })}</td>
                                `;
                                table.appendChild(row);
                            });
                        } else {
                            table.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No price history found</td></tr>';
                        }
                        
                        const modal = new bootstrap.Modal(document.getElementById('priceHistoryModal'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching price history:', error);
                        alert('Error loading price history');
                    });
            });
        });

        // Price form submission - add final validation
        document.getElementById('editPriceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const retailPrice = parseFloat(document.getElementById('retail_price').value);
            const maxPrice = 1000000;
            
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Price updated successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('editPriceModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update price'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error occurred');
            });
        });
    </script>
    @endpush
@endsection
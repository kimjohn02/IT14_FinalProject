@extends('layouts.app')
@section('title', 'Products - SAR EQUIP')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
<style>
    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }
    .low-stock {
        background-color: #fff3cd !important;
    }
    .out-of-stock {
        background-color: #f8d7da !important;
    }
.btn-outline-warning {
    color: #b45309;          /* darker amber */
    border-color: #b45309;
}

.btn-outline-warning:hover {
    background-color: #b45309;
    color: #fff;
}
</style>
@endpush
@section('content')
    @include('components.alerts')
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <b>Products</b>
                @if($showArchived)
                    <span class="text-secondary small ms-2">(Phased-Out View)</span>
                @endif
            </h2>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>
                Add New Product
            </a>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <!-- Search & Clear -->
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <form action="{{ route('products.index') }}" method="GET" class="d-flex flex-grow-1 me-2">
                            @if($showArchived)
                                <input type="hidden" name="archived" value="true">
                            @endif
                            <input type="hidden" name="sort" value="{{ $sort }}">
                            <input type="hidden" name="direction" value="{{ $direction }}">
                            <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                            <div class="input-group search-box w-100">
                                <input type="text" class="form-control" name="search" placeholder="Search products..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                        @if(request('search'))
                            @if($showArchived)
                                <a href="{{ route('products.index', ['archived' => true]) }}" class="btn btn-outline-danger flex-shrink-0" title="Clear search">
                            @else
                                <a href="{{ route('products.index') }}" class="btn btn-outline-danger flex-shrink-0" title="Clear search">
                            @endif
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="col-md-2">
                    <select class="form-select" onchange="window.location.href=this.value">
                        <option value="{{ request()->fullUrlWithQuery(['category_id' => null]) }}">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ request()->fullUrlWithQuery(['category_id' => $category->id]) }}" 
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Stock Filter -->
                <div class="col-md-2">
                    <select class="form-select" onchange="window.location.href=this.value">
                        <option value="{{ request()->fullUrlWithQuery(['stock_filter' => null]) }}">All Stock</option>
                        <option value="{{ request()->fullUrlWithQuery(['stock_filter' => 'okay_stock']) }}" 
                                {{ request('stock_filter') == 'okay_stock' ? 'selected' : '' }}>
                            Normal Stock
                        </option>
                        <option value="{{ request()->fullUrlWithQuery(['stock_filter' => 'low_stock']) }}" 
                                {{ request('stock_filter') == 'low_stock' ? 'selected' : '' }}>
                            Low Stock
                        </option>
                        <option value="{{ request()->fullUrlWithQuery(['stock_filter' => 'out_of_stock']) }}" 
                                {{ request('stock_filter') == 'out_of_stock' ? 'selected' : '' }}>
                            Out of Stock
                        </option>
                    </select>
                </div>

                <!-- Archive Toggle & Sort -->
                <div class="col-md-4">
                    <div class="d-flex gap-2 justify-content-end">
                        <!-- Archive Toggle -->
                        @if($showArchived)
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Back to Active
                            </a>
                        @else
                            <a href="{{ route('products.index', ['archived' => true]) }}" class="btn btn-outline-warning">
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
                                <li><a class="dropdown-item {{ $sort == 'quantity_in_stock' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'quantity_in_stock', 'direction' => $sort == 'quantity_in_stock' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    Stock @if($sort == 'quantity_in_stock') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <!-- Products Table -->
    <div class="table-container">    
        <div class="table-responsive">
            <!-- Results Count and Current Sort -->
            <div class="text-muted">
                @if(request('search'))
                    Showing {{ $products->firstItem() }}–{{ $products->lastItem() }}
                    of {{ $products->total() }} results for
                    "<strong>{{ request('search') }}</strong>"
                @else
                    Showing {{ $products->firstItem() }}–{{ $products->lastItem() }}
                    of {{ $products->total() }}
                    {{ $showArchived ? 'phased-out' : 'active' }} products
                @endif
            </div>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>SKU</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Model</th>
                        <th>Category</th>
                        <th class="text-end">Stock</th>
                        <th>Supplier</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr class="{{ $product->quantity_in_stock == 0 ? 'out-of-stock' : ($product->quantity_in_stock <= $product->reorder_level ? 'low-stock' : '') }}">
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
                        <td class="text-truncate" style="max-width: 100px;" title="{{ $product->model }}">
                            {{ $product->model ?? 'N/A' }}
                        </td>                        
                        <td class="text-truncate" style="max-width: 150px;">
                            {{ $product->category->name }}</td>
                        <td class="text-end">
                            <span class="fw-semibold {{ $product->quantity_in_stock == 0 ? 'text-danger' : ($product->quantity_in_stock <= $product->reorder_level ? 'text-warning' : 'text-success') }}">
                                {{ $product->quantity_in_stock }}
                            </span>
                        
                            @if($product->quantity_in_stock <= 0)
                                <br>
                            @elseif($product->quantity_in_stock <= $product->reorder_level)
                                <br>
                                <small class="text-warning fw-bold">
                                    LOW STOCK
                                </small>
                            @endif
                        </td>                        
                        <td class="text-truncate" style="max-width: 150px;">
                            @if($product->defaultSupplier)
                                <span class="fw-semibold">{{ $product->defaultSupplier->supplier_name }}</span>
                            @else
                                <span class="text-muted">No supplier</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-info btn-action view-product" data-id="{{ $product->id }}" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            @if($product->is_active)
                            <!-- Restock Button -->
                                <a href="{{ route('stock-ins.create', ['product_id' => $product->id]) }}" 
                                    class="btn btn-sm btn-outline-success btn-action" 
                                    title="Restock"
                                    data-bs-toggle="tooltip">
                                    <i class="bi bi-box-arrow-in-down"></i>
                                </a>
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-warning btn-action" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger btn-action archive-product" 
                                        data-id="{{ $product->id }}" 
                                        data-name="{{ $product->name }}"
                                        data-has-sales="{{ $product->hasSales() ? 'true' : 'false' }}"
                                        title="Phase Out">
                                    <i class="bi bi-archive"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-outline-success btn-action restore-product" data-id="{{ $product->id }}" data-name="{{ $product->name }}" title="Restore">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            @endif
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

    <!-- View Product Modal -->
    <div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewProductModalLabel">
                        <i class="bi bi-box me-2"></i>
                        Product Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img id="viewProductImage" src="" alt="Product Image" class="img-fluid rounded mb-3" style="max-height: 200px;">
                        </div>
                        <div class="col-md-8">
                            <!-- Compact grid layout -->
                            <div class="row g-2">
                                <div class="col-5">
                                    <small class="text-muted">Product Name:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold text-break" id="viewProductName"></span>
                                </div>

                                <div class="col-5">
                                    <small class="text-muted">Model:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold text-break" id="viewProductModel">N/A</span>
                                </div>                                

                                <div class="col-5">
                                    <small class="text-muted">SKU:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold text-break" id="viewProductSku"></span>
                                </div>
                                
                                <div class="col-5">
                                    <small class="text-muted">Description:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold text-break" id="viewProductDescription">N/A</span>
                                </div>
                                
                                <div class="col-5">
                                    <small class="text-muted">Category:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold" id="viewProductCategory"></span>
                                </div>
                                
                                <div class="col-5">
                                    <small class="text-muted">Barcode:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold" id="viewProductBarcode">N/A</span>
                                </div>
                                
                                <div class="col-5">
                                    <small class="text-muted">Stock:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold" id="viewProductStock"></span>
                                </div>
                                
                                <div class="col-5">
                                    <small class="text-muted">Reorder Level:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold" id="viewProductReorder"></span>
                                </div>
                                
                                <div class="col-5">
                                    <small class="text-muted">Supplier:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold" id="viewSupplier">N/A</span>
                                </div>

                                <div class="col-5">
                                    <small class="text-muted">Created at:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold" id="viewCreatedAt">N/A</span>
                                </div>

                                <div class="col-5">
                                    <small class="text-muted">Last Updated:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold" id="viewUpdatedAt">N/A</span>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Archive Info -->
                    <div class="mt-3 mb-1 p-2 bg-warning bg-opacity-10 rounded" id="archiveInfo" style="display: none;">
                        <small class="text-muted d-block">Phase-Out Information</small>
                        <div class="row g-2">
                            <div class="col-2">
                                <small>Phase-Out Date:</small>
                            </div>
                            <div class="col-10">
                                <small class="fw-semibold" id="viewDateDisabled"></small>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-2">
                                <small>Phase-Out By:</small>
                            </div>
                            <div class="col-10">
                                <small class="fw-semibold" id="viewDisabledBy"></small>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-2">
                                <small>Reason:</small>
                            </div>
                            <div class="col-10">
                                <small class="fw-semibold text-break" id="viewArchiveReason">N/A</small>
                            </div>
                        </div>                                      
                    </div>               
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Confirmation Modal -->
    <div class="modal fade" id="archiveProductModal" tabindex="-1" aria-labelledby="archiveProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="archiveProductForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="archiveProductModalLabel">
                            <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                            Confirm Phase Out
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-archive text-warning" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Are you sure you want to phase out this product?</h5>
                            <p class="text-muted">Product: <strong id="archiveProductName"></strong></p>
                            
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> Phased-out products will be hidden from active lists but their data is preserved.
                            </div>

                            <div class="mb-3 mt-3 text-start">
                                <label for="archive_reason" class="form-label">Reason / Notes:</label>
                                <textarea class="form-control" id="archive_reason" name="archive_reason" maxlength="255" rows="3" placeholder="Enter reason for phasing out..."></textarea>
                            </div>
                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning" id="archiveSubmitBtn">Phase Out Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div class="modal fade" id="restoreProductModal" tabindex="-1" aria-labelledby="restoreProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="restoreProductForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="restoreProductModalLabel">
                            <i class="bi bi-arrow-clockwise me-2 text-success"></i>
                            Confirm Restore
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-arrow-clockwise text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Are you sure you want to restore this product?</h5>
                            <p class="text-muted">Product: <strong id="restoreProductName"></strong></p>
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                The product will be visible in active lists again.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Restore Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // View Product
        document.querySelectorAll('.view-product').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                
                fetch(`/products/${productId}`)
                    .then(response => response.json())
                    .then(product => {                           
                        document.getElementById('viewProductName').textContent = product.name;
                        document.getElementById('viewProductModel').textContent = product.model || 'N/A';
                        document.getElementById('viewProductSku').textContent = product.sku;
                        document.getElementById('viewProductDescription').textContent = product.description || 'N/A';
                        document.getElementById('viewProductCategory').textContent = product.category.name;
                        document.getElementById('viewProductBarcode').textContent = product.manufacturer_barcode || 'N/A';
                        document.getElementById('viewProductStock').textContent = product.quantity_in_stock;
                        document.getElementById('viewProductReorder').textContent = product.reorder_level;
                        
                        const imageElement = document.getElementById('viewProductImage');
                        if (product.image_url) {
                            imageElement.src = product.image_url;
                            imageElement.alt = product.name;
                            imageElement.style.display = 'block';
                        } else {
                            imageElement.src = '/images/no-image.jpg';
                            imageElement.alt = 'No image available';
                        }
                        
                        // Handle supplier display
                        const supplierElement = document.getElementById('viewSupplier');
                        if (product.default_supplier) {
                            supplierElement.textContent = product.default_supplier.supplier_name;
                        } else {
                            supplierElement.textContent = 'No supplier assigned';
                        }
        
                        const createdAtElement = document.getElementById('viewCreatedAt');
                        const updatedAtElement = document.getElementById('viewUpdatedAt');
                        if (product.created_at) {
                            const createdDate = new Date(product.created_at);
                            createdAtElement.textContent = createdDate.toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                        } else {
                            createdAtElement.textContent = 'N/A';
                        }
        
                        if (product.updated_at) {
                            const updatedDate = new Date(product.updated_at);
                            updatedAtElement.textContent = updatedDate.toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                        } else {
                            updatedAtElement.textContent = 'N/A';
                        }
        
                        // Handle archive info
                        if (product.is_active) {
                            document.getElementById('archiveInfo').style.display = 'none';
                        } else {
                            document.getElementById('archiveInfo').style.display = 'block';
                            
                            // Date disabled
                            if (product.date_disabled) {
                                document.getElementById('viewDateDisabled').textContent = 
                                new Date(product.date_disabled).toLocaleDateString('en-US', { 
                                    month: 'short', 
                                    day: 'numeric', 
                                    year: 'numeric' 
                                });
                            } else {
                                document.getElementById('viewDateDisabled').textContent = 'N/A';
                            }
                            
                            // Disabled by
                            if (product.disabled_by && product.disabled_by.full_name) {
                                document.getElementById('viewDisabledBy').textContent = product.disabled_by.full_name;
                            } else if (product.disabled_by_user_id) {
                                document.getElementById('viewDisabledBy').textContent = 'User #' + product.disabled_by_user_id;
                            } else {
                                document.getElementById('viewDisabledBy').textContent = 'System';
                            }

                            // Archive reason
                            document.getElementById('viewArchiveReason').textContent = product.archive_reason || 'N/A';
                        }
                        
                        const modal = new bootstrap.Modal(document.getElementById('viewProductModal'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching product:', error);
                    });
            });
        });
        
        // Archive Product
        document.querySelectorAll('.archive-product').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const productName = this.getAttribute('data-name');
                const hasSales = this.getAttribute('data-has-sales') === 'true';
                
                document.getElementById('archiveProductName').textContent = productName;
                document.getElementById('archiveProductForm').action = `/products/${productId}/archive`;
                
                const submitBtn = document.getElementById('archiveSubmitBtn');
                
                const modal = new bootstrap.Modal(document.getElementById('archiveProductModal'));
                modal.show();
            });
        });
        
        // Restore Product
        document.querySelectorAll('.restore-product').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const productName = this.getAttribute('data-name');
                
                document.getElementById('restoreProductName').textContent = productName;
                document.getElementById('restoreProductForm').action = `/products/${productId}/restore`;
                
                const modal = new bootstrap.Modal(document.getElementById('restoreProductModal'));
                modal.show();
            });
        });
        </script>
    @endpush
@endsection
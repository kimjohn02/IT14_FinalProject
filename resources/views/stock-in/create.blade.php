@extends('layouts.app')
@section('title', 'New Stock In - SAR EQUIP')

@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
<style>
    .stockin-panel {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 20px;
        background-color: #f8f9fa;
    }
    .item-row {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        background-color: white;
    }
    .remove-item {
        color: #dc3545;
        cursor: pointer;
    }
    .autofill-highlight {
        background-color: #e8f5e8 !important;
        border-color: #28a745 !important;
    }

    /* Select2 height fix to match Bootstrap */
    .select2-container .select2-selection--single {
        height: 37.6px !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 35px !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 35px !important;
    }
    
    .select2-container--default .select2-selection--single {
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
    }
    
    /* Fix X button alignment */
    .select2-container--default .select2-selection--single .select2-selection__clear {
        margin-top: 8px !important;
        margin-right: 25px !important;
        height: 20px !important;
        width: 20px !important;
        font-size: 16px !important;
        line-height: 1 !important;
    }
    
    /* Adjust arrow position to accommodate X button */
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        margin-top: -2px !important;
    }
</style>
@endpush

@section('content')
    @include('components.alerts')

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <a href="{{ route('stock-ins.index') }}" class="text-decoration-none text-dark">
                    <b class="underline">Stock In</b>
                </a>
                > Process New Stock In
            </h2>
            <a href="{{ route('stock-ins.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Stock In
            </a>
        </div>
    </div>

    <!-- Single Stock In Panel -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Reference No. <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="reference_no" name="reference_no" placeholder="Invoice/Delivery Receipt Number" required>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Stock In Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="stock_in_date" name="stock_in_date" value="{{ now()->format('Y-m-d\TH:i') }}" max="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Received By</label>
                        <input type="text" class="form-control" value="{{ session('user_name') ?? 'Current User' }}" readonly>
                        <input type="hidden" id="received_by_user_id" name="received_by_user_id" value="{{ session('user_id') ?? '' }}">
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6>Products</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-item">
                        <i class="bi bi-plus-circle me-1"></i> Add Product
                    </button>
                </div>
                <div id="items-container">
                    <!-- Items will be added here dynamically -->
                </div>
            </div>

            <!-- Post Button -->
            <div class="d-flex justify-content-end mt-4 gap-2">
                <a href="{{ route('stock-ins.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="button" class="btn btn-success" id="post-shipment">
                    Post Shipment
                </button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Confirm Stock In
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-box-seam text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Are you sure you want to post this shipment?</h5>
                    <p class="text-muted">This action will permanently update inventory and pricing.</p>
                    <div class="alert alert-warning mt-3">
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                    <div id="confirmationSummary" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmPost">Confirm and Post</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let itemCount = 0;
            const urlParams = new URLSearchParams(window.location.search);
            const preSelectedProductId = urlParams.get('product_id');

            // Initialize with one empty row OR pre-filled row
            document.addEventListener('DOMContentLoaded', () => {
                if (preSelectedProductId) {
                    // Auto-add the pre-selected product
                    addItemRow(preSelectedProductId);
                } else {
                    // Normal empty row
                    addItemRow();
                }
            });
            const addedProducts = new Set();
        
            // Product data from Laravel
            const PRODUCTS_DATA = @php 
            echo json_encode($products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'latest_unit_cost' => $product->latest_unit_cost,
                    'default_supplier_id' => $product->default_supplier_id,
                    'default_supplier_name' => $product->defaultSupplier ? $product->defaultSupplier->supplier_name : null,
                    'current_retail_price' => $product->latestProductPrice ? $product->latestProductPrice->retail_price : null,
                ];
            }));
        @endphp;

        const ALL_SUPPLIERS = @json($suppliers->map(function($supplier) {
            return [
                'id' => $supplier->id,
                'supplier_name' => $supplier->supplier_name
            ];
        }));
        
            // Add item row
            function addItemRow(productId = '') {
                itemCount++;
                const container = document.getElementById('items-container');

                const itemHtml = `
                    <div class="item-row" id="item-${itemCount}">
                        <div class="row">
                            <!-- Product Selection -->
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Product <span class="text-danger">*</span></label>
                                    <select class="form-select product-select" name="items[${itemCount}][product_id]" required>
                                        <option value="">Select Product</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Supplier Selection -->
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                    <select class="form-select supplier-select" name="items[${itemCount}][supplier_id]" required>
                                        <option value="">Select Supplier</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Quantity -->
                            <div class="col-md-1">
                                <div class="mb-3">
                                    <label class="form-label">Qty <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="items[${itemCount}][quantity_received]" min="1" required>
                                </div>
                            </div>

                            <!-- Unit Cost -->
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Unit Cost <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control unit-cost" name="items[${itemCount}][actual_unit_cost]" step="0.01" min="0" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Retail Price -->
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Retail Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control retail-price" name="items[${itemCount}][retail_price]" step="0.01" min="0" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Remove Button -->
                            <div class="col-md-1">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-outline-danger w-100" onclick="removeItem(${itemCount})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                container.insertAdjacentHTML('beforeend', itemHtml);

                // Initialize Select2 for product dropdown
                const productSelect = $(`#item-${itemCount} .product-select`);
                productSelect.select2({
                    placeholder: "Search for a product...",
                    allowClear: true,
                    data: PRODUCTS_DATA.map(product => ({
                        id: product.id,
                        text: `${product.name} (${product.sku})`,
                        cost: product.latest_unit_cost,
                        supplier: product.default_supplier_id,
                        price: product.current_retail_price
                        // REMOVED: suppliers data
                    }))
                });

                // Initialize Select2 for supplier dropdown (show ALL suppliers)
                const supplierSelect = $(`#item-${itemCount} .supplier-select`);
                supplierSelect.select2({
                    placeholder: "Select supplier...",
                    allowClear: true,
                    data: ALL_SUPPLIERS.map(supplier => ({
                        id: supplier.id,
                        text: supplier.supplier_name
                    }))
                });

                // Track the current product for this row
                let currentProductId = null;

                // Handle product selection change
                const thisItemId = itemCount;

                productSelect.on('change', function () {
                    const newProductId = this.value;

                    if (currentProductId) {
                        addedProducts.delete(parseInt(currentProductId));
                    }

                    handleProductChange(thisItemId, newProductId);

                    currentProductId = newProductId;
                });

                // Auto-select product if provided
                if (productId) {
                    setTimeout(() => {
                        productSelect.val(productId).trigger('change');
                    }, 100);
                }
            }
        
            // Handle product selection change
            function handleProductChange(itemId, productId) {
                const itemRow = document.getElementById(`item-${itemId}`);
                
                if (!productId) {
                    // Product was cleared, reset fields but keep all suppliers available
                    resetItemFields(itemId);
                    return;
                }

                // Check for duplicate product
                if (addedProducts.has(parseInt(productId))) {
                    alert('This product has already been added to the shipment.');
                    $(`#item-${itemId} .product-select`).val('').trigger('change');
                    return;
                }
                
                // Add the new product to tracking
                addedProducts.add(parseInt(productId));

                // Find product data
                const product = PRODUCTS_DATA.find(p => p.id == productId);
                if (!product) return;

                // Auto-select default supplier if it exists
                if (product.default_supplier_id) {
                    $(`#item-${itemId} .supplier-select`).val(product.default_supplier_id).trigger('change');
                }

                // Auto-fill unit cost
                const unitCostInput = itemRow.querySelector('.unit-cost');
                if (product.latest_unit_cost) {
                    unitCostInput.value = product.latest_unit_cost;
                    unitCostInput.classList.add('autofill-highlight');
                    setTimeout(() => unitCostInput.classList.remove('autofill-highlight'), 2000);
                }

                // Auto-fill retail price
                const retailPriceInput = itemRow.querySelector('.retail-price');
                if (product.current_retail_price) {
                    retailPriceInput.value = product.current_retail_price;
                    retailPriceInput.classList.add('autofill-highlight');
                    setTimeout(() => retailPriceInput.classList.remove('autofill-highlight'), 2000);
                }
            }

            // Reset item fields when product is cleared
            function resetItemFields(itemId) {
                const itemRow = document.getElementById(`item-${itemId}`);
                
                // Clear unit cost and retail price
                itemRow.querySelector('.unit-cost').value = '';
                itemRow.querySelector('.retail-price').value = '';
                
                // Clear supplier selection but keep all suppliers available
                $(`#item-${itemId} .supplier-select`).val('').trigger('change');
            }
        
            function removeItem(itemId) {
                const row = document.getElementById(`item-${itemId}`);
                const select = $(row).find('.product-select');
                const productId = select.val();
                
                // Remove product from tracking
                if (productId) {
                    addedProducts.delete(parseInt(productId));
                }
                
                // Destroy Select2 before removing
                select.select2('destroy');
                $(row).find('.supplier-select').select2('destroy');
                row.remove();
            }
        
            // Add item button
            document.getElementById('add-item').addEventListener('click', () => addItemRow());
        
            // Post Shipment
            document.getElementById('post-shipment').addEventListener('click', function() {
                const items = document.querySelectorAll('.item-row');
                if (items.length === 0) {
                    alert('Please add at least one item.');
                    return;
                }
        
                const referenceNo = document.getElementById('reference_no').value;
                if (!referenceNo) {
                    alert('Please enter a reference number.');
                    return;
                }

                // Validate all items have required fields
                let hasErrors = false;
                items.forEach((item, index) => {
                    const productSelect = $(item).find('.product-select');
                    const supplierSelect = $(item).find('.supplier-select');
                    const quantity = item.querySelector('input[name*="quantity_received"]').value;
                    const cost = item.querySelector('.unit-cost').value;
                    const price = item.querySelector('.retail-price').value;
                    
                    if (!productSelect.val() || !supplierSelect.val() || !quantity || !cost || !price) {
                        alert(`Item ${index + 1} has missing fields. Please fill in all required fields.`);
                        hasErrors = true;
                    }
                });

                if (hasErrors) return;
        
                // Build confirmation summary
                let summary = `<strong>Reference:</strong> ${referenceNo}<br>`;
                summary += `<strong>Items:</strong> ${items.length}<br><br>`;
                
                items.forEach((item, index) => {
                    const productSelect = $(item).find('.product-select');
                    const supplierSelect = $(item).find('.supplier-select');
                    const quantity = item.querySelector('input[name*="quantity_received"]').value;
                    const cost = item.querySelector('.unit-cost').value;
                    const price = item.querySelector('.retail-price').value;
                    
                    const productName = productSelect.select2('data')[0]?.text || 'Unknown Product';
                    const supplierName = supplierSelect.select2('data')[0]?.text || 'Unknown Supplier';
                    summary += `
                    <div style="margin-bottom: 10px;">
                        <strong>Item ${index + 1}:</strong><br>
                        <div style="word-break: break-word; margin-left: 10px;">
                            <strong>Product:</strong> ${productName}<br>
                            <strong>Supplier:</strong> ${supplierName}<br>
                            <strong>Quantity:</strong> ${quantity}<br>
                            <strong>Unit Cost:</strong> ₱${cost}<br>
                            <strong>Retail Price:</strong> ₱${price}
                        </div>
                    </div>
                `;});
        
                document.getElementById('confirmationSummary').innerHTML = summary;
                new bootstrap.Modal(document.getElementById('confirmationModal')).show();
            });
        
            document.getElementById('confirmPost').addEventListener('click', function() {
                const formData = new FormData();
                let hasErrors = false;
        
                // Basic data
                formData.append('reference_no', document.getElementById('reference_no').value);
                formData.append('stock_in_date', document.getElementById('stock_in_date').value);
                formData.append('received_by_user_id', document.getElementById('received_by_user_id').value);
        
                // Items data
                document.querySelectorAll('.item-row').forEach((item, index) => {
                    const productId = $(item).find('.product-select').val();
                    const supplierId = $(item).find('.supplier-select').val();
                    const quantity = item.querySelector('input[name*="quantity_received"]').value;
                    const actualUnitCost = item.querySelector('.unit-cost').value;
                    const retailPrice = item.querySelector('.retail-price').value;
        
                    if (!productId || !supplierId || !quantity || !actualUnitCost || !retailPrice) {
                        alert(`Item ${index + 1} has missing fields.`);
                        hasErrors = true;
                        return;
                    }
        
                    formData.append(`items[${index}][product_id]`, productId);
                    formData.append(`items[${index}][supplier_id]`, supplierId);
                    formData.append(`items[${index}][quantity_received]`, quantity);
                    formData.append(`items[${index}][actual_unit_cost]`, actualUnitCost);
                    formData.append(`items[${index}][retail_price]`, retailPrice);
                });
        
                if (hasErrors) return;
        
                // Submit the form
                fetch('{{ route("stock-ins.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Stock In posted successfully!');
                        window.location = "{{ route('stock-ins.index') }}";
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error occurred'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error: ' + error.message);
                });
            });
        </script>
    @endpush
@endsection
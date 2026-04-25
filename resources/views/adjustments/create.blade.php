    @extends('layouts.app')
    @section('title', 'New Stock Adjustment - SAR EQUIP')

    @push('styles')
    <link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
    <style>
        .adjustment-panel {
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
        .negative-quantity {
            color: #dc3545;
            font-weight: bold;
        }
        .positive-quantity {
            color: #198754;
            font-weight: bold;
        }
        .current-stock {
            font-size: 0.875rem;
            color: #6c757d;
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
    </style>
    @endpush

    @section('content')
        @include('components.alerts')

        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <a href="{{ route('stock-adjustments.index') }}" class="text-decoration-none text-dark">
                        <b class="underline">Stock Adjustments</b>
                    </a>
                    > Create New Adjustment
                </h2>
                <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Adjustments
                </a>
            </div>
        </div>

        <!-- Stock Adjustment Panel -->
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="adjustment_type" name="adjustment_type" required>
                                <option value="">Select Adjustment Type</option>
                                <option value="Physical Count">Physical Count</option>
                                <option value="Damage/Scrap">Damage/Scrap</option>
                                <option value="Internal Use">Internal Use</option>
                                <option value="Error Correction">Error Correction</option>
                                <option value="Found Stock">Found Stock</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reason Notes <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reason_notes" name="reason_notes" rows="3" placeholder="Detailed explanation for this adjustment..."  maxlength="250" required></textarea>
                            <div class="form-text text-start">Maximum 250 characters</div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Adjustment Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="adjustment_date" name="adjustment_date" value="{{ now()->format('Y-m-d\TH:i') }}" max="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Processed By</label>
                            <input type="text" class="form-control" value="{{ session('user_name') ?? 'Current User' }}" readonly>
                            <input type="hidden" id="processed_by_user_id" name="processed_by_user_id" value="{{ session('user_id') ?? '' }}">
                        </div>
                    </div>
                </div>

                <!-- Items Section -->
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6>Adjustment Items</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-item">
                            <i class="bi bi-plus-circle me-1"></i> Add Item
                        </button>
                    </div>
                    <div id="items-container">
                        <!-- Items will be added here dynamically -->
                    </div>
                </div>

                <!-- Post Button -->
                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('stock-adjustments.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="button" class="btn btn-success" id="post-adjustment">
                        Post Adjustment
                    </button>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Confirm Stock Adjustment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="bi bi-clipboard-check text-warning" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Are you sure you want to post this adjustment?</h5>
                            <p class="text-muted">This action will permanently update inventory levels and financial records.</p>
                        </div>
                        
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> This action cannot be undone and will directly impact your inventory valuation.
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Adjustment Type:</strong> <span id="confirm_type"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Adjustment Date:</strong> <span id="confirm_date"></span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Reason:</strong>
                            <div style="word-wrap: break-word; white-space: pre-wrap; overflow-wrap: break-word;" id="confirm_reason"></div>
                        </div>

                        <h6>Items Summary:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Quantity Change</th>
                                        <th class="text-end">Unit Cost</th>
                                        <th class="text-end">Total Value</th>
                                    </tr>
                                </thead>
                                <tbody id="confirmationItems">
                                    <!-- Items will be populated here -->
                                </tbody>
                                <tfoot>
                                    <tr class="table-active">
                                        <td colspan="3" class="text-end"><strong>Net Financial Impact:</strong></td>
                                        <td><strong id="confirm_total_impact">₱0.00</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
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
            const addedProducts = new Set();
        
            // Product data from Laravel
            const PRODUCTS_DATA = @php 
            echo json_encode($products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'latest_unit_cost' => $product->latest_unit_cost,
                    'quantity_in_stock' => $product->quantity_in_stock
                ];
            }));
            @endphp;
        
            // Add item row
            function addItemRow(productId = '') {
                itemCount++;
                const container = document.getElementById('items-container');

                const itemHtml = `
                    <div class="item-row" id="item-${itemCount}">
                        <div class="row">
                            <!-- Product Selection -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Product <span class="text-danger">*</span></label>
                                    <select class="form-select product-select" name="items[${itemCount}][product_id]" required>
                                        <option value="">Select Product</option>
                                    </select>
                                    <div class="current-stock mt-1" id="current-stock-${itemCount}"></div>
                                </div>
                            </div>

                            <!-- Quantity Change -->
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Qty Change <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control quantity-change" name="items[${itemCount}][quantity_change]" required 
                                        placeholder="e.g., -5 or +3" step="1">
                                    <small class="form-text text-muted">Use - for removal, + for addition</small>
                                </div>
                            </div>
                            
                            <!-- Unit Cost (Read-only) -->
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Unit Cost</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control unit-cost" name="items[${itemCount}][unit_cost_at_adjustment]" 
                                            step="0.01" readonly style="background-color: #e9ecef;">
                                    </div>
                                </div>
                            </div>

                            <!-- Total Value (Read-only) -->
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Total Value</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control total-value" step="0.01" readonly style="background-color: #e9ecef;">
                                    </div>
                                </div>
                            </div>

                            <!-- Remove Button -->
                            <div class="col-md-2">
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
                        text: `${product.name} (${product.sku}) - Stock: ${product.quantity_in_stock}`,
                        cost: product.latest_unit_cost,
                        stock: product.quantity_in_stock
                    }))
                });

                // Track the current product for this row
                let currentProductId = null;

                // Handle product selection change
                productSelect.on('change', function() {
                    const newProductId = this.value;
                    
                    // Remove old product from tracking if it exists
                    if (currentProductId) {
                        addedProducts.delete(parseInt(currentProductId));
                    }
                    
                    // Handle the new product selection
                    handleProductChange(itemCount, newProductId);
                    
                    // Update current product tracking
                    currentProductId = newProductId;
                });

                // Handle quantity change
                const quantityInput = document.querySelector(`#item-${itemCount} .quantity-change`);
                quantityInput.addEventListener('input', function() {
                    handleQuantityChange(itemCount);
                });

                // Also add blur event for when user leaves the field
                quantityInput.addEventListener('blur', function() {
                    handleQuantityChange(itemCount);
                });

                // Auto-select product if provided
                if (productId) {
                    productSelect.val(productId).trigger('change');
                }
            }
        
            // Handle product selection change
            function handleProductChange(itemId, productId) {
                const itemRow = document.getElementById(`item-${itemId}`);
                const stockDisplay = document.getElementById(`current-stock-${itemId}`);
                const quantityInput = itemRow.querySelector('.quantity-change');
                
                if (!productId) {
                    // Product was cleared
                    stockDisplay.textContent = '';
                    itemRow.querySelector('.unit-cost').value = '';
                    itemRow.querySelector('.total-value').value = '';
                    quantityInput.removeAttribute('data-max-negative');
                    return;
                }

                // Check for duplicate product
                if (addedProducts.has(parseInt(productId))) {
                    alert('This product has already been added to the adjustment.');
                    $(`#item-${itemId} .product-select`).val('').trigger('change');
                    return;
                }
                
                // Add the new product to tracking
                addedProducts.add(parseInt(productId));

                // Find product data
                const product = PRODUCTS_DATA.find(p => p.id == productId);
                if (!product) return;

                // Update current stock display
                stockDisplay.textContent = `Current Stock: ${product.quantity_in_stock}`;

                // Store the current stock as data attribute for validation
                quantityInput.setAttribute('data-max-negative', product.quantity_in_stock * -1);

                // Auto-fill unit cost
                const unitCostInput = itemRow.querySelector('.unit-cost');
                if (product.latest_unit_cost) {
                    unitCostInput.value = product.latest_unit_cost;
                    unitCostInput.classList.add('autofill-highlight');
                    setTimeout(() => unitCostInput.classList.remove('autofill-highlight'), 2000);
                }

                // Calculate total value if quantity is already entered
                calculateTotalValue(itemId);
            }

            // Handle quantity change with validation
            // Handle quantity change with validation
            function handleQuantityChange(itemId) {
                const itemRow = document.getElementById(`item-${itemId}`);
                const quantityInput = itemRow.querySelector('.quantity-change');
                const maxNegative = parseFloat(quantityInput.getAttribute('data-max-negative')) || 0;
                const currentValue = parseFloat(quantityInput.value) || 0;

                // Remove warning class
                quantityInput.classList.remove('quantity-warning');
                
                // Check if trying to remove more than available stock
                if (currentValue < maxNegative) {
                    quantityInput.classList.add('quantity-warning');
                    
                    // Auto-correct on blur
                    if (document.activeElement !== quantityInput) {
                        alert(`Cannot remove more than available stock. Maximum removal: ${Math.abs(maxNegative)}`);
                        quantityInput.value = maxNegative;
                    }
                }

                calculateTotalValue(itemId);
                updateQuantityDisplay(itemId);
            }

            // Calculate total value for an item
            function calculateTotalValue(itemId) {
                const itemRow = document.getElementById(`item-${itemId}`);
                const quantityInput = itemRow.querySelector('.quantity-change');
                const unitCostInput = itemRow.querySelector('.unit-cost');
                const totalValueInput = itemRow.querySelector('.total-value');

                const quantity = parseFloat(quantityInput.value) || 0;
                const unitCost = parseFloat(unitCostInput.value) || 0;

                totalValueInput.value = (quantity * unitCost).toFixed(2);
            }

            // Update quantity display style
            function updateQuantityDisplay(itemId) {
                const itemRow = document.getElementById(`item-${itemId}`);
                const quantityInput = itemRow.querySelector('.quantity-change');
                const quantity = parseFloat(quantityInput.value) || 0;

                // Remove existing classes
                quantityInput.classList.remove('negative-quantity', 'positive-quantity');
                
                // Add appropriate class
                if (quantity < 0) {
                    quantityInput.classList.add('negative-quantity');
                } else if (quantity > 0) {
                    quantityInput.classList.add('positive-quantity');
                }
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
                row.remove();
            }
        
            // Add item button
            document.getElementById('add-item').addEventListener('click', () => addItemRow());
        
            // Post Adjustment
            document.getElementById('post-adjustment').addEventListener('click', function() {
                const items = document.querySelectorAll('.item-row');
                if (items.length === 0) {
                    alert('Please add at least one adjustment item.');
                    return;
                }
        
                const adjustmentType = document.getElementById('adjustment_type').value;
                const reasonNotes = document.getElementById('reason_notes').value;
                
                if (!adjustmentType) {
                    alert('Please select an adjustment type.');
                    return;
                }

                if (!reasonNotes.trim()) {
                    alert('Please enter reason notes for this adjustment.');
                    return;
                }
        
                // Build confirmation summary
                document.getElementById('confirm_type').textContent = adjustmentType;
                document.getElementById('confirm_date').textContent = document.getElementById('adjustment_date').value;
                document.getElementById('confirm_reason').textContent = reasonNotes;

                let itemsHtml = '';
                let totalFinancialImpact = 0;
                let hasErrors = false;

                items.forEach((item, index) => {
                    const productSelect = $(item).find('.product-select');
                    const quantity = item.querySelector('.quantity-change').value;
                    const unitCost = item.querySelector('.unit-cost').value;
                    const totalValue = item.querySelector('.total-value').value;
                    
                    if (productSelect.val() && quantity) {
                        const productName = productSelect.select2('data')[0]?.text.split(' - Stock:')[0] || 'Unknown Product';
                        const quantityNum = parseFloat(quantity);
                        const totalValueNum = parseFloat(totalValue);
                        totalFinancialImpact += totalValueNum;

                        const quantityClass = quantityNum < 0 ? 'negative-quantity' : 'positive-quantity';
                        const quantityDisplay = quantityNum > 0 ? `+${quantityNum}` : quantityNum;

                        itemsHtml += `
                            <tr>
                                 <td style="word-break: break-word; max-width: 250px;">
                                    <div style="word-break: break-word; white-space: normal;">
                                        ${productName}
                                    </div>
                                </td>
                                <td class="text-end ${quantityClass}">${quantityDisplay}</td>
                                <td class="text-end">₱${parseFloat(unitCost).toFixed(2)}</td>
                                <td class=" text-end ${quantityNum < 0 ? 'negative-quantity' : 'positive-quantity'}">₱${totalValueNum.toFixed(2)}</td>
                            </tr>
                        `;
                    } else {
                        hasErrors = true;
                    }
                });

                if (hasErrors) {
                    alert('Some items have missing fields. Please check all items.');
                    return;
                }

                document.getElementById('confirmationItems').innerHTML = itemsHtml;
                document.getElementById('confirm_total_impact').textContent = `₱${totalFinancialImpact.toFixed(2)}`;
                document.getElementById('confirm_total_impact').className = totalFinancialImpact < 0 ? 'negative-quantity' : 'positive-quantity';

                new bootstrap.Modal(document.getElementById('confirmationModal')).show();
            });
        
            document.getElementById('confirmPost').addEventListener('click', function() {
                const formData = new FormData();
                let hasErrors = false;
        
                // Basic data
                formData.append('adjustment_date', document.getElementById('adjustment_date').value);
                formData.append('adjustment_type', document.getElementById('adjustment_type').value);
                formData.append('reason_notes', document.getElementById('reason_notes').value);
                formData.append('processed_by_user_id', document.getElementById('processed_by_user_id').value);
        
                // Items data
                document.querySelectorAll('.item-row').forEach((item, index) => {
                    const productId = $(item).find('.product-select').val();
                    const quantityChange = item.querySelector('.quantity-change').value;
                    const unitCostAtAdjustment = item.querySelector('.unit-cost').value;

                    if (!productId || !quantityChange || !unitCostAtAdjustment) {
                        alert(`Item ${index + 1} has missing fields.`);
                        hasErrors = true;
                        return;
                    }

                    formData.append(`items[${index}][product_id]`, productId);
                    formData.append(`items[${index}][quantity_change]`, quantityChange);
                    formData.append(`items[${index}][unit_cost_at_adjustment]`, unitCostAtAdjustment);
                });
        
                if (hasErrors) return;
        
                // Submit the form
                fetch('{{ route("stock-adjustments.store") }}', {
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
                        alert('Stock Adjustment posted successfully!');
                        window.location = "{{ route('stock-adjustments.index') }}";
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error occurred'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error: ' + error.message);
                });
            });
        
            // Initialize with one empty row
            document.addEventListener('DOMContentLoaded', () => {
                addItemRow();
            });
        </script>
        @endpush
    @endsection
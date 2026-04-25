@extends('layouts.app')
@section('title', 'Process New Return - SAR EQUIP')

@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
<style>
    .return-panel {
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
</style>
@endpush

@section('content')
    @include('components.alerts')

    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <a href="{{ route('returns.index') }}" class="text-decoration-none text-dark">
                    <b class="underline">Product Returns</b>
                </a>
                > Process New Return
            </h2>
            <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Returns
            </a>
        </div>
    </div>

    <!-- Step 1: Locate Sale -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Locate Original Sale</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="sale_id" class="form-label">Enter Sale ID</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="sale_id" 
                                   placeholder="Enter the original Sale ID">
                            <button type="button" class="btn btn-primary" id="lookup-sale">
                                <i class="bi bi-search me-1"></i> Lookup Sale
                            </button>
                        </div>
                        <div class="form-text">Enter the Sale ID from the original receipt.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sale Details (Hidden until sale is found) -->
    <div id="sale-details-section" class="d-none">
        <!-- Sale Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Original Sale Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-2">
                            <small class="text-muted d-block">Sale ID</small>
                            <strong id="sale-id-display">-</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <small class="text-muted d-block">Sale Date</small>
                            <strong id="sale-date-display">-</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <small class="text-muted d-block">Customer</small>
                            <strong id="customer-name-display">-</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <small class="text-muted d-block">Contact</small>
                            <strong id="customer-contact-display">-</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Return Form -->
        <form id="return-form" method="POST" action="{{ route('returns.store') }}">
            @csrf
            <input type="hidden" name="sale_id" id="form-sale-id">
            
            <!-- Step 2: Select Items & Condition -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Select Items & Condition</h5>
                    <p class="text-muted mb-0 small">Enter 0 for items you don't want to return</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <!-- REMOVED CHECKBOX COLUMN HEADER -->
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th class="text-end">Original Price</th>
                                    <th>Quantity Sold</th>
                                    <th>Already Returned</th>
                                    <th>Return Quantity</th>
                                    <th>Condition</th>
                                    <th class="text-end">Refund Amount</th>
                                </tr>
                            </thead>
                            <tbody id="return-items-tbody">
                                <!-- Items will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Step 3: Finalize Financials -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Finalize Financials</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="return_reason" class="form-label">Return Reason <span class="text-danger">*</span></label>
                                <select class="form-select" id="return_reason" name="return_reason" required>
                                    <option value="">Select Reason</option>
                                    <option value="Defective">Defective Product</option>
                                    <option value="Wrong Item">Wrong Item Received</option>
                                    <option value="Customer Change Mind">Customer Changed Mind</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Return Date</label>
                                <input type="text" class="form-control" 
                                    value="{{ now()->format('M d, Y h:i A') }}" 
                                    readonly 
                                    style="background-color: #f8f9fa;">
                                <input type="hidden" id="return_date" name="return_date" 
                                    value="{{ now()->format('Y-m-d H:i:s') }}">
                                <div class="form-text">Automatically recorded</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="processed_by" class="form-label">Processed By</label>
                                <input type="text" class="form-control" value="{{ session('user_name') ?? 'Current User' }}" readonly>
                                <input type="hidden" id="processed_by_user_id" name="processed_by_user_id" value="{{ session('user_id') ?? '' }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- KEEP ONLY ONE REFUND METHOD FIELD -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="refund_method" class="form-label">Refund Method <span class="text-danger">*</span></label>
                                <select class="form-select" id="refund_method" name="refund_method" required>
                                    <option value="">Select Method</option>
                                    <option value="Cash">Cash</option>
                                    <option value="GCash">GCash</option>
                                    <option value="Card">Card</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- ADD A NEW FIELD HERE INSTEAD OF THE DUPLICATE -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Total Refund Amount</label>
                                <input type="text" class="form-control" id="total-refund-display" 
                                    value="₱0.00" readonly style="background-color: #f8f9fa; font-weight: bold;">
                                <div class="form-text">Calculated based on selected items</div>
                            </div>
                        </div>
                    </div>

                    <!-- Reference Number Field (shown only for GCash and Card) -->
                    <div class="row" id="reference_no_field" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference_no" class="form-label">Reference Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="reference_no" name="reference_no" 
                                    placeholder="Enter transaction reference number">
                                <div class="form-text">Required for GCash and Card refunds</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                placeholder="Additional notes about this return..." maxlength="250"></textarea>
                        <div class="form-text text-start">Maximum 250 characters</div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="alert alert-info mb-0">
                                <h6 class="alert-heading">Refund Summary</h6>
                                <p class="mb-1"><strong>Total Refund Amount:</strong> ₱<span id="total-refund-amount">0.00</span></p>
                                <small class="d-block mt-1">Items marked as "Damaged" will not be restocked.</small>
                            </div>
                        </div>
                    </div>
                    <!-- Submit Button -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('returns.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="button" class="btn btn-success" id="process-return-btn">
                            Process Return & Issue Refund
                        </button>
                    </div>
                </div>
            </div>  
        </form>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Return Processing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-arrow-return-left text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Are you sure you want to process this return?</h5>
                    <p class="text-muted">This action will issue a refund and update inventory records.</p>
                    <div class="alert alert-warning mt-3">
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                    <div id="confirmationSummary" class="mt-3">
                        <p><strong>Refund Amount:</strong> ₱<span id="modal-refund-amount">0.00</span></p>
                        <p><strong>Refund Method:</strong> <span id="modal-refund-method">-</span></p>
                        <p><strong>Items to Return:</strong> <span id="modal-items-count">0</span> item(s)</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmReturn">Confirm and Process Return</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Alert -->
    <div id="error-alert" class="alert alert-danger d-none" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i> <span id="error-message"></span>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentSale = null;

    $('#refund_method').change(function() {
        const method = $(this).val();
        const referenceField = $('#reference_no_field');
        
        if (method === 'GCash' || method === 'Card') {
            referenceField.show();
            $('#reference_no').prop('required', true);
        } else {
            referenceField.hide();
            $('#reference_no').prop('required', false);
            $('#reference_no').val('');
        }
    });

    $('#refund_method').trigger('change');

    // Lookup sale
    $('#lookup-sale').click(function() {
        const saleId = $('#sale_id').val().trim();
        
        if (!saleId) {
            showError('Please enter a Sale ID');
            return;
        }

        $('#lookup-sale').prop('disabled', true).html('<i class="bi bi-search me-1"></i> Searching...');

        // Build the URL
        const url = `/returns/get-sale/${saleId}`;
        console.log('Looking up sale:', saleId, 'URL:', url); // Debug
        
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                console.log('Response received:', response); // Debug
                $('#lookup-sale').prop('disabled', false).html('<i class="bi bi-search me-1"></i> Lookup Sale');
                
                if (response.success) {
                    currentSale = response.sale;
                    displaySaleDetails();
                    $('#error-alert').addClass('d-none');
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', error, 'Status:', status, 'Response:', xhr.responseText); // Debug
                $('#lookup-sale').prop('disabled', false).html('<i class="bi bi-search me-1"></i> Lookup Sale');
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    showError(xhr.responseJSON.message);
                } else {
                    showError('Error looking up sale. Please try again. Check console for details.');
                }
            }
        });
    });

    // Add this after your existing event handlers
    $('#process-return-btn').on('click', function() {
        // Trigger form submit which will show the modal
        $('#return-form').submit();
    });

    function displaySaleDetails() {
        // Update sale info display
        $('#sale-id-display').text(currentSale.id);
        $('#sale-date-display').text(new Date(currentSale.sale_date).toLocaleDateString());
        $('#customer-name-display').text(currentSale.customer_name || 'N/A');
        $('#customer-contact-display').text(currentSale.customer_contact || 'N/A');
        
        // Set hidden form field
        $('#form-sale-id').val(currentSale.id);

        // Populate return items table
        const tbody = $('#return-items-tbody');
        tbody.empty();

        // Use sequential array keys starting from 0
        currentSale.items.forEach((item, index) => {
            if (item.max_returnable > 0) {
                const row = `
                    <tr class="return-item-row" data-item-id="${item.id}">
                        <td style="word-break: break-word; min-width: 200px; max-width: 300px;">
                            <div style="overflow-wrap: break-word;">
                                ${item.product_name}
                                <input type="hidden" name="items[${index}][sale_item_id]" value="${item.id}">
                            </div>
                        </td>
                        <td>${item.product_sku}</td>
                        <td class="text-end">₱${parseFloat(item.unit_price).toFixed(2)}</td>
                        <td>${item.quantity_sold}</td>
                        <td>${item.already_returned}</td>
                        <td>
                            <input type="number" 
                                   name="items[${index}][quantity]" 
                                   class="form-control return-quantity" 
                                   min="0" 
                                   max="${item.max_returnable}"
                                   value="0"
                                   data-unit-price="${item.unit_price}"
                                   data-max="${item.max_returnable}"
                                   placeholder="0">
                        </td>
                        <td>
                            <select name="items[${index}][condition]" class="form-select item-condition">
                                <option value="resaleable">Resaleable</option>
                                <option value="damaged">Damaged</option>
                            </select>
                        </td>
                        <td class="text-end">
                            ₱<span class="line-refund-amount">0.00</span>
                            <input type="hidden" name="items[${index}][refund_amount]" class="line-refund-input" value="0">
                        </td>
                    </tr>
                `;
                tbody.append(row);
            }
        });

        // Initialize calculation
        calculateRefundAmounts();
        
        // Show the form section
        $('#sale-details-section').removeClass('d-none');

        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#sale-details-section').offset().top - 20
        }, 500);
    }

    // Calculate refund amounts when quantity or condition changes
    $(document).on('change', '.return-quantity, .item-condition', function() {
        calculateRefundAmounts();
    });

    function calculateRefundAmounts() {
        let totalRefund = 0;

        $('.return-quantity').each(function() {
            const quantity = parseInt($(this).val()) || 0;
            const unitPrice = parseFloat($(this).data('unit-price'));
            const maxQuantity = parseInt($(this).data('max'));
            const condition = $(this).closest('tr').find('.item-condition').val();
            
            // Calculate refund amount only if quantity > 0
            if (quantity > 0) {
                let refundAmount = quantity * unitPrice;
                
                const lineRefundElement = $(this).closest('tr').find('.line-refund-amount');
                const lineRefundInput = $(this).closest('tr').find('.line-refund-input');
                
                lineRefundElement.text(refundAmount.toFixed(2));
                lineRefundInput.val(refundAmount.toFixed(2));
                
                totalRefund += refundAmount;
            } else {
                // Reset to 0 if quantity is 0
                const lineRefundElement = $(this).closest('tr').find('.line-refund-amount');
                const lineRefundInput = $(this).closest('tr').find('.line-refund-input');
                lineRefundElement.text('0.00');
                lineRefundInput.val('0');
            }
        });

        $('#total-refund-amount').text(totalRefund.toFixed(2));
        $('#total-refund-display').val('₱' + totalRefund.toFixed(2));
    }

    function showError(message) {
        $('#error-message').text(message);
        $('#error-alert').removeClass('d-none');
        
        $('html, body').animate({
            scrollTop: $('#error-alert').offset().top - 20
        }, 500);
    }

    // Form validation before submission - shows modal instead of immediate submit
    $('#return-form').on('submit', function(e) {
        e.preventDefault(); // Prevent immediate form submission
        
        // Validation logic
        let hasReturnItems = false;
        let returnItems = [];
        
        // Check if any item has quantity > 0
        $('.return-quantity').each(function(index) {
            const quantity = parseInt($(this).val()) || 0;
            if (quantity > 0) {
                hasReturnItems = true;
                returnItems.push({
                    index: index,
                    quantity: quantity
                });
            }
        });

        if (!hasReturnItems) {
            showError('Please enter a return quantity greater than 0 for at least one item.');
            return false;
        }
        
        // Check that refund method and return reason are selected
        if (!$('#refund_method').val()) {
            showError('Please select a refund method.');
            return false;
        }
        
        if (!$('#return_reason').val()) {
            showError('Please select a return reason.');
            return false;
        }
        
        // Check reference number for GCash/Card
        const refundMethod = $('#refund_method').val();
        if ((refundMethod === 'GCash' || refundMethod === 'Card') && !$('#reference_no').val()) {
            showError('Reference number is required for GCash and Card refunds.');
            return false;
        }
        
        // Validate each item's quantity doesn't exceed max
        let validationPassed = true;
        $('.return-quantity').each(function() {
            const quantity = parseInt($(this).val()) || 0;
            const max = parseInt($(this).data('max'));
            
            if (quantity > max) {
                showError(`Return quantity cannot exceed ${max} for one or more items.`);
                validationPassed = false;
                return false; // break the loop
            }
        });
        
        if (!validationPassed) {
            return false;
        }

        // Populate modal with return details
        const totalRefund = $('#total-refund-amount').text();
        const refundMethodText = $('#refund_method option:selected').text();
        const itemsCount = returnItems.length;
        
        $('#modal-refund-amount').text(totalRefund);
        $('#modal-refund-method').text(refundMethodText);
        $('#modal-items-count').text(itemsCount);
        
        // Show the modal
        new bootstrap.Modal(document.getElementById('confirmationModal')).show();
    });

    // Handle the confirmation button click
    $('#confirmReturn').on('click', function() {
        // Submit the form programmatically
        document.getElementById('return-form').submit();
    });
});
</script>

<style>
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
</style>
@endpush
@extends('layouts.app')
@section('title', 'New Product - SAR EQUIP')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
<style>
    .supplier-item {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #f8f9fa;
    }
    .remove-supplier {
        color: #dc3545;
        cursor: pointer;
    }
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 5px;
    }
</style>
@endpush
@section('content')
    @include('components.alerts')
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <a href="{{ route('products.index') }}" class="text-decoration-none text-dark">
                    <b class="underline">Products</b>
                </a> 
                > New Product
            </h2>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Products
            </a>
        </div>
    </div>

    <!-- Product Form -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
                @csrf
                
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter product name..."
                                   value="{{ old('name') }}" required maxlength="150">
                            <div class="form-text">Max 150 characters</div>
                        </div>
                
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" placeholder="Enter product description..."
                                      rows="4" maxlength="500">{{ old('description') }}</textarea>
                            <div class="form-text">Max 500 characters</div>
                        </div>

                        <div class="mb-3">
                            <label for="model" class="form-label">Model</label>
                            <input type="text" class="form-control" id="model" name="model" 
                                   placeholder="Enter product model..." value="{{ old('model') }}" maxlength="100">
                            <div class="form-text">Max 100 characters</div>
                        </div>
                        
                    </div>
                
                    <!-- Right Column -->
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="bi bi-box me-2"></i>Product Details</h5>
                
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                
                        <div class="mb-3">
                            <label for="sku_suffix" class="form-label">SKU <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" id="sku_prefix_display">-</span>
                                <span class="input-group-text">-</span>
                                <input type="text" class="form-control" id="sku_suffix" name="sku_suffix"
                                       maxlength="5" pattern="[0-9]*" inputmode="numeric"
                                       required placeholder="00123">
                                <button type="button" class="btn btn-outline-secondary" id="suggest_sku">
                                    <i class="bi bi-magic"></i> Suggest
                                </button>
                            </div>
                            <div class="form-text">
                                Format: <strong><span id="sku_format_display">CAT-00123</span></strong>.
                                <span class="text-danger fw-semibold">This SKU is permanent and cannot be changed after creation.</span>
                            </div>
                        </div>
                
                        <div class="mb-3">
                            <label for="manufacturer_barcode" class="form-label">Manufacturer Barcode</label>
                            <input type="text" class="form-control" id="manufacturer_barcode" 
                                   name="manufacturer_barcode" value="{{ old('manufacturer_barcode') }}"
                                   maxlength="20" inputmode="numeric" 
                                   placeholder="Scan or type barcode...">
                        </div>
                
                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" 
                                   accept=".jpg,.jpeg,.png,.gif,.webp">
                            <div class="form-text">JPEG, PNG, JPG, GIF, WEBP — Max 2MB</div>
                            <div id="imagePreview" class="mt-2 position-relative" style="display: inline-block;"></div>
                            <div id="imageError" class="text-danger small mt-1"></div>
                        </div>

                        <hr class="my-3">
                        <h5 class="mb-3"><i class="bi bi-truck me-2"></i>Inventory & Sourcing</h5>
                
                        <div class="mb-3">
                            <label for="reorder_level" class="form-label">Reorder Level <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="reorder_level" 
                                   name="reorder_level" value="{{ old('reorder_level', 10) }}"
                                   min="0" max="99999" required>
                            <div class="form-text">
                                Alert when stock falls below this level.
                            </div>
                        </div>
    
                        <div class="mb-3">
                            <label for="default_supplier_id" class="form-label">Default Supplier <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-select" id="default_supplier_id" name="default_supplier_id" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('default_supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->supplier_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addSupplierModal" title="Quickly create a new supplier">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                            <div class="form-text">This supplier is used for tracking product costs and inventory.</div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit buttons -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Add Product</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupplierModalLabel">
                        <i class="bi bi-building me-2"></i>
                        Quick Add Supplier
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="quickSupplierForm">
                        @csrf
                        <div class="mb-3">
                            <label for="supplier_name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="supplier_name" name="supplier_name" placeholder="Enter supplier name" maxlength="150" required>
                        </div>
                        <div class="mb-3">
                            <label for="contactNO" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contactNO" name="contactNO" 
                                   placeholder="Enter contact number" 
                                   maxlength="11"
                                   pattern="[0-9]{0,11}"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" placeholder="Enter address" maxlength="255" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitQuickSupplier">Add Supplier</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // PASS SUPPLIERS FROM LARAVEL TO JS
    const ALL_SUPPLIERS = @json($suppliers->map(function($s) {
        return ['id' => $s->id, 'supplier_name' => $s->supplier_name];
    }));

    let currentSupplierModalContext = 'primary';

    // Save form data to localStorage
    function saveFormData() {
        const formData = {
            name: document.getElementById('name').value,
            description: document.getElementById('description').value,
            model: document.getElementById('model') ? document.getElementById('model').value : '',
            category_id: document.getElementById('category_id').value,
            manufacturer_barcode: document.getElementById('manufacturer_barcode').value,
            reorder_level: document.getElementById('reorder_level').value,
            default_supplier_id: document.getElementById('default_supplier_id').value,
        };

        localStorage.setItem('productFormData', JSON.stringify(formData));
    }

    // Image validation with strict 2MB limit
    document.getElementById('image').addEventListener('change', function(e) {
        const file = this.files[0];
        const errorDiv = document.getElementById('imageError');
        const preview = document.getElementById('imagePreview');
        
        // Clear previous errors and preview
        errorDiv.textContent = '';
        preview.innerHTML = '';
        
        if (!file) return;
        
        // Strict file type validation
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            errorDiv.textContent = 'Invalid file type. Please upload JPEG, PNG, JPG, GIF, or WEBP only.';
            this.value = ''; // Clear the file input
            return;
        }
        
        // Strict 2MB size validation
        const maxSize = 2 * 1024 * 1024; // 2MB in bytes
        if (file.size > maxSize) {
            errorDiv.textContent = `File size (${(file.size / (1024 * 1024)).toFixed(2)}MB) exceeds 2MB limit. Please choose a smaller file.`;
            this.value = ''; // Clear the file input
            return;
        }
        
        // If validation passes, show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'image-preview';
            
            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'btn btn-danger btn-sm position-absolute';
            clearBtn.style.top = '5px';
            clearBtn.style.right = '5px';
            clearBtn.innerHTML = '<i class="bi bi-x"></i>';
            clearBtn.onclick = clearImage;
            
            preview.appendChild(img);
            preview.appendChild(clearBtn);
        };
        reader.readAsDataURL(file);
    });

    function clearImage() {
        document.getElementById('image').value = '';
        document.getElementById('imagePreview').innerHTML = '';
        document.getElementById('imageError').textContent = '';
        saveFormData();
    }

    // Also prevent form submission if there's a large file
    document.getElementById('productForm').addEventListener('submit', function(e) {
        const imageInput = document.getElementById('image');
        const file = imageInput.files[0];
        const errorDiv = document.getElementById('imageError');
        
        if (file) {
            // Re-validate on submit for safety
            const maxSize = 2 * 1024 * 1024;
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!validTypes.includes(file.type)) {
                e.preventDefault();
                errorDiv.textContent = 'Invalid file type. Please upload a valid image.';
                imageInput.focus();
                return;
            }
            
            if (file.size > maxSize) {
                e.preventDefault();
                errorDiv.textContent = `File too large. Maximum size is 2MB.`;
                imageInput.value = '';
                imageInput.focus();
                return;
            }
        }
    });

    // Load form data
    function loadFormData() {
        const saved = localStorage.getItem('productFormData');
        if (!saved) return;

        const data = JSON.parse(saved);
        document.getElementById('name').value = data.name || '';
        document.getElementById('description').value = data.description || '';
            if (document.getElementById('model')) {
            document.getElementById('model').value = data.model || '';
        }
        document.getElementById('category_id').value = data.category_id || '';
        document.getElementById('manufacturer_barcode').value = data.manufacturer_barcode || '';
        document.getElementById('reorder_level').value = data.reorder_level || '';
        document.getElementById('default_supplier_id').value = data.default_supplier_id || '';

        document.getElementById('image').value = '';
        document.getElementById('imagePreview').innerHTML = '';
        document.getElementById('imageError').textContent = '';
    }

    function clearFormData() {
        localStorage.removeItem('productFormData');
    }

    // Primary supplier quick add context
    document.querySelector('#default_supplier_id').closest('.input-group').querySelector('.btn')
        .addEventListener('click', () => currentSupplierModalContext = 'primary');

    // Quick add supplier
    document.getElementById('submitQuickSupplier').addEventListener('click', function() {
        const formData = new FormData(document.getElementById('quickSupplierForm'));
        fetch('{{ route("suppliers.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(r => r.ok ? r.json() : r.text().then(t => { throw new Error(t); }))
        .then(data => {
            if (data.success) {
                // Update primary dropdown
                const primarySelect = document.getElementById('default_supplier_id');
                if (!Array.from(primarySelect.options).some(o => o.value == data.supplier.id)) {
                    primarySelect.add(new Option(data.supplier.supplier_name, data.supplier.id));
                }

                // Auto-select if this was for the primary supplier
                if (currentSupplierModalContext === 'primary') {
                    primarySelect.value = data.supplier.id;
                }

                bootstrap.Modal.getInstance(document.getElementById('addSupplierModal')).hide();
                document.getElementById('quickSupplierForm').reset();
                currentSupplierModalContext = null;
                saveFormData();
                alert('Supplier added successfully!');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error: ' + err.message);
        });
    });

    // Auto-save on input
    document.querySelectorAll('#productForm input, #productForm select, #productForm textarea')
        .forEach(el => el.addEventListener('input', saveFormData));
    document.querySelectorAll('#productForm input, #productForm select, #productForm textarea')
        .forEach(el => el.addEventListener('change', saveFormData));

    // Clear on submit
    document.getElementById('productForm').addEventListener('submit', clearFormData);

    // PAGE LOAD
    document.addEventListener('DOMContentLoaded', function() {
        // Load saved form data
        loadFormData();

        // Prevent leading zeros
        ['reorder_level'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/^0+(?=\d)/, '');
                });
            }
        });
    });

    // SKU Generation Logic
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category_id');
        const skuPrefixDisplay = document.getElementById('sku_prefix_display');
        const skuSuffixInput = document.getElementById('sku_suffix');
        const skuFormatDisplay = document.getElementById('sku_format_display');
        const suggestBtn = document.getElementById('suggest_sku');

        // Restrict input to numbers only and max 5 digits
        skuSuffixInput.addEventListener('input', function() {
            // Remove any non-digit characters
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Limit to 5 digits
            if (this.value.length > 5) {
                this.value = this.value.slice(0, 5);
            }
            
            // Remove leading zeros (but allow single zero)
            if (this.value.length > 1) {
                this.value = this.value.replace(/^0+/, '');
            }
            
            updateSkuFormat();
            saveSkuData();
        });

        // Prevent paste of non-numeric characters
        skuSuffixInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numbersOnly = pastedText.replace(/[^0-9]/g, '').slice(0, 5);
            this.value = numbersOnly;
            updateSkuFormat();
            saveSkuData();
        });

        // Update SKU prefix when category changes
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            if (categoryId) {
                fetch(`/categories/${categoryId}`)
                    .then(response => response.json())
                    .then(category => {
                        skuPrefixDisplay.textContent = category.sku_prefix;
                        updateSkuFormat();
                        suggestSku();
                    });
            } else {
                skuPrefixDisplay.textContent = '-';
                updateSkuFormat();
            }
        });

        // Update SKU format display
        function updateSkuFormat() {
            const prefix = skuPrefixDisplay.textContent;
            const suffix = skuSuffixInput.value ? skuSuffixInput.value.padStart(5, '0') : '00001';
            skuFormatDisplay.textContent = `${prefix}-${suffix}`;
        }

        // Suggest next available SKU
        function suggestSku() {
            const categoryId = categorySelect.value;
            if (!categoryId) {
                alert('Please select a category first');
                return;
            }

            fetch(`/products/suggest-sku/${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.suggested_suffix) {
                        // Ensure the suggested suffix is max 5 digits
                        let suffix = data.suggested_suffix.toString();
                        if (suffix.length > 5) {
                            suffix = suffix.slice(-5); // Take last 5 digits if too long
                        }
                        skuSuffixInput.value = suffix;
                        updateSkuFormat();
                    }
                })
                .catch(error => {
                    console.error('Error suggesting SKU:', error);
                });
        }

        // Suggest button click
        suggestBtn.addEventListener('click', suggestSku);

        // Load initial state from saved form data
        const saved = localStorage.getItem('productFormData');
        if (saved) {
            const data = JSON.parse(saved);
            if (data.category_id) {
                // Trigger category change to load SKU prefix
                setTimeout(() => {
                    categorySelect.value = data.category_id;
                    categorySelect.dispatchEvent(new Event('change'));
                    
                    // Set saved suffix if exists
                    if (data.sku_suffix) {
                        // Ensure saved suffix is only numbers and max 5 digits
                        let suffix = data.sku_suffix.toString().replace(/[^0-9]/g, '').slice(0, 5);
                        skuSuffixInput.value = suffix;
                        updateSkuFormat();
                    }
                }, 100);
            }
        }

        // Save SKU data to localStorage
        function saveSkuData() {
            const currentData = JSON.parse(localStorage.getItem('productFormData') || '{}');
            currentData.sku_suffix = skuSuffixInput.value;
            localStorage.setItem('productFormData', JSON.stringify(currentData));
        }
    });
</script>
@endpush
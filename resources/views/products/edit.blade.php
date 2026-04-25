@extends('layouts.app')
@section('title', 'Edit Product - SAR EQUIP')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
<style>
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
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <a href="{{ route('products.index') }}" class="text-decoration-none text-dark">
                    <b class="underline">Products</b>
                </a> 
                > Edit Product
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
            <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="productForm">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Left Column: Basic Information -->
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                
                        <!-- Product Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   placeholder="e.g. Lenovo Wireless Mouse M240"
                                   value="{{ old('name', $product->name) }}" required maxlength="150">
                            <div class="form-text">Max 150 characters</div>
                        </div>
                
                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      placeholder="Optional – short details like color, size, or features"
                                      rows="4" maxlength="500">{{ old('description', $product->description) }}</textarea>
                            <div class="form-text">Max 500 characters</div>
                        </div>

                        <div class="mb-3">
                            <label for="model" class="form-label">Model</label>
                            <input type="text" class="form-control" id="model" name="model" 
                                   placeholder="Enter product model..." 
                                   value="{{ old('model', $product->model) }}" maxlength="100">
                            <div class="form-text">Max 100 characters</div>
                        </div>                        
                    </div>
                
                    <!-- Right Column: Product Details + Inventory -->
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="bi bi-box me-2"></i>Product Details & Inventory</h5>
                
                        <!-- Category -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (old('category_id', $product->category_id) == $category->id) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                
                        <!-- SKU (Readonly) -->
                        <div class="mb-3">
                            <label class="form-label">SKU</label>
                            <input type="text" class="form-control" value="{{ $product->sku }}" readonly
                                   style="background-color: #e9ecef;">
                            <div class="form-text text-danger">SKU cannot be changed after product creation.</div>
                        </div>
                
                        <!-- Manufacturer Barcode -->
                        <div class="mb-3">
                            <label for="manufacturer_barcode" class="form-label">Manufacturer Barcode</label>
                            <input type="text" class="form-control" id="manufacturer_barcode" name="manufacturer_barcode" 
                                   value="{{ old('manufacturer_barcode', $product->manufacturer_barcode) }}" 
                                   maxlength="20" inputmode="numeric"
                                   placeholder="Scan or type barcode...">
                        </div>
                        
                        <!-- Product Image -->
                        <div class="mb-0">
                            <label for="image" class="form-label">Product Image:</label>
                            
                            <!-- File Input FIRST -->
                            <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp">
                            <div class="form-text">JPEG, PNG, JPG, GIF, WEBP — Max 2MB</div>
                            
                            <!-- Current Image Display BELOW the file input -->
                            @if($product->image_path)
                                <div id="currentImageContainer" class="mt-2">
                                    <div class="position-relative d-inline-block">
                                        <img src="{{ asset($product->image_path) }}" alt="Current Image" class="image-preview" id="currentImage">
                                        <div class="position-absolute top-0 end-0 p-1">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeCurrentImage()">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-text mt-1 mb-0">Current product image</div>
                                </div>
                            @endif
                            
                            <!-- New Image Preview -->
                            <div id="newImagePreview" class="mt-2 position-relative d-inline-block"></div>
                            <div id="imageError" class="text-danger small mt-1"></div>
                        </div>

                        <input type="hidden" id="delete_image" name="delete_image" value="0">
                
                        <!-- Reorder Level -->
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
                                            <option value="{{ $supplier->id }}" 
                                                {{ (old('default_supplier_id', $product->default_supplier_id) == $supplier->id) ? 'selected' : '' }}>
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

                <!-- Form Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Product</button>
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

    @push('scripts')
    <script>
        // Quick add supplier functionality
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
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(`HTTP ${response.status}: ${text}`); });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update the supplier dropdown
                    const supplierSelect = document.getElementById('default_supplier_id');
                    if (!Array.from(supplierSelect.options).some(opt => opt.value == data.supplier.id)) {
                        supplierSelect.add(new Option(data.supplier.supplier_name, data.supplier.id));
                    }
                    
                    // Auto-select the new supplier
                    supplierSelect.value = data.supplier.id;

                    // Close modal & reset
                    bootstrap.Modal.getInstance(document.getElementById('addSupplierModal')).hide();
                    document.getElementById('quickSupplierForm').reset();
                    alert('Supplier added successfully!');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Error adding supplier: ' + error.message);
            });
        });

        // Function to remove current image
        function removeCurrentImage() {
            document.getElementById('currentImageContainer').style.display = 'none';
            document.getElementById('delete_image').value = '1';
        }

        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const newImagePreview = document.getElementById('newImagePreview');
            const errorDiv = document.getElementById('imageError');
            const currentImageContainer = document.getElementById('currentImageContainer');
            
            // Clear previous errors and new preview
            errorDiv.textContent = '';
            newImagePreview.innerHTML = '';
            document.getElementById('delete_image').value = '0';
            
            if (!this.files || !this.files[0]) return;
            
            const file = this.files[0];
            
            // File type validation
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                errorDiv.textContent = 'Invalid file type. Please upload JPEG, PNG, JPG, GIF, or WEBP only.';
                this.value = '';
                return;
            }
            
            // File size validation (2MB)
            const maxSize = 2 * 1024 * 1024;
            if (file.size > maxSize) {
                errorDiv.textContent = `File size (${(file.size / (1024 * 1024)).toFixed(2)}MB) exceeds 2MB limit.`;
                this.value = '';
                return;
            }
            
            // Show new image preview
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
                clearBtn.onclick = function() {
                    document.getElementById('image').value = '';
                    newImagePreview.innerHTML = '';
                    // Show current image again if user cancels new image
                    if (currentImageContainer && document.getElementById('delete_image').value === '0') {
                        currentImageContainer.style.display = 'block';
                    }
                };
                
                newImagePreview.appendChild(img);
                newImagePreview.appendChild(clearBtn);
                
                // Hide current image if exists (user is replacing it)
                if (currentImageContainer) {
                    currentImageContainer.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        });
    </script>
    @endpush
@endsection
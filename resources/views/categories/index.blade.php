@extends('layouts.app')
@section('title', 'Categories - SAR EQUIP')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
@endpush
@section('content')
    @include('components.alerts')

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <b>Categories Management</b>
            </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-circle me-1"></i>
                Add New Category
            </button>
        </div>
    </div>

    <!-- Search Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <!-- Search & Clear -->
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <form action="{{ route('categories.index') }}" method="GET" class="d-flex flex-grow-1 gap-2 align-items-center">
                            <div class="input-group search-box flex-grow-1">
                                <input type="text" class="form-control" name="search" placeholder="Search categories..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            
                            @if(request('search'))
                                <a href="{{ route('categories.index') }}" class="btn btn-outline-danger flex-shrink-0" title="Clear search">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="table-container">
        <div class="table-responsive">
            <!-- Results Count -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="text-muted">
                    @if(request('search'))
                        Showing {{ $categories->firstItem() }}–{{ $categories->lastItem() }}
                        of {{ $categories->total() }} results for
                        "<strong>{{ request('search') }}</strong>"
                    @else
                        Showing {{ $categories->firstItem() }}–{{ $categories->lastItem() }}
                        of {{ $categories->total() }} categories
                    @endif
                </div>
            </div>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>SKU Prefix</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td class="text-truncate" style="max-width: 150px;">
                            {{ $category->name }}</td>
                        <td>{{ $category->sku_prefix }}</td> 
                        <td class="description-cell" title="{{ $category->description }}">{{ $category->description ?? 'No description' }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info btn-action view-category" data-id="{{ $category->id }}" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning btn-action edit-category" data-id="{{ $category->id }}" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-action delete-category" 
                                data-id="{{ $category->id }}" 
                                data-products-count="{{ $category->products_count ?? 0 }}"
                                data-name="{{ $category->name }}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="bi bi-grid display-4 text-muted"></i>
                            <p class="mt-3 mb-0">No categories found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $categories->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCategoryModalLabel">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add New Category
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="categoryName" name="name" placeholder="Enter category name" maxlength="50" required>
                            <div class="form-text">Maximum 50 characters</div>
                        </div>
                        <div class="mb-3">
                            <label for="categorySkuPrefix" class="form-label">SKU Prefix <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="categorySkuPrefix" name="sku_prefix" placeholder="e.g., CAT, SHOE, ELEC" maxlength="10" required>
                            <div class="form-text">Unique prefix for product SKUs (max 10 characters, will be converted to uppercase)</div>
                            <div class="form-text text-warning">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Important:</strong> SKU prefix cannot be changed after creation. Choose carefully as it will be used for all product SKUs in this category.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="categoryDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="categoryDescription" name="description" rows="3" placeholder="Enter category description" maxlength="255"></textarea>
                            <div class="form-text">Maximum 255 characters</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Category Modal -->
    <div class="modal fade" id="viewCategoryModal" tabindex="-1" aria-labelledby="viewCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewCategoryModalLabel">
                        <i class="bi bi-eye me-2"></i>
                        Category Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <small class="text-muted d-block">ID</small>
                            <span class="fw-semibold" id="viewCategoryId"></span>
                        </div>
                        <div class="list-group-item">
                            <small class="text-muted d-block">Category Name</small>
                            <span class="fw-semibold" id="viewCategoryName"></span>
                        </div>
                        <div class="list-group-item">
                            <small class="text-muted d-block">SKU Prefix</small>
                            <span class="fw-semibold" id="viewCategorySkuPrefix"></span>
                        </div>
                        <div class="list-group-item">
                            <small class="text-muted d-block">Description</small>
                            <span class="fw-semibold" id="viewCategoryDescription" style="word-wrap: break-word; word-break: break-word;"></span>
                        </div>
                        <div class="list-group-item">
                            <small class="text-muted d-block">Created</small>
                            <span class="fw-semibold" id="viewCategoryCreatedAt"></span>
                        </div>
                        <div class="list-group-item">
                            <small class="text-muted d-block">Last Updated</small>
                            <span class="fw-semibold" id="viewCategoryUpdatedAt"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editCategoryForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCategoryModalLabel">
                            <i class="bi bi-pencil me-2"></i>
                            Edit Category
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editCategoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editCategoryName" name="name" maxlength="50" required>
                            <div class="form-text">Maximum 50 characters</div>
                        </div>
                        <div class="mb-3">
                            <label for="editCategorySkuPrefix" class="form-label">SKU Prefix</label>
                            <input type="text" class="form-control" id="editCategorySkuPrefix" name="sku_prefix" maxlength="10" readonly style="background-color: #e9ecef;">
                            <div class="form-text text-warning">
                                <i class="bi bi-exclamation-triangle"></i> SKU prefix cannot be changed after creation to maintain product SKU integrity.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editCategoryDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editCategoryDescription" name="description" rows="3" maxlength="255"></textarea>
                            <div class="form-text">Maximum 255 characters</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteCategoryForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteCategoryModalLabel">
                            <i class="bi bi-exclamation-triangle me-2 text-danger"></i>
                            Confirm Deletion
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Are you sure you want to delete this category?</h5>
                            <p class="text-muted">Category: <strong id="deleteCategoryName"></strong></p>
                            
                            <!-- Warning for categories with products -->
                            <div class="alert alert-danger mt-3" id="productsWarning" style="display: none;">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Cannot Delete!</strong> This category is used in <span id="productsCount" class="fw-bold">0</span> product(s).
                            </div>
                            
                            <!-- Info for empty categories -->
                            <div class="alert alert-warning mt-3" id="deleteWarning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Warning:</strong> This action cannot be undone.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="deleteSubmitBtn">Delete Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('scripts')
    
    <script>
        // Edit Category
        document.querySelectorAll('.edit-category').forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-id');
                
                fetch(`/categories/${categoryId}/edit`)
                    .then(response => response.json())
                    .then(category => {
                        document.getElementById('editCategoryName').value = category.name;
                        document.getElementById('editCategorySkuPrefix').value = category.sku_prefix;
                        document.getElementById('editCategoryDescription').value = category.description || '';
                        document.getElementById('editCategoryForm').action = `/categories/${categoryId}`;
                        
                        const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
                        modal.show();
                    });
            });
        });

        // View Category
        document.querySelectorAll('.view-category').forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-id');
                
                fetch(`/categories/${categoryId}`)
                    .then(response => response.json())
                    .then(category => {
                        document.getElementById('viewCategoryId').textContent = category.id;
                        document.getElementById('viewCategoryName').textContent = category.name;
                        document.getElementById('viewCategorySkuPrefix').textContent = category.sku_prefix;
                        document.getElementById('viewCategoryDescription').textContent = category.description || 'No description';
                        document.getElementById('viewCategoryCreatedAt').textContent = new Date(category.created_at).toLocaleDateString('en-US', { 
                            month: 'long', 
                            day: 'numeric', 
                            year: 'numeric'
                        });

                        document.getElementById('viewCategoryUpdatedAt').textContent = new Date(category.updated_at).toLocaleDateString('en-US', { 
                            month: 'long', 
                            day: 'numeric', 
                            year: 'numeric'
                        });
                        
                        const modal = new bootstrap.Modal(document.getElementById('viewCategoryModal'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching category:', error);
                    });
            });
        });
        
        // Delete Category
        document.querySelectorAll('.delete-category').forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-id');
                const categoryName = this.getAttribute('data-name');
                const productsCount = parseInt(this.getAttribute('data-products-count'));
                
                document.getElementById('deleteCategoryName').textContent = categoryName;
                document.getElementById('deleteCategoryForm').action = `/categories/${categoryId}`;
                
                // Show/hide warning based on product count
                const productsWarning = document.getElementById('productsWarning');
                const deleteWarning = document.getElementById('deleteWarning');
                const deleteSubmitBtn = document.getElementById('deleteSubmitBtn');
                
                if (productsCount > 0) {
                    // Category has products - show cannot delete warning
                    productsWarning.style.display = 'block';
                    deleteWarning.style.display = 'none';
                    document.getElementById('productsCount').textContent = productsCount;
                    
                    // Disable the delete button
                    deleteSubmitBtn.disabled = true;
                    deleteSubmitBtn.textContent = 'Cannot Delete';
                    deleteSubmitBtn.classList.remove('btn-danger');
                    deleteSubmitBtn.classList.add('btn-secondary');
                } else {
                    // Category has no products - show regular delete warning
                    productsWarning.style.display = 'none';
                    deleteWarning.style.display = 'block';
                    
                    // Enable the delete button
                    deleteSubmitBtn.disabled = false;
                    deleteSubmitBtn.textContent = 'Delete Category';
                    deleteSubmitBtn.classList.remove('btn-secondary');
                    deleteSubmitBtn.classList.add('btn-danger');
                }
                
                const modal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
                modal.show();
            });
        });
        </script>
@endpush
@endsection
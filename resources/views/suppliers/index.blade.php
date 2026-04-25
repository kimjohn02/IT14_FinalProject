@extends('layouts.app')
@section('title', 'Suppliers - SAR EQUIP')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
@endpush
@section('content')
    @include('components.alerts')
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <b>Suppliers Management</b>
                @if($showArchived)
                    <span class="text-secondary small ms-2">(Archive View)</span>
                @endif
            </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                <i class="bi bi-plus-circle me-1"></i>
                Add New Supplier
            </button>
        </div>
    </div>

    <!-- Search & Archive Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <!-- Search & Clear -->
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <form action="{{ route('suppliers.index') }}" method="GET" class="d-flex flex-grow-1 gap-2 align-items-center">
                            @if($showArchived)
                                <input type="hidden" name="archived" value="true">
                            @endif
                            <div class="input-group search-box flex-grow-1">
                                <input type="text" class="form-control" name="search" placeholder="Search suppliers..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            
                            @if(request('search'))
                                @if($showArchived)
                                    <a href="{{ route('suppliers.index', ['archived' => true]) }}" class="btn btn-outline-danger flex-shrink-0" title="Clear search">
                                @else
                                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-danger flex-shrink-0" title="Clear search">
                                @endif
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
                
                <!-- Archive Toggle -->
                <div class="col-md-6">
                    <div class="d-flex gap-2 justify-content-end">
                        @if($showArchived)
                            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Back to Active
                            </a>
                        @else
                            <a href="{{ route('suppliers.index', ['archived' => true]) }}" class="btn btn-outline-warning">
                                <i class="bi bi-archive me-1"></i>
                                View Archive
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="table-container">    
        <div class="table-responsive">
            <!-- Results Count -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="text-muted">
                    @if(request('search'))
                        Showing {{ $suppliers->firstItem() }}–{{ $suppliers->lastItem() }}
                        of {{ $suppliers->total() }} results for
                        "<strong>{{ request('search') }}</strong>"
                    @else
                        Showing {{ $suppliers->firstItem() }}–{{ $suppliers->lastItem() }}
                        of {{ $suppliers->total() }} {{ $showArchived ? 'archived' : 'active' }} suppliers
                    @endif
                </div>
            </div>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Supplier Name</th>
                        <th>Contact No.</th>
                        <th>Address</th>
                        <th class="text-end">Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                    <tr>
                        <td>{{ $supplier->id }}</td>
                        <td class="text-truncate" style="max-width: 150px;">
                            {{ $supplier->supplier_name }}</td>
                        <td>{{ $supplier->contactNO ?? 'N/A' }}</td>
                        <td class="text-truncate" style="max-width: 200px;" title="{{ $supplier->address }}">
                            {{ $supplier->address ?? 'N/A' }}
                        </td>
                        <td class="text-end">{{ $supplier->products_count ?? $supplier->products->count() }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info btn-action view-supplier" data-id="{{ $supplier->id }}" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            @if($supplier->is_active)
                                <a href="{{ route('products.index', ['search' => $supplier->supplier_name]) }}" 
                                    class="btn btn-sm btn-outline-primary btn-action"
                                    title="View Products from this supplier">
                                        <i class="bi bi-box"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-warning btn-action edit-supplier" data-id="{{ $supplier->id }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-action archive-supplier" 
                                        data-id="{{ $supplier->id }}" 
                                        data-name="{{ $supplier->supplier_name }}"
                                        data-is-default-supplier="{{ $supplier->isDefaultSupplier() ? 'true' : 'false' }}"
                                        title="Archive">
                                    <i class="bi bi-archive"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-outline-success btn-action restore-supplier" data-id="{{ $supplier->id }}" data-name="{{ $supplier->supplier_name }}" title="Restore">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="bi bi-truck display-4 text-muted"></i>
                            <p class="mt-3 mb-0"> No {{ $showArchived ? 'archived' : 'active' }} suppliers found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $suppliers->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('suppliers.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSupplierModalLabel">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add New Supplier
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editSupplierForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editSupplierModalLabel">
                            <i class="bi bi-pencil me-2"></i>
                            Edit Supplier
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editSupplierName" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editSupplierName" name="supplier_name" maxlength="150" required>
                        </div>
                        <div class="mb-3">
                            <label for="editContactInfo" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="editContactInfo" name="contactNO" 
                                maxlength="11"
                                pattern="[0-9]{0,11}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)">
                        </div>
                        <div class="mb-3">
                            <label for="editAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="editAddress" name="address" maxlength="255" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Supplier Modal -->
    <div class="modal fade" id="viewSupplierModal" tabindex="-1" aria-labelledby="viewSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewSupplierModalLabel">
                        <i class="bi bi-building me-2"></i>
                        Supplier Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <small class="text-muted">Supplier Name:</small>
                            <span class="fw-semibold" id="viewSupplierName"></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <small class="text-muted">Contact No:</small>
                            <span class="fw-semibold" id="viewContactInfo">N/A</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <small class="text-muted">Address:</small>
                            <span class="fw-semibold text-break text-end" style="max-width: 60%; word-wrap: break-word;" id="viewAddress">N/A</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <small class="text-muted">Status:</small>
                            <span class="fw-semibold" id="viewStatusText"></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <small class="text-muted">Created:</small>
                            <span class="fw-semibold" id="viewCreatedAt"></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <small class="text-muted">Updated:</small>
                            <span class="fw-semibold" id="viewUpdatedAt"></span>
                        </div>
                    </div>

                    <!-- Archive Info -->
                    <div class="mt-3 mb-1 p-2 bg-warning bg-opacity-10 rounded" id="archiveInfo" style="display: none;">
                        <small class="text-muted d-block mb-2">Archive Information</small>
                        <div class="row g-2">
                            <div class="col-3">
                                <small>Archived Date:</small>
                            </div>
                            <div class="col-9">
                                <small class="fw-semibold" id="viewDateDisabled"></small>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-3">
                                <small>Archived By:</small>
                            </div>
                            <div class="col-9">
                                <small class="fw-semibold" id="viewDisabledBy"></small>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-3">
                                <small style="white-space: nowrap;">Reason:</small>
                            </div>
                            <div class="col-9">
                                <small class="fw-semibold text-break" 
                                    id="viewArchiveReason" 
                                    style="word-wrap: break-word; white-space: pre-wrap;">
                                    N/A
                                </small>
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

    <!-- In the archive confirmation modal -->
    <div class="modal fade" id="archiveSupplierModal" tabindex="-1" aria-labelledby="archiveSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="archiveSupplierForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="archiveSupplierModalLabel">
                            <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                            Confirm Archive
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-archive text-warning" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Are you sure you want to archive this supplier?</h5>
                            <p class="text-muted">Supplier: <strong id="archiveSupplierName"></strong></p>
                            
                            <!-- Warning about being default supplier -->
                            <div class="alert alert-danger mt-3" id="defaultSupplierWarning" style="display: none;">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Warning:</strong> This supplier cannot be archived because it is set as the default supplier for one or more products.
                                <br><small>Please change the default supplier for those products first.</small>
                            </div>
                            
                            <!-- Info about products -->
                            <div class="alert alert-info mt-3" id="productsInfo" style="display: none;">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Note:</strong> Products currently using this supplier will remain associated with it in historical records.
                            </div>
                                                        
                            <div id="archiveNoteWarning" class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> Archived suppliers will be hidden from active lists but their data is preserved.
                            </div>

                            <div id="archiveReasonField" class="mb-3 text-start">
                                <label for="archiveReason" class="form-label">Archive Reason:</label>
                                <textarea class="form-control" id="archiveReason" name="archive_reason" rows="2" placeholder="Enter reason for archiving" maxlength="255"></textarea>
                            </div>
                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning" id="archiveSubmitBtn">Archive Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div class="modal fade" id="restoreSupplierModal" tabindex="-1" aria-labelledby="restoreSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="restoreSupplierForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="restoreSupplierModalLabel">
                            <i class="bi bi-arrow-clockwise me-2 text-success"></i>
                            Confirm Restore
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-arrow-clockwise text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Are you sure you want to restore this supplier?</h5>
                            <p class="text-muted">Supplier: <strong id="restoreSupplierName"></strong></p>
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                The supplier will be visible in active lists again.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Restore Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Edit Supplier
        document.querySelectorAll('.edit-supplier').forEach(button => {
            button.addEventListener('click', function() {
                const supplierId = this.getAttribute('data-id');
                
                fetch(`/suppliers/${supplierId}/edit`)
                    .then(response => response.json())
                    .then(supplier => {
                        document.getElementById('editSupplierName').value = supplier.supplier_name;
                        document.getElementById('editContactInfo').value = supplier.contactNO || '';
                        document.getElementById('editAddress').value = supplier.address || '';
                        
                        document.getElementById('editSupplierForm').action = `/suppliers/${supplierId}`;
                        
                        const modal = new bootstrap.Modal(document.getElementById('editSupplierModal'));
                        modal.show();
                    });
            });
        });
        
        // View Supplier
        document.querySelectorAll('.view-supplier').forEach(button => {
            button.addEventListener('click', function() {
                const supplierId = this.getAttribute('data-id');
                
                fetch(`/suppliers/${supplierId}`)
                    .then(response => response.json())
                    .then(supplier => {                
                        document.getElementById('viewSupplierName').textContent = supplier.supplier_name;
                        document.getElementById('viewContactInfo').textContent = supplier.contactNO || 'N/A';
                        document.getElementById('viewAddress').textContent = supplier.address || 'N/A';
                        document.getElementById('viewCreatedAt').textContent = new Date(supplier.created_at).toLocaleDateString('en-US', { 
                            month: 'short', 
                            day: 'numeric', 
                            year: 'numeric' 
                        });
                        document.getElementById('viewUpdatedAt').textContent = new Date(supplier.updated_at).toLocaleDateString('en-US', { 
                            month: 'short', 
                            day: 'numeric', 
                            year: 'numeric' 
                        });
                        
                        const statusText = document.getElementById('viewStatusText');
                        if (supplier.is_active) {
                            statusText.textContent = 'Active';
                            statusText.className = 'fw-semibold'; 
                            document.getElementById('archiveInfo').style.display = 'none';
                        } else {
                            statusText.textContent = 'Archived';
                            statusText.className = 'fw-semibold';
                            document.getElementById('archiveInfo').style.display = 'block';
                            
                            // Date disabled
                            if (supplier.date_disabled) {
                                document.getElementById('viewDateDisabled').textContent = 
                                document.getElementById('viewDateDisabled').textContent = 
                                new Date(supplier.date_disabled).toLocaleDateString('en-US', { 
                                    month: 'short', 
                                    day: 'numeric', 
                                    year: 'numeric' 
                                });
                            } else {
                                document.getElementById('viewDateDisabled').textContent = 'N/A';
                            }
                            
                            // Disabled by
                            if (supplier.disabled_by && supplier.disabled_by.full_name) {
                                document.getElementById('viewDisabledBy').textContent = supplier.disabled_by.full_name;
                            } else if (supplier.disabled_by_user_id) {
                                document.getElementById('viewDisabledBy').textContent = 'User #' + supplier.disabled_by_user_id;
                            } else {
                                document.getElementById('viewDisabledBy').textContent = 'System';
                            }

                            document.getElementById('viewArchiveReason').textContent = supplier.archive_reason || 'N/A';
                        }
                        
                        const modal = new bootstrap.Modal(document.getElementById('viewSupplierModal'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching supplier:', error);
                    });
            });
        });
        
        // Archive Supplier
        document.querySelectorAll('.archive-supplier').forEach(button => {
            button.addEventListener('click', function() {
                const supplierId = this.getAttribute('data-id');
                const supplierName = this.getAttribute('data-name');
                const isDefaultSupplier = this.getAttribute('data-is-default-supplier') === 'true';
                
                document.getElementById('archiveSupplierName').textContent = supplierName;
                document.getElementById('archiveSupplierForm').action = `/suppliers/${supplierId}/archive`;
                
                const defaultSupplierWarning = document.getElementById('defaultSupplierWarning');
                const submitBtn = document.getElementById('archiveSubmitBtn');
                const productsInfo = document.getElementById('productsInfo');
                const archiveReasonField = document.getElementById('archiveReasonField'); 
                const noteWarning = document.getElementById('archiveNoteWarning');
                const archiveReasonInput = document.getElementById('archiveReason');

                // Clear previous reason value
                if (archiveReasonInput) {
                    archiveReasonInput.value = '';
                }

                if (isDefaultSupplier) {
                    // Show only warning
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Cannot Archive';
                    defaultSupplierWarning.style.display = 'block';
                    
                    // Hide note & reason
                    productsInfo.style.display = 'none';
                    archiveReasonField.style.display = 'none';
                    noteWarning.style.display = 'none';
                } else {
                    // Normal archive view
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Archive Supplier';
                    defaultSupplierWarning.style.display = 'none';
                    
                    // Show note & reason
                    productsInfo.style.display = 'block';
                    archiveReasonField.style.display = 'block';
                    noteWarning.style.display = 'block';
                }
                
                const modal = new bootstrap.Modal(document.getElementById('archiveSupplierModal'));
                modal.show();
            });
        });

        // Restore Supplier
        document.querySelectorAll('.restore-supplier').forEach(button => {
            button.addEventListener('click', function() {
                const supplierId = this.getAttribute('data-id');
                const supplierName = this.getAttribute('data-name');
                
                document.getElementById('restoreSupplierName').textContent = supplierName;
                document.getElementById('restoreSupplierForm').action = `/suppliers/${supplierId}/restore`;
                
                const modal = new bootstrap.Modal(document.getElementById('restoreSupplierModal'));
                modal.show();
            });
        });
    </script>
    @endpush
@endsection
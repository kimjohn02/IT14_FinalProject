<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Reports System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #9b59b6;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .dashboard-header {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
            margin-bottom: 1.5rem;
        }
        
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-top: 1.5rem;
        }
        
        .table th {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 1rem;
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        .status-in-stock {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-out-of-stock {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-completed {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-pending {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-active {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .nav-tabs .nav-link {
            color: var(--secondary-color);
            font-weight: 500;
            border: none;
            padding: 0.75rem 1.5rem;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
            background-color: transparent;
        }
        
        .filter-section {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .form-control, .form-select {
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .total-value {
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .product-code {
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .alert-warning {
            background-color: rgba(243, 156, 18, 0.1);
            border: 1px solid rgba(243, 156, 18, 0.2);
            color: var(--warning-color);
            border-radius: 8px;
        }
        
        .module-content {
            display: none;
        }
        
        .module-content.active {
            display: block;
        }
        
        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Inventory Reports</h1>
                <div class="d-flex align-items-center">
                    <span class="me-3 text-muted">Welcome, Admin</span>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> Account
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="h5 mb-3">Select a report type to view and export data</h2>
                <ul class="nav nav-tabs" id="reportTabs">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeModule === 'inventory' ? 'active' : '' }}" href="#" data-module="inventory">Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeModule === 'purchase' ? 'active' : '' }}" href="#" data-module="purchase">Purchase</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeModule === 'sales' ? 'active' : '' }}" href="#" data-module="sales">Sales</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeModule === 'supplier' ? 'active' : '' }}" href="#" data-module="supplier">Supplier</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeModule === 'user' ? 'active' : '' }}" href="#" data-module="user">User</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Inventory Module -->
        <div id="inventory-module" class="module-content {{ $activeModule === 'inventory' ? 'active' : '' }}">
            <form action="{{ route('reports.index') }}" method="GET">
                <input type="hidden" name="module" value="inventory">
                <div class="filter-section">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="dateFrom" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="dateFrom" name="dateFrom" value="{{ request('dateFrom', '2024-01-01') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="dateTo" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="dateTo" name="dateTo" value="{{ request('dateTo', '2024-12-31') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="All Categories" {{ request('category', 'All Categories') === 'All Categories' ? 'selected' : '' }}>All Categories</option>
                                <option value="Electronics" {{ request('category') === 'Electronics' ? 'selected' : '' }}>Electronics</option>
                                <option value="Furniture" {{ request('category') === 'Furniture' ? 'selected' : '' }}>Furniture</option>
                                <option value="Office Supplies" {{ request('category') === 'Office Supplies' ? 'selected' : '' }}>Office Supplies</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 mb-1">
                                <i class="fas fa-filter me-2"></i> Apply Filters
                            </button>
                            <a href="{{ route('reports.export.pdf', ['module' => 'inventory']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-file-pdf me-2"></i> Export to PDF
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            @if($lowStockItems > 0)
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    <strong>Attention needed:</strong> {{ $lowStockItems }} items are low in stock and need to be reordered.
                </div>
            </div>
            @endif

            <div class="table-container">
                <div class="p-3 border-bottom">
                    <h3 class="h5 mb-0">Inventory Details</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>PRODUCT CODE</th>
                                <th>PRODUCT NAME</th>
                                <th>CATEGORY</th>
                                <th>QUANTITY</th>
                                <th>UNIT PRICE</th>
                                <th>TOTAL VALUE</th>
                                <th>STATUS</th>
                                <th>LAST UPDATED</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventory as $item)
                            <tr>
                                <td><span class="product-code">{{ $item->product_code }}</span></td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->category }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>${{ number_format($item->unit_price, 2) }}</td>
                                <td class="total-value">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                <td>
                                    @if($item->quantity > 0)
                                        <span class="status-in-stock">In Stock</span>
                                    @else
                                        <span class="status-out-of-stock">Out of Stock</span>
                                    @endif
                                </td>
                                <td>{{ $item->last_updated }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Purchase Module -->
        <div id="purchase-module" class="module-content {{ $activeModule === 'purchase' ? 'active' : '' }}">
            <form action="{{ route('reports.index') }}" method="GET">
                <input type="hidden" name="module" value="purchase">
                <div class="filter-section">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="purchaseDateFrom" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="purchaseDateFrom" name="dateFrom" value="{{ request('dateFrom', '2024-01-01') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="purchaseDateTo" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="purchaseDateTo" name="dateTo" value="{{ request('dateTo', '2024-12-31') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="purchaseStatus" class="form-label">Status</label>
                            <select class="form-select" id="purchaseStatus" name="status">
                                <option value="All Status" {{ request('status', 'All Status') === 'All Status' ? 'selected' : '' }}>All Status</option>
                                <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                                <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 mb-1">
                                <i class="fas fa-filter me-2"></i> Apply Filters
                            </button>
                            <a href="{{ route('reports.export.pdf', ['module' => 'purchase']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-file-pdf me-2"></i> Export to PDF
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-container">
                <div class="p-3 border-bottom">
                    <h3 class="h5 mb-0">Purchase Orders</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ORDER ID</th>
                                <th>SUPPLIER</th>
                                <th>PRODUCT</th>
                                <th>QUANTITY</th>
                                <th>UNIT COST</th>
                                <th>TOTAL COST</th>
                                <th>ORDER DATE</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchases ?? [] as $purchase)
                            <tr>
                                <td><span class="product-code">{{ $purchase->order_id }}</span></td>
                                <td>{{ $purchase->supplier }}</td>
                                <td>{{ $purchase->product }}</td>
                                <td>{{ $purchase->quantity }}</td>
                                <td>${{ number_format($purchase->unit_cost, 2) }}</td>
                                <td class="total-value">${{ number_format($purchase->total_cost, 2) }}</td>
                                <td>{{ $purchase->order_date }}</td>
                                <td>
                                    @if($purchase->status === 'Completed')
                                        <span class="status-completed">{{ $purchase->status }}</span>
                                    @elseif($purchase->status === 'Pending')
                                        <span class="status-pending">{{ $purchase->status }}</span>
                                    @else
                                        <span class="status-out-of-stock">{{ $purchase->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sales Module -->
        <div id="sales-module" class="module-content {{ $activeModule === 'sales' ? 'active' : '' }}">
            <form action="{{ route('reports.index') }}" method="GET">
                <input type="hidden" name="module" value="sales">
                <div class="filter-section">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="salesDateFrom" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="salesDateFrom" name="dateFrom" value="{{ request('dateFrom', '2024-01-01') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="salesDateTo" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="salesDateTo" name="dateTo" value="{{ request('dateTo', '2024-12-31') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="salesCategory" class="form-label">Product Category</label>
                            <select class="form-select" id="salesCategory" name="category">
                                <option value="All Categories" {{ request('category', 'All Categories') === 'All Categories' ? 'selected' : '' }}>All Categories</option>
                                <option value="Electronics" {{ request('category') === 'Electronics' ? 'selected' : '' }}>Electronics</option>
                                <option value="Furniture" {{ request('category') === 'Furniture' ? 'selected' : '' }}>Furniture</option>
                                <option value="Office Supplies" {{ request('category') === 'Office Supplies' ? 'selected' : '' }}>Office Supplies</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 mb-1">
                                <i class="fas fa-filter me-2"></i> Apply Filters
                            </button>
                            <a href="{{ route('reports.export.pdf', ['module' => 'sales']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-file-pdf me-2"></i> Export to PDF
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-container">
                <div class="p-3 border-bottom">
                    <h3 class="h5 mb-0">Sales Transactions</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ORDER ID</th>
                                <th>CUSTOMER</th>
                                <th>PRODUCT</th>
                                <th>QUANTITY</th>
                                <th>UNIT PRICE</th>
                                <th>TOTAL AMOUNT</th>
                                <th>ORDER DATE</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales ?? [] as $sale)
                            <tr>
                                <td><span class="product-code">{{ $sale->order_id }}</span></td>
                                <td>{{ $sale->customer }}</td>
                                <td>{{ $sale->product }}</td>
                                <td>{{ $sale->quantity }}</td>
                                <td>${{ number_format($sale->unit_price, 2) }}</td>
                                <td class="total-value">${{ number_format($sale->total_amount, 2) }}</td>
                                <td>{{ $sale->order_date }}</td>
                                <td>
                                    @if($sale->status === 'Completed')
                                        <span class="status-completed">{{ $sale->status }}</span>
                                    @else
                                        <span class="status-pending">{{ $sale->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Supplier Module -->
        <div id="supplier-module" class="module-content {{ $activeModule === 'supplier' ? 'active' : '' }}">
            <form action="{{ route('reports.index') }}" method="GET">
                <input type="hidden" name="module" value="supplier">
                <div class="filter-section">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="supplierStatus" class="form-label">Status</label>
                            <select class="form-select" id="supplierStatus" name="status">
                                <option value="All Status" {{ request('status', 'All Status') === 'All Status' ? 'selected' : '' }}>All Status</option>
                                <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="supplierCategory" class="form-label">Category</label>
                            <select class="form-select" id="supplierCategory" name="category">
                                <option value="All Categories" {{ request('category', 'All Categories') === 'All Categories' ? 'selected' : '' }}>All Categories</option>
                                <option value="Electronics" {{ request('category') === 'Electronics' ? 'selected' : '' }}>Electronics</option>
                                <option value="Furniture" {{ request('category') === 'Furniture' ? 'selected' : '' }}>Furniture</option>
                                <option value="Office Supplies" {{ request('category') === 'Office Supplies' ? 'selected' : '' }}>Office Supplies</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary w-100 mb-1">
                                <i class="fas fa-filter me-2"></i> Apply Filters
                            </button>
                            <a href="{{ route('reports.export.pdf', ['module' => 'supplier']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-file-pdf me-2"></i> Export to PDF
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-container">
                <div class="p-3 border-bottom">
                    <h3 class="h5 mb-0">Supplier Information</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>SUPPLIER ID</th>
                                <th>SUPPLIER NAME</th>
                                <th>CONTACT PERSON</th>
                                <th>EMAIL</th>
                                <th>PHONE</th>
                                <th>PRODUCT CATEGORY</th>
                                <th>TOTAL ORDERS</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suppliers ?? [] as $supplier)
                            <tr>
                                <td><span class="product-code">{{ $supplier->supplier_id }}</span></td>
                                <td>{{ $supplier->supplier_name }}</td>
                                <td>{{ $supplier->contact_person }}</td>
                                <td>{{ $supplier->email }}</td>
                                <td>{{ $supplier->phone }}</td>
                                <td>{{ $supplier->product_category }}</td>
                                <td>{{ $supplier->total_orders }}</td>
                                <td>
                                    @if($supplier->status === 'Active')
                                        <span class="status-active">{{ $supplier->status }}</span>
                                    @else
                                        <span class="status-out-of-stock">{{ $supplier->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- User Module -->
        <div id="user-module" class="module-content {{ $activeModule === 'user' ? 'active' : '' }}">
            <form action="{{ route('reports.index') }}" method="GET">
                <input type="hidden" name="module" value="user">
                <div class="filter-section">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="userRole" class="form-label">Role</label>
                            <select class="form-select" id="userRole" name="role">
                                <option value="All Roles" {{ request('role', 'All Roles') === 'All Roles' ? 'selected' : '' }}>All Roles</option>
                                <option value="Admin" {{ request('role') === 'Admin' ? 'selected' : '' }}>Admin</option>
                                <option value="Manager" {{ request('role') === 'Manager' ? 'selected' : '' }}>Manager</option>
                                <option value="Staff" {{ request('role') === 'Staff' ? 'selected' : '' }}>Staff</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="userStatus" class="form-label">Status</label>
                            <select class="form-select" id="userStatus" name="status">
                                <option value="All Status" {{ request('status', 'All Status') === 'All Status' ? 'selected' : '' }}>All Status</option>
                                <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary w-100 mb-1">
                                <i class="fas fa-filter me-2"></i> Apply Filters
                            </button>
                            <a href="{{ route('reports.export.pdf', ['module' => 'user']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-file-pdf me-2"></i> Export to PDF
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-container">
                <div class="p-3 border-bottom">
                    <h3 class="h5 mb-0">User Management</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>USER ID</th>
                                <th>FULL NAME</th>
                                <th>EMAIL</th>
                                <th>ROLE</th>
                                <th>DEPARTMENT</th>
                                <th>LAST LOGIN</th>
                                <th>JOIN DATE</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users ?? [] as $user)
                            <tr>
                                <td><span class="product-code">{{ $user->user_id }}</span></td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role }}</td>
                                <td>{{ $user->department }}</td>
                                <td>{{ $user->last_login }}</td>
                                <td>{{ $user->join_date }}</td>
                                <td>
                                    @if($user->status === 'Active')
                                        <span class="status-active">{{ $user->status }}</span>
                                    @else
                                        <span class="status-out-of-stock">{{ $user->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tab switching functionality
        document.querySelectorAll('#reportTabs .nav-link').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                const module = this.getAttribute('data-module');
                const url = new URL(window.location.href);
                url.searchParams.set('module', module);
                window.location.href = url.toString();
            });
        });

        // Initialize date inputs with current date values
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const firstDayOfYear = new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0];
            
            // Set default date values if not already set
            const dateFrom = document.getElementById('dateFrom');
            const dateTo = document.getElementById('dateTo');
            
            if (dateFrom && !dateFrom.value) {
                dateFrom.value = firstDayOfYear;
            }
            if (dateTo && !dateTo.value) {
                dateTo.value = today;
            }
        });
    </script>
</body>
</html>



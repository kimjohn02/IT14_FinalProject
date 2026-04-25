<!-- Sidebar Only -->
<nav class="sidebar {{ session('user_role') == 'Cashier' ? 'collapsed' : '' }}">
    <div class="sidebar-content">
        <div class="logo-container">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('images/sar_equip_logo.png') }}" alt="Company Logo" class="img-fluid me-3 sidebar-logo" style="max-height: 50px;">
                    <div class="logo-text">
                        <h5 class="mb-0 fw-bold" style="color: var(--congress-blue);">SAR EQUIP</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <ul class="nav flex-column">
            <!-- Dashboard - Admin Only -->
            @if(session('user_role') == 'Administrator')
            <li class="nav-item">
                <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" title="Dashboard">
                    <i class="bi bi-speedometer2 me-3"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            @endif

            <!-- POS - Both roles -->
            <li class="nav-item">
                <a href="{{ route('pos.index') }}" class="nav-link {{ request()->is('pos') ? 'active' : '' }}" title="POS">
                    <i class="bi bi-cash-stack me-3"></i>
                    <span>POS</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('pos.my-transactions') }}" class="nav-link {{ request()->is('pos/my-transactions') || request()->routeIs('pos.my-transactions') ? 'active' : '' }}" title="Today's Transactions">
                    <i class="bi bi-clock-history me-2"></i>
                    <span>Today’s Sales</span>
                </a>
            </li>

            <!-- Admin-only sections -->
            @if(session('user_role') == 'Administrator')
            <!-- Products Menu -->
            <li class="nav-item">
                <a href="#collapseInventory" class="nav-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseInventory" title="Product Management">
                    <i class="bi bi-boxes me-3"></i>
                    <span class="pe-2">Product Management</span>
                    <i class="bi bi-chevron-down ms-auto chevron"></i> 
                </a>
                <div class="collapse {{ request()->is('products*') || request()->is('product-prices*') || request()->is('categories*') || request()->is('suppliers*') ? 'show' : '' }}" id="collapseInventory">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item">
                            <a href="{{ route('products.index') }}" class="nav-link {{ request()->is('products*') ? 'active' : '' }}" title="Products">
                                <i class="bi bi-box-seam me-3"></i>
                                <span>Products</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('product-prices.index') }}" class="nav-link {{ request()->is('product-prices*') ? 'active' : '' }}" title="Product Prices">
                                <i class="bi bi-cash-stack me-3"></i>
                                <span>Product Prices</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->is('categories*') ? 'active' : '' }}" title="Categories">
                                <i class="bi bi-funnel me-3"></i>
                                <span>Categories</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->is('suppliers*') ? 'active' : '' }}" title="Suppliers">
                                <i class="bi bi-truck me-3"></i>
                                <span>Suppliers</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Inventory Management -->
            <li class="nav-item">
                <a href="#collapseInventoryOps" class="nav-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseInventoryOps" title="Inventory Management">
                    <i class="bi bi-box-seam me-3"></i>
                    <span class="pe-2">Inventory Management</span>
                    <i class="bi bi-chevron-down ms-auto chevron"></i>
                </a>
                <div class="collapse {{ request()->is('stock-ins*') || request()->is('stock-adjustments*') || request()->is('returns*') ? 'show' : '' }}" id="collapseInventoryOps">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item">
                            <a href="{{ route('stock-ins.index') }}" class="nav-link {{ request()->is('stock-ins*') ? 'active' : '' }}" title="Stock In">
                                <i class="bi bi-box-arrow-in-down me-3"></i>
                                <span>Stock In</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('stock-adjustments.index') }}" class="nav-link {{ request()->is('stock-adjustments*') ? 'active' : '' }}" title="Stock Adjustments">
                                <i class="bi bi-sliders me-3"></i>
                                <span>Stock Adjustments</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('returns.index') }}" class="nav-link {{ request()->is('returns*') ? 'active' : '' }}" title="Returns">
                                <i class="bi bi-arrow-counterclockwise me-3"></i>
                                <span>Returns</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Reports -->
            <li class="nav-item">
                <a href="#collapseReports" class="nav-link {{ request()->is('reports*', 'sales*') ? '' : 'collapsed' }}" 
                    data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('reports*', 'sales*') ? 'true' : 'false' }}" aria-controls="collapseReports" title="Reports">
                    <i class="bi bi-file-text me-3"></i> <span class="pe-2">Reports</span> <i class="bi bi-chevron-down ms-auto chevron"></i> 
                </a>
                <div class="collapse {{ request()->is('reports*', 'sales*') ? 'show' : '' }}" id="collapseReports">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item">
                            <a href="{{ route('reports.sales.index') }}" class="nav-link {{ request()->is('reports/sales*') ? 'active' : '' }}" title="Sales Reports">
                                <i class="bi bi-graph-up me-3"></i>
                                <span>Sales Reports</span>
                            </a>                            
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.inventory.index') }}" class="nav-link {{ request()->is('reports/inventory*') ? 'active' : '' }}" title="Inventory Reports">
                                <i class="bi bi-box-seam me-3"></i>
                                <span>Inventory Reports</span>
                            </a>                            
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('sales.index') }}" class="nav-link {{ request()->is('sales*') ? 'active' : '' }}" title="Transaction History">
                                <i class="bi bi-receipt me-3"></i>
                                <span>Transaction History</span>
                            </a>                            
                        </li>
                    </ul>
                </div>
            </li>

             <!-- User Management -->
             <li class="nav-item">
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->is('users*') ? 'active' : '' }}" title="Users">
                    <i class="bi bi-people me-3"></i>
                    <span>Users</span>
                </a>
            </li>
            @endif            
        </ul>
    </div> <!-- End sidebar-content -->

    <div class="p-3 border-top border-secondary sidebar-footer">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle sidebar-user-link" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar me-3">
                    {{ strtoupper(substr(session('user_name'), 0, 1)) }}
                </div>
                <div class="flex-grow-1 sidebar-user-info">
                    <div class="fw-bold small" style="color: var(--congress-blue);">{{ session('user_name') }}</div>
                    <small class="text-muted">{{ session('user_role') }}</small>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu shadow">
                <li>
                    <a class="dropdown-item" href="{{ route('account.settings') }}">
                        <i class="bi bi-gear me-2"></i>Settings
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="/logout" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

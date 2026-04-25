<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SAR EQUIP')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">

    <!-- Select 2 CSS -->
    <link href="{{ asset('css/vendor/select2.min.css') }}" rel="stylesheet">
    
    <style>
        
        /* Company Color Variables */
        :root {
            --congress-blue: #06448a;
            --sar-red: #d32f2f;
            --white: #f8f9fa;
            --monza: #e20615;
        }
        
        .sidebar {
            background: #f8f9fa;
            color: #333;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 281px;
            padding-top: 20px;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-right: 1px solid #e9ecef;
        }
        
        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 100px;
        }
        
        .sidebar-content::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-content::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 3px;
        }
        
        .sidebar-content::-webkit-scrollbar-thumb {
            background: #dee2e6;
            border-radius: 3px;
        }
        
        .sidebar-content::-webkit-scrollbar-thumb:hover {
            background: #adb5bd;
        }
        
        .sidebar .nav-link {
            color: #495057;
            padding: 12px 25px;
            margin: 4px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            border: none;
        }
        
        .sidebar .nav-link:hover {
            background: #f8f9fa;
            color: var(--congress-blue);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--sar-red) 0%, #ff5252 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(211, 47, 47, 0.3);
        }
        
        .sidebar .nav-link.collapsed {
            background: transparent;
        }
        .sidebar .nav-link .chevron {
            display: inline-block;               
            transform-origin: center center;
            transition: transform 0.28s cubic-bezier(.4,0,.2,1);
            pointer-events: none;                
        }

            .sidebar .nav-link[aria-expanded="true"] .chevron,
            .sidebar .nav-link.expanded .chevron {   
            transform: rotate(180deg);
        }
                    
        .sub-link {
            padding: 8px 15px 8px 35px !important;
            font-size: 0.9rem;
            margin: 2px 15px !important;
        }
        
        .sub-icon {
            font-size: 0.5rem;
        }
        
        .main-iframe {
            margin-left: 280px;
            width: calc(100vw - 280px);
            height: 100vh;
            border: none;
        }

        .main-content {
            margin-left: 280px;
            width: calc(100vw - 280px);
            height: 100vh;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--sar-red);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: bold;
            font-size: 16px;
            border: 3px solid #e9ecef;
        }
        
        .logo-container {
            padding: 0 25px 25px 25px;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 20px;
        }
        
        .sidebar-toggle-btn {
            color: var(--congress-blue);
            font-size: 1.2rem;
            transition: transform 0.3s ease;
            background: none;
            border: none;
        }
        
        .sidebar-toggle-btn:hover {
            color: var(--congress-blue);
            transform: scale(1.1);
        }
        
        .sidebar-toggle-btn i {
            transition: transform 0.3s ease;
        }
        
        /* Collapsed Sidebar Styles */
        .sidebar {
            transition: width 0.3s ease;
        }
        
        .sidebar.collapsed {
            width: 80px;
        }
        
        .sidebar.collapsed .logo-text {
            display: none;
        }
        
        .sidebar.collapsed .sidebar-logo {
            margin-right: 0 !important;
        }
        
        .sidebar.collapsed .sidebar-toggle-btn i {
            transform: rotate(180deg);
        }
        
        .sidebar.collapsed .nav-link {
            padding: 15px;
            margin: 8px 10px;
            text-align: center;
            justify-content: center;
        }
        
        .sidebar.collapsed .nav-link span {
            display: none;
        }
        
        .sidebar.collapsed .nav-link i {
            margin-right: 0 !important;
            font-size: 1.2rem;
        }
        
        .sidebar.collapsed .nav-link .chevron {
            display: none;
        }
        
        .sidebar.collapsed .collapse {
            display: none !important;
        }
        
        .sidebar.collapsed .collapse.show {
            display: none !important;
        }
        
        .sidebar.collapsed .sidebar-user-info {
            display: none;
        }
        
        .sidebar.collapsed .sidebar-user-link {
            justify-content: center;
            width: 100%;
        }
        
        .sidebar.collapsed .user-avatar {
            margin-right: 0 !important;
            margin-left: 0 !important;
            width: 40px !important;
            height: 40px !important;
            min-width: 40px !important;
            min-height: 40px !important;
            flex-shrink: 0;
        }
        
        .sidebar.collapsed .sidebar-footer {
            padding: 15px 10px !important;
        }
        
        .sidebar.collapsed .dropdown {
            position: static;
        }
        
        /* Only apply fixed positioning when sidebar is collapsed */
        .sidebar.collapsed .dropdown-menu.show {
            position: fixed !important;
            left: 80px !important;
            transform: none !important;
            margin-top: 0 !important;
            z-index: 1051 !important;
            min-width: 200px;
        }
        
        /* Reset dropdown positioning for expanded sidebar (admin view) */
        .sidebar:not(.collapsed) .dropdown-menu {
            position: absolute !important;
            left: auto !important;
            transform: translateX(0) !important;
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: 80px;
            width: calc(100vw - 80px);
            transition: margin-left 0.3s ease, width 0.3s ease;
        }
        
        .notification-badge {
            background: var(--monza);
            color: var(--white);
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                text-align: center;
            }
            
            .sidebar .nav-link span {
                display: none;
            }
            
            .sidebar .logo-text {
                display: none;
            }
            
            .sidebar .nav-link i {
                margin-right: 0 !important;
                font-size: 1.2rem;
            }
            
            .sidebar .nav-link {
                padding: 15px;
                margin: 8px 10px;
                text-align: center;
            }

            .sidebar .sidebar-user-info {
                display: none !important; /* Hide the name */
            }
            
            .sidebar .user-avatar {
                margin-right: 0 !important;
                margin-left: 0 !important;
                width: 40px !important;
                height: 40px !important;
                min-width: 40px !important;
                min-height: 40px !important;
                flex-shrink: 0;
                /* Ensure avatar is visible */
                display: flex !important;
            }

            .sidebar .nav .nav {
                padding-left: 0 !important;
            }
            
            .sidebar .nav .nav .nav-item .nav-link {
                padding-left: 15px !important;
            }
            
            .sidebar .dropdown-menu.show {
                position: fixed !important;
                left: 80px !important;
                transform: none !important;
                margin-top: 0 !important;
                z-index: 1051 !important;
                min-width: 200px;
            }

            .main-content {
                margin-left: 80px;
                width: calc(100vw - 80px);
            }

            .main-iframe {
                margin-left: 80px;
                width: calc(100vw - 80px);
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>

   <!-- Default Password Reminder Modal -->
    <div class="modal fade" id="defaultPasswordModal" tabindex="-1" aria-labelledby="defaultPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-2 shadow-sm">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="defaultPasswordModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Security Reminder
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    <p class="fs-6">
                        You are currently using your <strong>default password</strong>.
                    </p>
                    <p>
                        For your account security, we strongly recommend updating it as soon as possible.
                    </p>
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-outline-secondary" id="dismissDefaultPasswordModal">Later</button>
                    <a href="{{ route('account.settings') }}" class="btn btn-warning fw-semibold" id="changeNowBtn">Change Password</a>
                </div>
            </div>
        </div>
    </div>

    @include('components.sidebar')
    
    <!-- Everything else is one big iframe -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

    <!-- Select 2 JS -->
    <script src="{{ asset('js/vendor/jquery.min.js') }}"></script>
    <script src="{{ asset('js/vendor/select2.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('defaultPasswordModal');
    
            // Check the session flag that you set in login controller
            const shouldShowModal = {{ session('show_default_password_modal') ? 'true' : 'false' }};
    
            console.log('Session flag - show modal:', shouldShowModal);
            console.log('User password changed:', {{ auth()->check() ? (auth()->user()->password_changed ? 'true' : 'false') : 'false' }});
    
            if (modalEl && shouldShowModal) {
                console.log('Showing default password modal from session flag');
                const defaultModal = new bootstrap.Modal(modalEl);
                defaultModal.show();
    
                // Clear the session flag so it doesn't show again on page refresh
                fetch('{{ route("clear-password-modal-flag") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).catch(err => console.log('Flag clear request failed:', err));
    
                // Later button hides modal
                document.getElementById('dismissDefaultPasswordModal').addEventListener('click', function() {
                    defaultModal.hide();
                });
    
                // Change Now button navigates to settings
                document.getElementById('changeNowBtn').addEventListener('click', function() {
                    // The href will handle navigation
                });
            }
    
            // Sidebar functionality
            const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
            
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // If it's a collapse toggle (has data-bs-toggle="collapse"), don't prevent default
                    if (this.getAttribute('data-bs-toggle') === 'collapse') {
                        // Let Bootstrap handle the collapse
                        return;
                    }
                    
                    // If it's a regular navigation link (has href and not #), let it navigate normally
                    const href = this.getAttribute('href');
                    if (href && href !== '#') {
                        // Let the browser navigate to the page normally
                        return;
                    }
                    
                    // Only prevent default for links that don't navigate anywhere
                    e.preventDefault();
                    
                    // Update active state for non-navigation links
                    sidebarLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                });
            });
    
            // Auto-expand sidebar sections based on current page
            const currentPath = window.location.pathname;
            if (currentPath.includes('/roles') || currentPath.includes('/users')) {
                const userCollapse = document.getElementById('collapseUser');
                if (userCollapse) {
                    userCollapse.classList.add('show');
                    const trigger = document.querySelector('[aria-controls="collapseUser"]');
                    if (trigger) {
                        trigger.classList.remove('collapsed');
                    }
                }
            }
            
            // Auto-expand inventory section if on related pages
            if (currentPath.includes('/products') || currentPath.includes('/categories') || currentPath.includes('/suppliers')) {
                const inventoryCollapse = document.getElementById('collapseInventory');
                if (inventoryCollapse) {
                    inventoryCollapse.classList.add('show');
                    const trigger = document.querySelector('[aria-controls="collapseInventory"]');
                    if (trigger) {
                        trigger.classList.remove('collapsed');
                    }
                }
            }
            
            // Sidebar state management - no toggle, role-based only
            const sidebar = document.querySelector('.sidebar');
            const isCashier = {{ session('user_role') == 'Cashier' ? 'true' : 'false' }};
            
            if (sidebar) {
                // Force correct state based on role (no user preference)
                if (isCashier) {
                    // Cashier: Always collapsed
                    sidebar.classList.add('collapsed');
                } else {
                    // Admin: Always expanded
                    sidebar.classList.remove('collapsed');
                }
            }
            
            // Fix dropdown menu positioning when sidebar is collapsed
            if (isCashier) {
                const dropdownToggle = document.querySelector('.sidebar.collapsed .sidebar-user-link');
                const dropdownMenu = document.querySelector('.sidebar.collapsed .dropdown-menu');
                
                if (dropdownToggle && dropdownMenu) {
                    // Use Bootstrap's dropdown events
                    dropdownToggle.addEventListener('show.bs.dropdown', function() {
                        const rect = this.getBoundingClientRect();
                        dropdownMenu.style.position = 'fixed';
                        dropdownMenu.style.left = '80px';
                        dropdownMenu.style.top = (rect.top - dropdownMenu.offsetHeight - 10) + 'px';
                        dropdownMenu.style.bottom = 'auto';
                        dropdownMenu.style.transform = 'none';
                        dropdownMenu.style.zIndex = '1051';
                        
                        // Ensure it doesn't go off screen
                        const menuHeight = dropdownMenu.offsetHeight;
                        const windowHeight = window.innerHeight;
                        if (parseInt(dropdownMenu.style.top) < 10) {
                            dropdownMenu.style.top = '10px';
                        }
                        if (rect.top - menuHeight < 10) {
                            dropdownMenu.style.top = (rect.bottom + 10) + 'px';
                        }
                    });
                }
            }
            
            // Initialize Bootstrap tooltips for sidebar links when collapsed
            function initializeTooltips() {
                const sidebar = document.querySelector('.sidebar');
                const tooltipTriggerList = document.querySelectorAll('.sidebar .nav-link[title]');
                
                // Destroy existing tooltips first
                tooltipTriggerList.forEach(el => {
                    const existingTooltip = bootstrap.Tooltip.getInstance(el);
                    if (existingTooltip) {
                        existingTooltip.dispose();
                    }
                });
                
                // Create new tooltips only if sidebar is collapsed
                if (sidebar && sidebar.classList.contains('collapsed')) {
                    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                        new bootstrap.Tooltip(tooltipTriggerEl, {
                            placement: 'right',
                            trigger: 'hover'
                        });
                    });
                }
            }
            
            // Initialize tooltips if sidebar is collapsed
            initializeTooltips();
        });
    </script>
    @stack('scripts')
</body>
</html>
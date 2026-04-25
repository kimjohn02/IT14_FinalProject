@extends('layouts.app')
@section('title', 'Users - SAR EQUIP')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
<style>
    select[readonly] {
        pointer-events: none;
        touch-action: none;
        background-color: #f8f9fa !important;
        opacity: 1;
        }
</style>
@endpush
@section('content')
    @include('components.alerts')
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <b>Users Management</b>
                @if($showArchived)
                    <span class="text-secondary small ms-2">(Archive View)</span>
                @endif
            </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-circle me-1"></i>
                Add New User
            </button>
        </div>
    </div>

    <!-- Search & View Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <!-- Search & Clear -->
                <div class="col-md-6">
                    <div class="d-flex gap-2 align-items-center">
                        <!-- Move the entire search and clear button into a single flex container -->
                        <form action="{{ route('users.index') }}" method="GET" class="d-flex flex-grow-1 gap-2 align-items-center">
                            @if($showArchived)
                                <input type="hidden" name="archived" value="true">
                            @endif
                            <div class="input-group search-box flex-grow-1">
                                <input type="text" class="form-control" name="search" placeholder="Search users..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            
                            @if(request('search'))
                                @if($showArchived)
                                    <a href="{{ route('users.index', ['archived' => true]) }}" class="btn btn-outline-danger flex-shrink-0" title="Clear search">
                                @else
                                    <a href="{{ route('users.index') }}" class="btn btn-outline-danger flex-shrink-0" title="Clear search">
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
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Back to Active Users
                            </a>
                        @else
                            <a href="{{ route('users.index', ['archived' => true]) }}" class="btn btn-outline-warning" title="View archived users">
                                <i class="bi bi-archive me-1"></i>
                                View Archive
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Users Table -->
    <div class="table-container">
        <div class="table-responsive">
            <!-- Results Count -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="text-muted">
                    @if(request('search'))
                        Showing {{ $users->firstItem() }}–{{ $users->lastItem() }}
                        of {{ $users->total() }} results for
                        "<strong>{{ request('search') }}</strong>"
                    @else
                        Showing {{ $users->firstItem() }}–{{ $users->lastItem() }}
                        of {{ $users->total() }} {{ $showArchived ? 'archived' : 'active' }} users
                    @endif
                </div>
            </div>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Contact No.</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <strong>{{ $user->username }}</strong>
                        </td>
                        <td>{{ $user->full_name }}</td>
                        <td class="text-truncate" style="max-width: 100px;">{{ $user->email }}</td>
                        <td>{{ $user->contactNo ?? 'N/A' }}</td>
                        <td class="primary">{{ $user->role }}</td>
                        <td>
                             @if($user->is_active)
                                <button class="btn btn-sm btn-outline-secondary btn-action reset-password" 
                                        data-id="{{ $user->id }}" 
                                        data-name="{{ $user->full_name }}"
                                        title="Reset Password">
                                    <i class="bi bi-key"></i>
                                </button>
                            @endif
                            <button class="btn btn-sm btn-outline-info btn-action view-user" data-id="{{ $user->id }}" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            @if($user->is_active)
                                <button class="btn btn-sm btn-outline-warning btn-action edit-user" data-id="{{ $user->id }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-action archive-user"
                                    @if(session('user_id') == $user->id) disabled disabled-archive @endif
                                    data-id="{{ $user->id }}" data-name="{{ $user->full_name }}" title="Archive">
                                    <i class="bi bi-archive"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-outline-success btn-action restore-user" data-id="{{ $user->id }}" data-name="{{ $user->full_name }}" title="Restore">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <i class="bi bi-people display-4 text-muted"></i>
                            <p class="mt-3 mb-0">No {{ $showArchived ? 'archived' : 'active' }} users found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add New User
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" maxlength="50" required>
                                </div>
                                <div class="mb-3">
                                    <label for="f_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="f_name" name="f_name" placeholder="Enter first name" maxlength="100" required>
                                </div>
                                <div class="mb-3">
                                    <label for="m_name" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="m_name" name="m_name" placeholder="Enter middle name" maxlength="100">
                                </div>
                                <div class="mb-3">
                                    <label for="l_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="l_name" name="l_name" placeholder="Enter last name" maxlength="100" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contactNo" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="contactNo" name="contactNo" 
                                           placeholder="Enter contact number" 
                                           maxlength="11"
                                           pattern="[0-9]{0,11}" maxlength="100"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)">
                                </div>
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role }}">{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-warning mt-2 small">
                            * Password will be automatically generated.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editUserForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">
                            <i class="bi bi-pencil me-2"></i>
                            Edit User
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editUsername" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editUsername" placeholder="Enter username" name="username" maxlength="50" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editFName" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editFName" placeholder="Enter first name" name="f_name" maxlength="100" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editMName" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="editMName" placeholder="Enter middle name" name="m_name" maxlength="100">
                                </div>
                                <div class="mb-3">
                                    <label for="editLName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editLName" placeholder="Enter last name" name="l_name" maxlength="100" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="editEmail" placeholder="Enter email" name="email" maxlength="100" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editContactNo" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="editContactNo" name="contactNo" 
                                        maxlength="11" placeholder="Enter contact number" 
                                        pattern="[0-9]{0,11}"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)">
                                </div>
                                <div class="mb-3">
                                    <label for="editRole" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editRole" name="role" required>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role }}">
                                                {{ $role }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="roleHelpText" class="form-text" style="display: none;">
                                        <small class="text-muted">You cannot change your own role.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View User Modal - Compact -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserModalLabel">
                        <i class="bi bi-person-circle me-2"></i>
                        User Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- User Info in a more compact list -->
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <small class="text-muted">Username:</small>
                            <span class="fw-semibold" id="viewUsername"></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <small class="text-muted">Full Name:</small>
                            <span class="fw-semibold" id="viewFullName"></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <small class="text-muted">Email:</small>
                            <span class="fw-semibold" id="viewEmail"></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <small class="text-muted">Contact:</small>
                            <span class="fw-semibold" id="viewContactNo">N/A</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <small class="text-muted">Role:</small>
                            <span class="fw-semibold" id="viewRole"></span>
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
                    <div class="mt-3 p-2 bg-warning bg-opacity-10 rounded" id="archiveInfo" style="display: none;">
                        <small class="text-muted d-block">Archive Information</small>
                        <div class="d-flex justify-content-between">
                            <small>Date:</small>
                            <small class="fw-semibold" id="viewDateDisabled"></small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small>By:</small>
                            <small class="fw-semibold" id="viewDisabledBy"></small>
                        </div>
                    </div>               
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Confirmation Modal -->
    <div class="modal fade" id="archiveUserModal" tabindex="-1" aria-labelledby="archiveUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="archiveUserForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="archiveUserModalLabel">
                            <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                            Confirm Archive
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-archive text-warning" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Are you sure you want to archive this user?</h5>
                            <p class="text-muted">User: <strong id="archiveUserName"></strong></p>
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> Archived users cannot log in to the system but their data is preserved.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Archive User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div class="modal fade" id="restoreUserModal" tabindex="-1" aria-labelledby="restoreUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="restoreUserForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="restoreUserModalLabel">
                            <i class="bi bi-arrow-clockwise me-2 text-success"></i>
                            Confirm Restore
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-arrow-clockwise text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Are you sure you want to restore this user?</h5>
                            <p class="text-muted">User: <strong id="restoreUserName"></strong></p>
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                The user will be able to log in to the system again.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Restore User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Temporary Password Modal -->
    <div class="modal fade" id="tempPasswordModal" tabindex="-1" aria-labelledby="tempPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tempPasswordModalLabel">
                        <i class="bi bi-person-plus me-2"></i> New User Created
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <!-- User Info -->
                    <div class="mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                        <h5 class="mt-2">User Created Successfully!</h5>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title mb-2">User Details</h6>
                            <p class="mb-1"><strong>Name:</strong> <span id="newUserName"></span></p>
                            <p class="mb-1"><strong>Username:</strong> <span id="newUserUsername"></span></p>
                        </div>
                    </div>
                    
                    <!-- Temporary Password -->
                    <div class="alert alert-warning">
                        <h6 class="alert-heading mb-2">
                            <i class="bi bi-key me-1"></i>Temporary Password
                        </h6>
                        <p class="mb-2 fw-bold fs-5" id="tempPasswordDisplay"></p>
                        <small class="mb-0">
                            Please provide this password to the user. They will be required to change it upon first login.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="resetPasswordForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="resetPasswordModalLabel">
                            <i class="bi bi-key me-2"></i>
                            Reset Password
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="bi bi-key text-warning" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Reset password for: <strong id="resetPasswordUserName"></strong></p>
                        </div>
                        
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="newPassword" name="password" placeholder="Enter new password" required maxlength="100">
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        <div class="mb-3">
                            <label for="newPasswordConfirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="newPasswordConfirmation" name="password_confirmation" placeholder="Confirm new password" required maxlength="100">
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            The user will need to use this new password to log in.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>

    @if(session('temp_password'))
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('newUserName').textContent = '{{ session('new_user_name') }}';
            document.getElementById('newUserUsername').textContent = '{{ session('new_user_username') }}';
            document.getElementById('tempPasswordDisplay').textContent = '{{ session('temp_password') }}';
            
            var tempModal = new bootstrap.Modal(document.getElementById('tempPasswordModal'));
            tempModal.show();
        });
    @endif

        let currentViewUserId = null;
    
        // Edit User
        document.querySelectorAll('.edit-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                
                fetch(`/users/${userId}/edit`)
                    .then(response => response.json())
                    .then(user => {
                        document.getElementById('editUsername').value = user.username;
                        document.getElementById('editFName').value = user.f_name;
                        document.getElementById('editMName').value = user.m_name || '';
                        document.getElementById('editLName').value = user.l_name;
                        document.getElementById('editEmail').value = user.email;
                        document.getElementById('editContactNo').value = user.contactNo || '';
                        document.getElementById('editRole').value = user.role;

                        // Disable role select if editing self
                        const roleSelect = document.getElementById('editRole');
                        if(user.id === {{ session('user_id') }}) {
                            roleSelect.setAttribute('readonly', true);
                            document.getElementById('roleHelpText').style.display = 'block';
                        } else {
                            roleSelect.removeAttribute('readonly');
                            document.getElementById('roleHelpText').style.display = 'none';
                        }

                        document.getElementById('editUserForm').action = `/users/${userId}`;
                        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                        modal.show();
                    });
            });
        });
        
        // View User
        document.querySelectorAll('.view-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                currentViewUserId = userId; 
                
                fetch(`/users/${userId}`)
                    .then(response => response.json())
                    .then(user => {                
                        document.getElementById('viewUsername').textContent = user.username;
                        document.getElementById('viewFullName').textContent = user.full_name;
                        document.getElementById('viewEmail').textContent = user.email;
                        document.getElementById('viewContactNo').textContent = user.contactNo || 'N/A';
                        document.getElementById('viewRole').textContent = user.role;
                        document.getElementById('viewCreatedAt').textContent = new Date(user.created_at).toLocaleDateString('en-US', { 
                            month: 'short', 
                            day: 'numeric', 
                            year: 'numeric' 
                        });
                        document.getElementById('viewUpdatedAt').textContent = new Date(user.updated_at).toLocaleDateString('en-US', { 
                            month: 'short', 
                            day: 'numeric', 
                            year: 'numeric' 
                        });
                                                
                        const statusText = document.getElementById('viewStatusText');
                        if (user.is_active) {
                            statusText.textContent = 'Active';
                            statusText.className = 'fw-semibold'; 
                            document.getElementById('archiveInfo').style.display = 'none';
                        } else {
                            statusText.textContent = 'Archived';
                            statusText.className = 'fw-semibold';
                            document.getElementById('archiveInfo').style.display = 'block';
                            
                            // Date disabled
                            if (user.date_disabled) {
                                document.getElementById('viewDateDisabled').textContent = 
                                new Date(user.date_disabled).toLocaleDateString('en-US', { 
                                    month: 'short', 
                                    day: 'numeric', 
                                    year: 'numeric' 
                                });
                            } else {
                                document.getElementById('viewDateDisabled').textContent = 'N/A';
                            }
                            
                            // Disabled by
                            if (user.disabled_by && user.disabled_by.full_name) {
                                document.getElementById('viewDisabledBy').textContent = user.disabled_by.full_name;
                            } else if (user.disabled_by_user_id) {
                                document.getElementById('viewDisabledBy').textContent = 'User #' + user.disabled_by_user_id;
                            } else {
                                document.getElementById('viewDisabledBy').textContent = 'System';
                            }
                        }
                        
                        const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching user:', error);
                    });
            });
        });
        
        // Archive User
        document.querySelectorAll('.archive-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const userName = this.getAttribute('data-name');
                
                document.getElementById('archiveUserName').textContent = userName;
                document.getElementById('archiveUserForm').action = `/users/${userId}/archive`;
                
                const modal = new bootstrap.Modal(document.getElementById('archiveUserModal'));
                modal.show();
            });
        });
        
        // Restore User
        document.querySelectorAll('.restore-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const userName = this.getAttribute('data-name');
                
                document.getElementById('restoreUserName').textContent = userName;
                document.getElementById('restoreUserForm').action = `/users/${userId}/restore`;
                
                const modal = new bootstrap.Modal(document.getElementById('restoreUserModal'));
                modal.show();
            });
        });
    
        // Reset Password
        document.querySelectorAll('.reset-password').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const userName = this.getAttribute('data-name');
                
                document.getElementById('resetPasswordUserName').textContent = userName;
                // Set the form action dynamically
                document.getElementById('resetPasswordForm').action = `/users/${userId}/reset-password`;
                
                document.getElementById('newPassword').value = '';
                document.getElementById('newPasswordConfirmation').value = '';
                
                const resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
                resetModal.show();
            });
        });
            
        
    </script>
    @endpush
@endsection
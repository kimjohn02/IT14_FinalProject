@extends('layouts.app')

@section('title', 'Account Settings - SAR EQUIP')

@push('styles')
<style>
    .settings-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
    }

    .admin-note {
        border-left: 4px solid #ffc107;
            font-size: 0.8rem; /* smaller text */
    }

    .settings-header .card-title {
        font-size: 1rem; /* smaller than default */
        font-weight: 600;  /* optional: keep it slightly bold */
    }
</style>
@endpush

@section('content')
@include('components.alerts')

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold" style="color: #06448a;">
            <i class="bi bi-person-gear me-2"></i>Account Settings
        </h2>
    </div>

    @if(!$user)
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            User not found. Please <a href="{{ route('login') }}" class="alert-link">log in again</a>.
        </div>
    @else
    <div class="row">
        <!-- Personal Information -->
        <div class="col-lg-8">
            <div class="card settings-card">
                <div class="card-header settings-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-vcard me-2"></i>Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('account.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-6 mb-3">
                                <label for="f_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('f_name') is-invalid @enderror" 
                                       id="f_name" name="f_name" 
                                       value="{{ old('f_name', $user->f_name) }}" 
                                       placeholder="Enter first name" 
                                       maxlength="100" required>
                                @error('f_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Middle Name -->
                            <div class="col-md-6 mb-3">
                                <label for="m_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control @error('m_name') is-invalid @enderror" 
                                       id="m_name" name="m_name" 
                                       value="{{ old('m_name', $user->m_name) }}" 
                                       placeholder="Enter middle name" 
                                       maxlength="100">
                                @error('m_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="col-md-6 mb-3">
                                <label for="l_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('l_name') is-invalid @enderror" 
                                       id="l_name" name="l_name" 
                                       value="{{ old('l_name', $user->l_name) }}" 
                                       placeholder="Enter last name" 
                                       maxlength="100" required>
                                @error('l_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Contact Number -->
                            <div class="col-md-6 mb-3">
                                <label for="contactNo" class="form-label">Contact Number</label>
                                <input type="text" class="form-control @error('contactNo') is-invalid @enderror" 
                                       id="contactNo" name="contactNo" 
                                       value="{{ old('contactNo', $user->contactNo) }}" 
                                       placeholder="Enter contact number" 
                                       maxlength="11"
                                       pattern="[0-9]{0,11}"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)">
                                @error('contactNo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-12 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" 
                                       value="{{ old('email', $user->email) }}" 
                                       placeholder="Enter email address" 
                                       maxlength="255" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Password Change -->
            <div class="card settings-card">
                <div class="card-header settings-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-lock me-2"></i>Change Password
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('account.settings.password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Current Password -->
                            <div class="col-12 mb-3">
                                <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" name="current_password" 
                                       placeholder="Enter current password" 
                                       required>
                                       <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- New Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" placeholder="Enter new password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Minimum 8 characters</div>
                            </div>
                            

                            <!-- Confirm New Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" 
                                       placeholder="Confirm new password" 
                                       required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-key me-2"></i>Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

          
        </div>

        <!-- System Information & Admin-Managed Fields -->
        <div class="col-lg-4">
            <!-- System Information -->
            <div class="card settings-card">
                <div class="card-header settings-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>System Information
                    </h5>
                </div>

                <div class="card-body">
                    <!-- System Info -->
                    <div class="list-group list-group-flush mb-3">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <small class="text-muted">Username:</small>
                            <span class="fw-semibold">{{ $user->username }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <small class="text-muted">Role:</small>
                            <span class="fw-semibold">{{ $user->role }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <small class="text-muted">Account Created:</small>
                            <span class="fw-semibold">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <!-- Admin Managed Note (Secondary Info) -->
                    <div class="admin-note p-2 rounded d-flex align-items-center">
                        <i class="bi bi-tools text-warning me-2 fs-6"></i>
                        <small class="text-dark mb-0">Username & role only editable by administrators.</small>
                    </div>
                </div>
            </div>

              <!-- Session Timeout Settings -->
            <div class="card settings-card mt-3">
                <div class="card-header settings-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock me-2"></i>Session Timeout Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form id="sessionForm" action="{{ route('session.timeout.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="session_timeout" class="form-label">Auto-logout after inactivity:</label>
                            <select name="timeout" id="session_timeout" class="form-select">
                                @php
                                    // Get current timeout from session (which comes from database on login)
                                    $currentTimeout = session('session_timeout', 600);
                                @endphp
                                
                                <option value="300" {{ $currentTimeout == 300 ? 'selected' : '' }}>5 minutes</option>
                                <option value="600" {{ $currentTimeout == 600 ? 'selected' : '' }}>10 minutes (default)</option>
                                <option value="1800" {{ $currentTimeout == 1800 ? 'selected' : '' }}>30 minutes</option>
                                <option value="3600" {{ $currentTimeout == 3600 ? 'selected' : '' }}>1 hour</option>
                                <option value="7200" {{ $currentTimeout == 7200 ? 'selected' : '' }}>2 hours</option>
                                <option value="14400" {{ $currentTimeout == 14400 ? 'selected' : '' }}>4 hours</option>
                                <option value="28800" {{ $currentTimeout == 28800 ? 'selected' : '' }}>8 hours</option>
                                <option value="0" {{ $currentTimeout == 0 ? 'selected' : '' }}>Never auto-logout</option>
                            </select>
                            
                            <div class="form-text mt-2">
                                <i class="bi bi-info-circle"></i>
                                Current setting: 
                                @if($currentTimeout == 0)
                                    <span class="text-success fw-bold">Never auto-logout</span>
                                @elseif($currentTimeout == 60)
                                    <span class="fw-bold">1 minute</span>
                                @else
                                    <span class="fw-bold">{{ round($currentTimeout / 60) }} minutes</span>
                                @endif
                                <br>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Setting
                            </button>
                        </div>
                    </form>       
                </div>
            </div>

            @if(session('user_role') == 'Administrator')
            <!-- Backup & Restore -->
            <div class="card settings-card">
                <div class="card-header settings-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-hdd-network me-2"></i>Database Backup & Restore
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Backup Button -->
                    <div class="mb-3">
                        <form action="{{ route('database.backup') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-download me-2"></i>Backup Database
                            </button>
                        </form>
                    </div>
                    <hr>
                    <!-- Restore Form -->
                    <form action="{{ route('database.restore') }}" method="POST" enctype="multipart/form-data"
                    onsubmit="return confirmRestore()">
                        @csrf
                        <div class="mb-3">
                            <label for="backup_file" class="form-label">Restore from backup (.sql)</label>
                            <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".sql" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-upload me-2"></i>Restore Database
                        </button>
                        <small class="text-danger mt-1 d-block">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                            WARNING: This will DELETE ALL current product images!
                        </small>
                    </form>
                </div>
            </div>
            @endif   
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Contact number validation
        const contactInput = document.getElementById('contactNo');
        if (contactInput) {
            contactInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
            });
        }

        // Toggle Password Visibility
        const toggleButtons = document.querySelectorAll('.toggle-password');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Find the input field relative to this button
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                
                if (input.type === "password") {
                    input.type = "text";
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    input.type = "password";
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            });
        });

        // Password confirmation validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
        
        function validatePassword() {
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords don't match");
            } else if (confirmPassword) {
                confirmPassword.setCustomValidity('');
            }
        }

        if (password && confirmPassword) {
            password.addEventListener('input', validatePassword);
            confirmPassword.addEventListener('input', validatePassword);
        }

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });

    function confirmRestore() {
        return confirm(
            '⚠️ CRITICAL WARNING ⚠️\n\n' +
            '1. This will overwrite ALL current database data\n' +
            '2. ALL product images will be DELETED\n' +
            '3. This action cannot be undone\n\n' +
            'Are you absolutely sure you want to continue?'
        );
    }
</script>
@endpush
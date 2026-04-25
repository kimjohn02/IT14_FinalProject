<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'f_name',
        'm_name',
        'l_name',
        'contactNo',
        'role',
        'is_active',
        'date_disabled',
        'disabled_by_user_id',
        'email',
        'password',
        'password_changed',
        'session_timeout'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['full_name']; 
    
     protected $attributes = [
        'session_timeout' => 600, // Default 10 minutes
    ];
    
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_changed' => 'boolean',
            'is_active' => 'boolean',
            'date_disabled' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'session_timeout' => 'integer',
        ];
    }

    public function disabledBy()
    {
        return $this->belongsTo(User::class, 'disabled_by_user_id')->withDefault([
            'full_name' => 'System'
        ]);
    }

    // Helper methods

    public function isAdministrator(): bool
    {
        return $this->role === 'Administrator';
    }

    public function isCashier(): bool
    {
        return $this->role === 'Cashier';
    }
    
    public function getFullNameAttribute()
    {
        $middle = $this->m_name ? " {$this->m_name} " : ' ';
        return "{$this->f_name}{$middle}{$this->l_name}";
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_active', false);
    }
}

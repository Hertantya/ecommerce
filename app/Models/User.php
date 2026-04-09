<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Columns that are mass-assignable (safe to fill via create/update)
    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'address'];

    // Never expose the password in JSON output
    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed'];

    // ── Relationships ──────────────────────────────
    /**
     * A customer can have many orders.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // ── Helper methods ─────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }
}

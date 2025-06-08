<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'contact_name',
        'contact_email',
        'address',
        'phone',
        // 'user_id', // If you decide a client record is directly managed by a specific user
    ];

    // Relationships
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Optional: If a client is associated with a user account for client portal access
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}

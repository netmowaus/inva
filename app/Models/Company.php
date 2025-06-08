<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'logo_path',
        'address',
        'phone',
        'email',
        'website',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'companies'; // Explicitly defining table name
}

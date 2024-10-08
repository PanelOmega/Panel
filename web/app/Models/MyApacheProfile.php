<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MyApacheProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'packages',
        'tags',
        'description',
        'version',
        'is_default',
        'is_active',
        'config',
        'vendor',
    ];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomainDkim extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain_name',
        'description',
        'selector',
        'private_key',
        'public_key'
    ];

    protected $table = 'domain_dkims';
}

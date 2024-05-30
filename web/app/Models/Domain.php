<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_DELETED = 'deleted';

    public const STATUS_DEACTIVATED = 'deactivated';

    public const STATUS_BROKEN = 'broken';

    protected $fillable = [
        'domain',
        'status',
    ];
}

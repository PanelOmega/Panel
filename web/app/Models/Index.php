<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Index extends Model
{
    use HasFactory;

    protected $fillable = [
        'hosting_subscription_id',
        'directory',
        'index_type'
    ];

    protected $table = 'indices';

    public static function boot() {
        parent::boot();
        static::indexesBoot();
    }

    public static function indexesBoot() {

    }

//    public static function loadRirectories() {
//        dd($this->directoryPrivacy());
//    }


    public function directoryPrivacy()
    {
        return $this->belongsTo(DirectoryPrivacy::class, 'directory', 'directory')
            ->where('hosting_subscription_id', $this->hosting_subscription_id);
    }
}

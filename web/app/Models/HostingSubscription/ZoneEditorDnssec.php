<?php

namespace App\Models\HostingSubscription;

use App\Models\Traits\ZoneEditorDnssecTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneEditorDnssec extends Model
{
    use HasFactory, ZoneEditorDnssecTrait;

    protected $fillable = [
        'hosting_subscription_id',
        'key_tag',
        'ket_type',
        'algorithm',
    ];

    public static function boot()
    {
        parent::boot();
        static::zoneEditorDnssecBoot();
    }

    public static function zoneEditorDnssecBoot()
    {

    }

    public function zoneEditor()
    {
        return $this->belongsTo(ZoneEditor::class, 'zone_editor_id');
    }


}

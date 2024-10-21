<?php

namespace App\Models\LandMouzaMapBank\CSMouzaMap;

use App\Models\LandInformationBank\CSDagAndKhatiyan\CSDagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CSMouzaMapInfo extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function csInfo()
    {
        return $this->hasOne(CSDagInfo::class, 'id', 'cs_id');
    }

    public function map_image()
    {
        return $this->hasMany(CSMouzaMapInfoImage::class, 'cs_mouza_map_id', 'id');
    }
}

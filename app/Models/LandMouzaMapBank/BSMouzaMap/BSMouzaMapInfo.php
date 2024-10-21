<?php

namespace App\Models\LandMouzaMapBank\BSMouzaMap;

use App\Models\LandInformationBank\BSDagAndKhatiyan\BSDagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BSMouzaMapInfo extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function bsDagInfo()
    {
        return $this->hasOne(BSDagInfo::class,'id','bs_id');
    }
    public function map_image()
    {
        return $this->hasMany(BSMouzaMapInfoImage::class, 'bs_mouza_map_id', 'id');
    }
}

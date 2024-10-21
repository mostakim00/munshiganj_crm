<?php

namespace App\Models\LandMouzaMapBank\RSMouzaMap;

use App\Models\LandInformationBank\RSDagAndKhatiyan\RSDagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RSMouzaMapInfo extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function rsInfo()
    {
        return $this->hasOne(RSDagInfo::class,'id','rs_id');
    }
    public function map_image()
    {
        return $this->hasMany(RSMouzaMapInfoImage::class, 'rs_mouza_map_id', 'id');
    }
}

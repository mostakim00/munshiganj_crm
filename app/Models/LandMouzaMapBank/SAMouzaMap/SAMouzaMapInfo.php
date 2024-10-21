<?php

namespace App\Models\LandMouzaMapBank\SAMouzaMap;

use App\Models\LandInformationBank\SADagAndKhatiyan\SADagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SAMouzaMapInfo extends Model
{
    use HasFactory;

    protected $guarded = [];
    public function saInfo()
    {
        return $this->hasOne(SADagInfo::class, 'id', 'sa_id');
    }
    public function map_image()
    {
        return $this->hasMany(SAMouzaMapInfoImage::class, 'sa_mouza_map_id', 'id');
    }

}

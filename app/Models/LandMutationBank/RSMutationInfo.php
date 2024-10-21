<?php

namespace App\Models\LandMutationBank;

use App\Models\LandInformationBank\RSDagAndKhatiyan\RSDagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RSMutationInfo extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function rsDagInfo(){      
            return $this->hasOne(RSDagInfo::class,'id','rs_id'); 
    }
}

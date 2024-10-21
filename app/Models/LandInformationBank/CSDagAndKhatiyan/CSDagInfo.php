<?php

namespace App\Models\LandInformationBank\CSDagAndKhatiyan;

use App\Models\LandInformationBank\MouzaInformation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LandInformationBank\ProjectInformation;

class CSDagInfo extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function csRecordedPerson(){
        return $this->hasManyThrough(CSDagRecordedPerson::class,CSDagRecordedPersonRelation::class,'CSDagInfoId','id','id' ,'CSDagRecordedPeopleId');
    }

    public function mouzaInfo(){
        return $this->hasOne(MouzaInformation::class, 'id','mouza_id' );
    }

}


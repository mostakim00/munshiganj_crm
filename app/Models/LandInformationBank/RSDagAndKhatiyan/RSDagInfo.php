<?php

namespace App\Models\LandInformationBank\RSDagAndKhatiyan;

use App\Models\LandInformationBank\SADagAndKhatiyan\SADagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RSDagInfo extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function rsRecordedPerson(){
        return $this->hasManyThrough(RSDagRecordedPerson::class,RSDagRecordedPersonRelation::class,'RSDagInfoId','id','id','RSDagRecordedPeopleId');

    }

    public function saInfo(){
        return $this->hasOne(SADagInfo::class,'id','sa_id');
    }


}
   
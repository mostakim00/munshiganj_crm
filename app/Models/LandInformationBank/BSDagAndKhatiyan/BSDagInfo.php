<?php

namespace App\Models\LandInformationBank\BSDagAndKhatiyan;

use App\Models\LandInformationBank\RSDagAndKhatiyan\RSDagInfo;
use App\Models\LandInformationBank\RSDagAndKhatiyan\RSDagRecordedPersonRelation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BSDagInfo extends Model
{
    use HasFactory;

    protected $hidden = [
        'laravel_through_key'
    ];
    
    protected $guarded=[];

    public function bsRecordedPerson(){
        return $this->hasManyThrough(BSDagRecordedPerson::class,BSDagRecordedPersonRelation::class,'BSDagInfoId','id','id','BSDagRecordedPeopleId');

    }
    public function rsInfo(){
        return $this->hasOne(RSDagInfo::class,'id','rs_id');
    }

}


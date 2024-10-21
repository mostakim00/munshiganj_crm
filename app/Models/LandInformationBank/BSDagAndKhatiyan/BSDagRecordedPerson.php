<?php

namespace App\Models\LandInformationBank\BSDagAndKhatiyan;

//use App\Models\LandInformationBank\RSDagAndKhatiyan\BSDagRecordedPerson;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BSDagRecordedPerson extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function bsRecordedPersonLandRelation(){
        return $this->hasOne(BSDagRecordedPerson::class,'BSDagRecordedPeopleId','id');
    }
}

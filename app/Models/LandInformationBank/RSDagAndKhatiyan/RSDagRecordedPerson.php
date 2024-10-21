<?php

namespace App\Models\LandInformationBank\RSDagAndKhatiyan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RSDagRecordedPerson extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function rsRecordedPersonLandRelation(){
        return $this->hasOne(RSDagRecordedPerson::class,'RSDagRecordedPeopleId','id');
    }
}

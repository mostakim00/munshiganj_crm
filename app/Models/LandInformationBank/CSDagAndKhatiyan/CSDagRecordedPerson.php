<?php

namespace App\Models\LandInformationBank\CSDagAndKhatiyan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CSDagRecordedPerson extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function csRecordedPersonLandRelation()
    {
        return $this->hasOne(CSDagRecordedPerson::class,'CSDagRecordedPeopleId','id');
    }
}

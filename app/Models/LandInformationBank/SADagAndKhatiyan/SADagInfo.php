<?php

namespace App\Models\LandInformationBank\SADagAndKhatiyan;

use App\Models\LandInformationBank\CSDagAndKhatiyan\CSDagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SADagInfo extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function saRecordedPerson()
    {
        return $this->hasManyThrough(SADagRecordedPerson::class, SADagRecordedPersonRelation::class, 'SADagInfoId', 'id', 'id', 'SADagRecordedPeopleId');
    }

    public function csInfo()
    {
        return $this->hasOne(CSDagInfo::class, 'id', 'cs_id');
    }
}

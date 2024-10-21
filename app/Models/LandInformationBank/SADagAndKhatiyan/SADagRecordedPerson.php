<?php

namespace App\Models\LandInformationBank\SADagAndKhatiyan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SADagRecordedPerson extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function saRecordedPersonLandRelation()
    {
        return $this->hasOne(SADagRecordedPerson::class, 'CSDagRecordedPeopleId', 'id');
    }

}

<?php

namespace App\Models\LandMutationBank;

use App\Models\LandInformationBank\CSDagAndKhatiyan\CSDagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CSMutationInfo extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function csInfo()
    {
        return $this->hasOne(CSDagInfo::class, 'id', 'cs_id');
    }

}

<?php

namespace App\Models\LandDisputeBank;

use App\Models\LandInformationBank\BSDagAndKhatiyan\BSDagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BSDispute extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function bsDagInfo()
    {
        return $this->hasOne(BSDagInfo::class,'id','bs_id');
    }
}

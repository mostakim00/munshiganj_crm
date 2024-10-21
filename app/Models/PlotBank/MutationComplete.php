<?php

namespace App\Models\PlotBank;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutationComplete extends Model
{
    use HasFactory;

    public function customer_land_agreement_info(){
        return $this->hasOne(\App\Models\Customer\CustomerLandAgreement::class,'id','customer_land_agreement_id');
    }
}

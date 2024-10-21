<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerLandInformation extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function customerInfo()
    {
        return $this->hasManyThrough(CustomerInfo::class,CustomerLandRelation::class,'landID','id','id' ,'customerID');
    }

    public function plotInfo()
    {
        return $this->hasOne(\App\Models\PlotBank\PlotInformation::class, 'id', 'plot_id');
    }

    public function customer_land_price()
    {
        return $this->hasOne(CustomerLandPriceInfo::class, 'customer_land_info_id', 'id');
    }
    
    public function agent()
    {
        return $this->hasOne(\App\Models\Agent\Agent::class, 'id', 'agentID');
    }

    public function agentPrice()
    {
        return $this->hasOne(\App\Models\Agent\AgentPaymentInformation::class, 'customer_land_info_id', 'id');
    }

    public function landAgreement()
    {
        return $this->hasOne(\App\Models\customer\CustomerLandAgreement::class, 'customer_land_information_id', 'id');
    }

  

}

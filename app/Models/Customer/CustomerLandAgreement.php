<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerLandAgreement extends Model
{
    use HasFactory;

    public function landSellerAgreement()
    {
        return $this->hasOne(\App\Models\LandSeller\LandSellerAgreement::class, 'id', 'land_seller_agreement_id');
    }

    public function customer_land_info()
    {
        return $this->hasOne(\App\Models\Customer\CustomerLandInformation::class, 'id', 'customer_land_information_id');
    }

    public function dolilVariation()
    {
        return $this->hasOne(\App\Models\DolilVariation::class,'id', 'dolil_variation_id');
    }

}

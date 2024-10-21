<?php

namespace App\Models\LandSeller;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandSellerAgreement extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function sellerInfo()
    {
        return $this->hasManyThrough(LandSellerList::class,LandSellerAgreementRelation::class,'land_seller_agreements_id','id','id' ,'land_seller_id');
    }

    public function bsInfo()
    {
        return $this->hasManyThrough(\App\Models\LandInformationBank\BSDagAndKhatiyan\BSDagInfo::class,LandSellerAgreementBSRelation::class,'land_seller_agreements_id','id','id' ,'bs_id');
    }

    public function priceInfo()
    {
        return $this->hasOne(LandSellerAgreementPriceInformation::class,'land_seller_agreements_id','id');
    }

    public function landBroker()
    {
        return $this->hasOne(\App\Models\LandBroker\LandBroker::class,'id','landBrokerID');
    }

    public function landBrokerAgreements()
    {
        return $this->hasMany(\App\Models\LandBroker\LandBrokerAgreement::class,'land_seller_agreements_id','id');
    }

    public function brokerPriceInfo()
    {
        return $this->hasOne(\App\Models\LandBroker\LandBrokerPaymentInformation::class,'land_seller_agreements_id', 'id');
    }
    
    public function dolilVariation()
    {
        return $this->hasOne(\App\Models\DolilVariation::class,'id', 'dolil_variation_id');
    }

    public function dolilImage()
    {
        return $this->hasMany(\App\Models\LandSeller\LandSellerAgreementDolilImage::class,'land_seller_agreements_id', 'id');
    }

    public function otherImage()
    {
        return $this->hasMany(\App\Models\LandSeller\LandSellerAgreementOtherImage::class,'land_seller_agreements_id', 'id');
    }

    public function porcaImage()
    {
        return $this->hasMany(\App\Models\LandSeller\LandSellerAgreementPorcaImage::class,'land_seller_agreements_id', 'id');
    }
}

<?php

namespace App\Models\LandSeller;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandSellerAgreementPriceInformation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function paymentStatement()
    {
        return $this->hasMany(LandSellerAgreementPaymentStatement::class,'land_seller_agreement_price_information_id','id');
    }

}

<?php

namespace App\Models\LandSubDeedBank\CSSubDeed;

use App\Models\LandInformationBank\CSDagAndKhatiyan\CSDagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CSSubDeedInfo extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function csInfo()
    {
        return $this->hasOne(CSDagInfo::class, 'id', 'cs_id');
    }
    public function csSalerBuyer()
    {
        return $this->hasManyThrough(CSSubDeedSellerBuyerList::class, CSSubDeedSellerBuyerRelation::class, 'cs_sub_deed_id', 'id', 'id', 'cs_seller_buyer_id');
    }
}

<?php

namespace App\Models\LandSubDeedBank\BSSubDeed;

use App\Models\LandInformationBank\BSDagAndKhatiyan\BSDagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BSSubDeedInfo extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function bsSellerBuyerList(){
        return $this->hasManyThrough(BSSubDeedSellerBuyerList::class, BSSubDeedSellerBuyerRelation::class,'bs_sub_deed_id','id','id','bs_seller_buyer_id');
    }
    public function bsDagInfo(){
        return $this->hasOne(BSDagInfo::class,'id','bs_id');
    }
}

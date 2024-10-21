<?php

namespace App\Models\LandSubDeedBank\RSSubDeed;

use App\Models\LandInformationBank\RSDagAndKhatiyan\RSDagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RSSubDeedInfo extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function rsSellerBuyerList(){
        return $this->hasManyThrough(RSSubDeedSellerBuyerList::class, RSSubDeedSellerBuyerRelation::class,'rs_sub_deed_id','id','id','rs_seller_buyer_id');
    }
    public function rsDagInfo(){
        return $this->hasOne(RSDagInfo::class,'id','rs_id');
    }
}
 
<?php

namespace App\Models\LandSubDeedBank\BSSubDeed;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BSSubDeedSellerBuyerList extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function bsSubDeedSellerBuyerRelation(){
        return $this->hasOne(BSSubDeedSellerBuyerList::class,'bs_seller_buyer_id','id');
    }
}

<?php

namespace App\Models\LandSubDeedBank\RSSubDeed;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RSSubDeedSellerBuyerList extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function rsSubDeedSellerBuyerRelation(){
        return $this->hasOne(RSSubDeedSellerBuyerList::class,'rs_seller_buyer_id','id');
    }
}
